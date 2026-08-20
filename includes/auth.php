	<?php

require_once __DIR__ . '/functions.php';

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        flash('error', 'Please log in to access the admin dashboard.');
        redirect('login.php');
    }
}

function loginAdmin(string $username, string $password): bool
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT id, username, password, role, branch_id
        FROM admins
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['branch_id'] = $admin['branch_id'];

    return true;
}

function logoutAdmin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
