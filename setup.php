<?php
/**
 * One-time setup script — run once after uploading files, then DELETE this file.
 * Visit: https://yoursite.infinityfreeapp.com/setup.php
 */

require_once __DIR__ . '/config.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = trim($_POST['username'] ?? 'admin');
    $adminPass = $_POST['password'] ?? 'admin123';

    if ($adminUser === '' || $adminPass === '') {
        $errors[] = 'Username and password are required.';
    } elseif (strlen($adminPass) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } else {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS suggestions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    full_name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    category ENUM('Suggestion', 'Complaint', 'Compliment', 'Recommendation') NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('Pending', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_category (category),
                    INDEX idx_status (status),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO admins (username, password) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE password = VALUES(password)'
            );
            $stmt->execute([$adminUser, $hash]);

            $messages[] = 'Database tables verified and admin account configured successfully.';
            $messages[] = 'IMPORTANT: Delete setup.php from your server now for security.';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            $errors[] = 'Check your config.php database credentials and ensure the database exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup — Swahilipot Suggestion Box</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 520px; margin: 3rem auto; padding: 0 1rem; color: #334155; }
        h1 { color: #0B4F8A; }
        .box { background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 1.5rem; margin-top: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.25rem; font-size: 0.875rem; }
        input { width: 100%; padding: 0.625rem; margin-bottom: 1rem; border: 1px solid #E2E8F0; border-radius: 8px; }
        button { background: #0B4F8A; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; }
        .ok { background: #D1FAE5; color: #065F46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .err { background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Swahilipot Suggestion Box Setup</h1>
    <p>Use this page once to create database tables and set your admin login. Delete this file afterward.</p>

    <?php foreach ($messages as $msg): ?>
        <div class="ok"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $err): ?>
        <div class="err"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <div class="box">
        <form method="POST">
            <label for="username">Admin Username</label>
            <input type="text" id="username" name="username" value="admin" required>

            <label for="password">Admin Password</label>
            <input type="password" id="password" name="password" value="admin123" required minlength="6">

            <button type="submit">Run Setup</button>
        </form>
    </div>
</body>
</html>
