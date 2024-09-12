<?php
session_start();
require 'db.php';

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember_me']);
    $captcha_response = $_POST['g-recaptcha-response'];

    // Verify reCAPTCHA v3 response
    $captcha_secret = '6LeMfD4qAAAAAPZWX_EXz8JWyyLk8Nuof0C18G4k'; // Replace with your reCAPTCHA v3 secret key
    $captcha_url = "https://www.google.com/recaptcha/api/siteverify?secret=$captcha_secret&response=$captcha_response";
    
    $captcha_verify = file_get_contents($captcha_url);
    $captcha_success = json_decode($captcha_verify);

    if ($captcha_success->success && $captcha_success->score >= 0.5) {
        // CAPTCHA passed, proceed with login
        if (!empty($username) && !empty($password)) {
            // Fetch user from database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // If "Remember Me" is checked, set a cookie that lasts 5 days
                if ($remember) {
                    setcookie('user_id', $user['id'], time() + (5 * 24 * 60 * 60), "/"); // 5 days
                    setcookie('username', $user['username'], time() + (5 * 24 * 60 * 60), "/");
                }

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Please enter both username and password.";
        }
    } else {
        $error = "CAPTCHA verification failed. Please try again.";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container">
    <h2 class="text-center mt-5">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="username">Username or Email:</label>
            <input type="text" name="username" class="form-control" id="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" class="form-control" id="password" required>
        </div>

        <!-- reCAPTCHA v3 token -->
        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

        <!-- Remember me checkbox -->
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
            <label class="form-check-label" for="remember_me">Remember me for 5 days</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>

        <!-- Forgot Password and Register links -->
        <div class="text-center mt-3">
            <a href="reset_password.php">Forgot your password?</a> | 
            <a href="register.php">Don't have an account? Register</a>
        </div>
    </form>
</div>

<!-- reCAPTCHA v3 Script -->
<script src="https://www.google.com/recaptcha/api.js?render=6LeMfD4qAAAAAEK7oWXQ5XSzOuzP4Av8wGv8e4cQ"></script> <!-- Replace with your site key -->
<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('6LeMfD4qAAAAAEK7oWXQ5XSzOuzP4Av8wGv8e4cQ', {action: 'login'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
</script>

<?php include 'footer.php'; ?>