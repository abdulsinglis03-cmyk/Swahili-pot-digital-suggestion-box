<?php

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed"
    ]);

    exit;
}


$data = json_decode(file_get_contents("php://input"), true);


$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$branch_id = (int) ($data['branch_id'] ?? 0);
$category = trim($data['category'] ?? '');
$message = trim($data['message'] ?? '');


// Validation

if ($name === '' || $branch_id <= 0 || $category === '' || $message === '') {

    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);

    exit;
}


// Save suggestion

$stmt = $pdo->prepare("
INSERT INTO suggestions
(name, email, branch_id, category, message, status)
VALUES (?, ?, ?, ?, ?, 'Pending')
");


$stmt->execute([
    $name,
    $email,
    $branch_id,
    $category,
    $message
]);


echo json_encode([
    "success" => true,
    "message" => "Suggestion submitted successfully"
]);