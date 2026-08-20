<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "success" => false,
        "message" => "Only GET requests are allowed"
    ]);
    exit;
}

if ($apiAdmin['role'] === 'super_admin') {

    $stmt = $pdo->prepare("
        SELECT
            suggestions.id,
            suggestions.name,
            suggestions.email,
            suggestions.category,
            suggestions.message,
            suggestions.status,
            suggestions.created_at,
            branches.name AS branch
        FROM suggestions
        LEFT JOIN branches
        ON suggestions.branch_id = branches.id
        ORDER BY suggestions.created_at DESC
    ");

    $stmt->execute();

} else {

    $stmt = $pdo->prepare("
        SELECT
            suggestions.id,
            suggestions.name,
            suggestions.email,
            suggestions.category,
            suggestions.message,
            suggestions.status,
            suggestions.created_at,
            branches.name AS branch
        FROM suggestions
        LEFT JOIN branches
        ON suggestions.branch_id = branches.id
        WHERE suggestions.branch_id = ?
        ORDER BY suggestions.created_at DESC
    ");

    $stmt->execute([
        $apiAdmin['branch_id']
    ]);
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "count" => count($data),
    "suggestions" => $data
]);