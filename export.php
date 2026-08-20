<?php
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireAdmin();

$pdo = getDB();
$categories = getCategories();
$statuses = getStatuses();

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');

if ($category !== '' && !in_array($category, $categories, true)) {
    $category = '';
}
if ($status !== '' && !in_array($status, $statuses, true)) {
    $status = '';
}

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR message LIKE ? OR category LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
}

if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
}

$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="swahilipot-suggestions-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Category', 'Suggestion', 'Status', 'Date']);

$stmt = $pdo->prepare('SELECT * FROM suggestions' . $whereClause . ' ORDER BY created_at DESC');
$stmt->execute($params);

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['email'],
        getCategoryLabel($row['category']),
        $row['message'],
        getStatusLabel($row['status']),
        date('d M Y h:i A', strtotime($row['created_at'])),
    ]);
}

fclose($output);
exit;
