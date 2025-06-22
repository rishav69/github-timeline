<?php
require __DIR__ . '/functions.php';

$msg = "";
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code  = trim($_POST['code'] ?? '');

    if (!$email || !$code) {
        $msg = "Email and code are required.";
    } else {
        $stored = trim(@file_get_contents(codeFile($email)));

        if ($stored === $code) {
            if (registerEmail($email)) {
                $msg = "Subscription successful for $email!";
            } else {
                $msg = "Failed to register email.";
            }
        } else {
            $msg = "Invalid verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify Email - GH Timeline</title></head>
<body>
<h2>Verify Your Email</h2>

<form method="POST" action="verify.php">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <label>Verification Code: <input type="text" name="code" required></label><br><br>
    <button type="submit">Verify</button>
</form>

<p><?= htmlspecialchars($msg) ?></p>
</body>
</html>
