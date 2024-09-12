<?php
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        // Check if email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->execute([$reset_token, $email]);

            // Send reset password email
            $reset_link = "https://yourwebsite.com/reset_password_confirm.php?token=" . $reset_token;
            $to = $user['email'];
            $subject = "Password Reset Request";
            $message = "Click the link below to reset your password:\n\n" . $reset_link;
            $headers = "From: no-reply@yourwebsite.com";

            if (mail($to, $subject, $message, $headers)) {
                $success = "A password reset link has been sent to your email.";
            } else {
                $error = "Failed to send the email. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container">
    <h2 class="text-center mt-5">Reset Password</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" name="email" class="form-control" id="email" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
</div>

<?php include 'footer.php'; ?>