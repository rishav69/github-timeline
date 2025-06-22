<?php
require __DIR__ . '/functions.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $msg = "Please enter a valid email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
    } else {
        $code = generateVerificationCode();
        if (sendVerificationEmail($email, $code)) {
            header("Location: verify.php?email=" . urlencode($email));
            exit;
        } else {
            $msg = "Failed to send verification email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Subscribe - GH Timeline</title></head>
<body>
<h2>Subscribe to GitHub Timeline</h2>

<form method="POST" action="index.php">
    <label>Email: <input type="email" name="email" required></label>
    <button type="submit">Send Verification Code</button>
</form>

<p><?= htmlspecialchars($msg) ?></p>

<p>Already have a code? <a href="verify.php">Verify here</a></p>
</body>
</html>
