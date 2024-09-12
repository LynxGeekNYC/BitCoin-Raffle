<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $wallet_address = $_POST['wallet_address'];
    $referral_code = uniqid();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->rowCount() > 0) {
        $error = "Username or email already exists!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, wallet_address, referral_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $wallet_address, $referral_code]);

        $verification_link = "https://www.bitcoinraffletix.com/game/verify.php?email=" . $email . "&code=" . $referral_code;
        mail($email, "Verify your email", "Click this link to verify your email: " . $verification_link);

        $success = "Registration successful! Please check your email to verify your account.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Register</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Register</h2>
    
    <p><strong>NOTE:</strong> We are still in Beta Mode and the game is not live yet! Please register to be on the waiting list. Once the game is live, you will be notified via email when you are able to play. Upon registration,<br />
please provide a BITCOIN Address for withdrawal. We are not responsible if you provided a wrong bitcoin address.&nbsp;</p>

<p><span style="color:#c0392b;"><strong>WARNING:</strong> </span>We have no support yet. If anyone claiming to offer support from this platform, they are a scammer. Support will only be available via a ticketing system. We will never contact you.</p>

<p><strong>E-Mail Verification:</strong>&nbsp;<strong>Check your e-mail spam folder if you don&#39;t receive email verification.&nbsp;</strong></p>

<p></p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="wallet_address">Wallet Address:</label>
            <input type="text" class="form-control" name="wallet_address" id="wallet_address" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
</div>
</body>
</html>