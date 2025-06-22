<?php
require __DIR__ . '/functions.php';

$email = $_GET['email'] ?? '';
$email = strtolower(trim($email));
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $code = $_POST['code'] ?? '';

    $expectedCode = @file_get_contents(codeFile($email));

    if ($code && $expectedCode && trim($code) === trim($expectedCode)) {
        if (unsubscribeEmail($email)) {
            $msg = "✅ You have been unsubscribed successfully.";
        } else {
            $msg = "❌ Error: Could not unsubscribe.";
        }
    } else {
        $msg = "⚠️ Invalid verification code.";
    }
} elseif ($email) {
    $code = generateVerificationCode();
    if (sendVerificationEmail($email, $code)) {
        $msg = "📧 Verification code sent to <strong>$email</strong>. Please enter the code below.";
    } else {
        $msg = "❌ Failed to send verification email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe - GH Timeline</title>
</head>
<body>
<h2>Unsubscribe from GitHub Timeline Emails</h2>

<p><?= $msg ?></p>

<form method="POST">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <label>
        Verification Code:
        <input type="text" name="code" required>
    </label>
    <button type="submit">Confirm Unsubscribe</button>
</form>

</body>
</html>