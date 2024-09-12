<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user info
$stmt = $pdo->prepare("SELECT username, email, wallet_address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Update email
if (isset($_POST['update_email'])) {
    $new_email = $_POST['email'];
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$new_email, $user_id]);
    $message = "Email updated successfully!";
}

// Update wallet address
if (isset($_POST['update_wallet'])) {
    $new_wallet = $_POST['wallet_address'];
    $stmt = $pdo->prepare("UPDATE users SET wallet_address = ? WHERE id = ?");
    $stmt->execute([$new_wallet, $user_id]);
    $message = "Wallet address updated successfully!";
}

// Update password
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_password = $stmt->fetchColumn();

    if (password_verify($current_password, $user_password)) {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        $message = "Password updated successfully!";
    } else {
        $message = "Incorrect current password!";
    }
}
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Profile</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message; ?></div>
<?php endif; ?>

<form method="post">
    <h4>Update Email</h4>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" class="form-control" name="email" id="email" value="<?= $user['email']; ?>" required>
    </div>
    <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
</form>

<form method="post" class="mt-4">
    <h4>Update Wallet Address</h4>
    <div class="form-group">
        <label for="wallet_address">Wallet Address:</label>
        <input type="text" class="form-control" name="wallet_address" id="wallet_address" value="<?= $user['wallet_address']; ?>" required>
    </div>
    <button type="submit" name="update_wallet" class="btn btn-primary">Update Wallet Address</button>
</form>

<form method="post" class="mt-4">
    <h4>Update Password</h4>
    <div class="form-group">
        <label for="current_password">Current Password:</label>
        <input type="password" class="form-control" name="current_password" id="current_password" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password:</label>
        <input type="password" class="form-control" name="new_password" id="new_password" required>
    </div>
    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
</form>

<?php include 'footer.php'; ?>