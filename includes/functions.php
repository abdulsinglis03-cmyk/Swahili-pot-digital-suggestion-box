<?php

require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function getCategories(): array
{
    return ['Suggestion', 'Complaint', 'Compliment', 'Recommendation'];
}

function getStatuses(): array
{
    return ['Pending', 'In Progress', 'Resolved'];
}

function normalizeCategory(string $category): ?string
{
    $category = trim($category);
    return in_array($category, getCategories(), true) ? $category : null;
}

function normalizeStatus(string $status): ?string
{
    $status = trim($status);
    return in_array($status, getStatuses(), true) ? $status : null;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}

function asset(string $path): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

function getCategoryLabel(string $category): string
{
    $labels = [
        'Suggestion'     => 'Ideas',
        'Complaint'      => 'Complaints',
        'Compliment'     => 'Compliments',
        'Recommendation' => 'Recommendations',
    ];

    return $labels[$category] ?? $category;
}

function getStatusLabel(string $status): string
{
    $labels = [
        'Pending'     => 'New',
        'In Progress' => 'In Review',
        'Resolved'    => 'Resolved',
    ];

    return $labels[$status] ?? $status;
}

function getCategoryBadgeClass(string $category): string
{
    $classes = [
        'Suggestion'     => 'ideas',
        'Complaint'      => 'complaints',
        'Compliment'     => 'compliments',
        'Recommendation' => 'recommendations',
    ];

    return $classes[$category] ?? 'default';
}

function getStatusBadgeClass(string $status): string
{
    $classes = [
        'Pending'     => 'new',
        'In Progress' => 'in-review',
        'Resolved'    => 'resolved',
    ];

    return $classes[$status] ?? 'default';
}
