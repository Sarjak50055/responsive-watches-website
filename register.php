<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php?view=my-drive");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        if (registerUser($pdo, $username, $email, $password)) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Email already exists or registration failed";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Google Drive Clone</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css?v=1.2">
    <script src="assets/js/theme.js"></script>
</head>

<body>
    <button class="icon-btn" id="themeToggle" title="Toggle theme" style="position: absolute; top: 16px; right: 16px; z-index: 100;">
        <span class="material-icons-outlined theme-icon-dark">dark_mode</span>
        <span class="material-icons-outlined theme-icon-light" style="display:none;">light_mode</span>
    </button>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <svg width="34" height="34" viewBox="0 0 24 24" class="drive-logo">
                    <path fill="#34A853" d="M15.43 3.5H8.57L2 15h6.86l6.57-11.5z"></path>
                    <path fill="#4285F4" d="M22 15l-6.57-11.5H8.57L15.43 15H22z"></path>
                    <path fill="#FBBC05" d="M5.43 21h13.14L22 15H8.86L5.43 21z"></path>
                </svg>
                Drive
            </div>
            <h1 class="auth-title">Create your Account</h1><br><br>
            <!-- <p class="auth-subtitle">to continue to Drive</p> -->

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-msg"
                    style="color: #0d652d; background-color: #e6f4ea; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-group split-inputs">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm" required>
                </div>

                <div class="auth-links">
                    <a href="login.php">Sign in instead</a>
                    <button type="submit" class="auth-btn" style="width: auto; padding: 10px 24px;">Register</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>