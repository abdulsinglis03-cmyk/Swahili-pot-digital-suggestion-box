<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/auth.php';

startSecureSession();

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (loginAdmin($username, $password)) {
    redirect('dashboard.php');
} else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Swahilipot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <img src="<?= e(asset('assets/images/logo.png')) ?>" alt="Swahilipot" class="login-logo">
                <h1>Admin Login</h1>
                <p>Sign in to manage suggestion box submissions</p>
            </div>

            <?php if ($error): ?>
                <div class="sp-alert sp-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><?= e($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($flash = flash('error')): ?>
                <div class="sp-alert sp-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><?= e($flash) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <?= csrfField() ?>

                <div class="sp-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($username) ?>"
                        placeholder="Enter your username" autocomplete="username" required autofocus>
                </div>

                <div class="sp-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="sp-submit-btn">Sign In</button>
            </form>

            <p class="login-footer">
                <a href="index.php">&larr; Back to Suggestion Box</a>
            </p>
        </div>
    </div>
</body>
</html>
