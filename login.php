<?php
require_once 'includes/auth.php';

if (isLoggedIn() && !isset($_GET['add_account'])) {
    header("Location: index.php?view=my-drive");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (loginUser($pdo, $email, $password)) {
        header("Location: index.php?view=my-drive");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Google Drive Clone</title>
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
            <h1 class="auth-title">Sign in</h1><br><br>
            <!-- <p class="auth-subtitle">Use your Account</p> -->

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="auth-links">
                    <a href="register.php">Create account</a>
                    <button type="submit" class="auth-btn" style="width: auto; padding: 10px 24px;">Next</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>