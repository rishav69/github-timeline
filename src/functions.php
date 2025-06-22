<?php

declare(strict_types=1);

const EMAIL_DB = __DIR__ . '/registered_emails.txt';

const CODE_DIR = __DIR__ . '/codes';

const GH_ENDPOINT = 'https://api.github.com/events';

/**
 * Convert the GitHub events array into a small HTML snippet we can
 * embed inside the email.
 *
 * @param array $events
 * @return string HTML
 */
function formatGitHubData(array $events): string
{
    if (!$events) {
        return '<p>No recent events.</p>';
    }

    ob_start(); ?>
    <div style="font-family:Arial,Helvetica,sans-serif">
      <h3 style="margin:0 0 8px">Latest public GitHub activity</h3>
      <?php foreach (array_slice($events, 0, 10) as $ev): ?>
          <div style="margin:6px 0">
            <strong><?= htmlspecialchars($ev['type'] ?? 'Event') ?></strong>
            on
            <em><?= htmlspecialchars($ev['repo']['name'] ?? '(unknown repo)') ?></em><br>
            <small><?= date('Y-m-d H:i', strtotime($ev['created_at'] ?? '')) ?></small>
          </div>
      <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}



function codeFile(string $email): string{
    if (!is_dir(CODE_DIR)) {
        mkdir(CODE_DIR, 0700, true);          
    }
    return CODE_DIR . '/' . md5(strtolower($email)) . '.txt';
}

function generateVerificationCode(): string{
    return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationEmail(string $email, string $code): bool{
    file_put_contents(codeFile($email), $code, LOCK_EX);

    $subject = 'GH-Timeline • Your verification code';
    $body = <<<HTML
    <html>
      <body style="font-family:Arial,sans-serif">
        <p>Use the code below to complete your action on <strong>GH-Timeline</strong>:</p>
        <h2 style="letter-spacing:4px">$code</h2>
        <p style="font-size:90%;color:#666">If you didn't request this, just ignore the email.</p>
      </body>
    </html> 
    HTML;

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: GH-Timeline <noreply@example.com>\r\n";

    return mail($email, $subject, $body, $headers);
}

function registerEmail(string $email): bool
{
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $list = file_exists(EMAIL_DB)
          ? file(EMAIL_DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
          : [];

    if (!in_array($email, $list, true)) {
        $list[] = $email;
        file_put_contents(EMAIL_DB, implode(PHP_EOL, $list) . PHP_EOL, LOCK_EX);
    }

    @unlink(codeFile($email));

    return true;
}

function unsubscribeEmail(string $email): bool
{
    $email = strtolower(trim($email));
    if (!file_exists(EMAIL_DB)) return false;

    $list  = file(EMAIL_DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new   = array_values(array_diff($list, [$email]));

    if (count($new) !== count($list)) {
        file_put_contents(EMAIL_DB, implode(PHP_EOL, $new) . PHP_EOL, LOCK_EX);
        @unlink(codeFile($email));
        return true;
    }
    return false;
}

function fetchGitHubTimeline()
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => GH_ENDPOINT,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: GH-Timeline-Assignment',
            'Accept: application/json'
        ]
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "cURL error: " . curl_error($ch) . "\n", FILE_APPEND);
        curl_close($ch);
        return null;
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "GitHub returned status $status\n", FILE_APPEND);
        return null;
    }

    return json_decode($response, true);
}

function sendGitHubUpdatesToSubscribers(): void
{
    // 0) start‑of‑function marker
    file_put_contents(__DIR__ . '/cron_debug.txt', "Running function\n", FILE_APPEND);

    /* ---------------------------------------------------------
       1)  Is the email database present?
    --------------------------------------------------------- */
    if (!file_exists(EMAIL_DB)) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "No email DB – abort\n", FILE_APPEND);
        return;
    }

    /* ---------------------------------------------------------
       2)  Load subscribers
    --------------------------------------------------------- */
    $subscribers = file(EMAIL_DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$subscribers) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "No subscribers found – abort\n", FILE_APPEND);
        return;
    }
    file_put_contents(__DIR__ . '/cron_debug.txt',
        "Found subscribers: " . implode(', ', $subscribers) . "\n",
        FILE_APPEND
    );

    /* ---------------------------------------------------------
       3)  Fetch GitHub timeline
    --------------------------------------------------------- */
    $events = fetchGitHubTimeline();
    if (!$events) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "GitHub API returned nothing – abort\n", FILE_APPEND);
        return;
    }
    file_put_contents(__DIR__ . '/cron_debug.txt', "Fetched events OK\n", FILE_APPEND);

    $html = formatGitHubData($events);

    /* ---------------------------------------------------------
       4)  Send e‑mail to each subscriber
    --------------------------------------------------------- */
    foreach ($subscribers as $addr) {
        file_put_contents(__DIR__ . '/cron_debug.txt', "Sending to $addr ... ", FILE_APPEND);

        // ‑‑‑ build unsubscribe link – adjust path/port if needed
        $unsubscribeLink = sprintf(
            'http://localhost/github-timeline-rishav69-main/src/unsubscribe.php?email=%s',
            urlencode($addr)
        );

        $body = <<<HTML
<html><body style="font-family:Arial,sans-serif">
  $html
  <p style="margin-top:24px;font-size:90%">
    If you no longer wish to receive these updates, click
    <a href="$unsubscribeLink">unsubscribe</a>.
  </p>
</body></html>
HTML;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: GH-Timeline <noreply@example.com>\r\n";

        $ok = mail($addr, 'GitHub public timeline update', $body, $headers);

        file_put_contents(
            __DIR__ . '/cron_debug.txt',
            $ok ? "OK\n" : "mail() returned FALSE\n",
            FILE_APPEND
        );
    }

    file_put_contents(__DIR__ . '/cron_debug.txt', "Function finished\n", FILE_APPEND);
}
