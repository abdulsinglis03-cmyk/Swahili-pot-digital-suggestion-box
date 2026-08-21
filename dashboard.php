<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/auth.php';
startSecureSession();
requireAdmin();

$pdo = getDB();
$categories = getCategories();
$statuses = getStatuses();

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 5;

if ($category !== '' && !in_array($category, $categories, true)) {
    $category = '';
}
if ($status !== '' && !in_array($status, $statuses, true)) {
    $status = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        flash('error', 'Invalid request. Status was not updated.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $newStatus = normalizeStatus($_POST['status'] ?? '');

        if ($id > 0 && $newStatus) {
            $stmt = $pdo->prepare('UPDATE suggestions SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $id]);
            flash('success', 'Status updated successfully.');
        } else {
            flash('error', 'Invalid status update request.');
        }
    }

    $query = http_build_query(array_filter([
        'search'   => $search,
        'category' => $category,
        'status'   => $status,
        'page'     => $page > 1 ? $page : null,
    ]));
    redirect('dashboard.php' . ($query ? '?' . $query : ''));
}

$stats = [
    'total'          => (int) $pdo->query('SELECT COUNT(*) FROM suggestions')->fetchColumn(),
    'suggestions'    => (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE category = 'Suggestion'")->fetchColumn(),
    'complaints'     => (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE category = 'Complaint'")->fetchColumn(),
    'compliments'    => (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE category = 'Compliment'")->fetchColumn(),
    'recommendations'=> (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE category = 'Recommendation'")->fetchColumn(),
    'resolved'       => (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE status = 'Resolved'")->fetchColumn(),
    'pending'        => (int) $pdo->query("SELECT COUNT(*) FROM suggestions WHERE status = 'Pending'")->fetchColumn(),
];

$where = [];
$params = [];


if ($_SESSION['admin_role'] !== 'super_admin') {
    $where[] = 'suggestions.branch_id = ?';
    $params[] = $_SESSION['branch_id'];
}


if ($search !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR message LIKE ? OR category LIKE ?)';
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

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM suggestions' . $whereClause);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "
SELECT
    suggestions.*,
    branches.name AS branch_name
FROM suggestions
LEFT JOIN branches
ON suggestions.branch_id = branches.id
" . $whereClause . "
ORDER BY suggestions.created_at DESC
LIMIT " . (int)$perPage . "
OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suggestions = $stmt->fetchAll();

$successMessage = flash('success');
$errorMessage = flash('error');
$categoryChart = [
    'Suggestion'     => $stats['suggestions'],
    'Complaint'      => $stats['complaints'],
    'Compliment'     => $stats['compliments'],
    'Recommendation' => $stats['recommendations'],
];

$statusChart = [
    'Pending'  => $stats['pending'],
    'Resolved' => $stats['resolved'],
];

$queryParams = array_filter([
    'search'   => $search,
    'category' => $category,
    'status'   => $status,
]);

function dashUrl(array $extra = []): string
{
    global $queryParams;
    $params = array_filter(array_merge($queryParams, $extra));
    $qs = http_build_query($params);
    return 'dashboard.php' . ($qs ? '?' . $qs : '');
}

$showFrom = $totalRows > 0 ? $offset + 1 : 0;
$showTo = min($offset + $perPage, $totalRows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggestion Box Dashboard — Swahilipot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
    <link
    rel="icon"
    type="image/png"
    href="<?= e(asset('assets/images/logo.png')) ?>"
>
</head>
<body class="dash-layout">
    <aside class="dash-sidebar" id="sidebar">
        <nav class="dash-nav">
            <a href="dashboard.php" class="dash-nav-item active">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="<?= e(dashUrl(['category' => null, 'status' => null, 'search' => null, 'page' => null])) ?>" class="dash-nav-item">
                <i class="fa-solid fa-table-list"></i> All Suggestions
            </a>
            <a href="export.php?<?= e(http_build_query($queryParams)) ?>" class="dash-nav-item">
                <i class="fa-solid fa-file-csv"></i> Export to CSV
            </a>
            <a href="logout.php" class="dash-nav-item">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </nav>
        <div class="dash-sidebar-footer">
            <div class="dash-pot-watermark">
                <i class="fa-solid fa-kitchen-set"></i>
            </div>
            <p>Your feedback helps us serve you better every day.</p>
        </div>
    </aside>

    <div class="dash-content-wrap">
        <header class="dash-topbar">
            <button class="dash-menu-toggle" id="menuToggle" aria-label="Toggle menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="index.php" class="sp-logo sp-logo-sm">
                <img src="<?= e(asset('assets/images/logo.png')) ?>" alt="Swahilipot">
            </a>
           <div class="dash-topbar-title">
    <span class="dash-eyebrow">
        <i class="fa-solid fa-chart-line"></i>
        Admin Overview
    </span>

    <h1>Suggestion Box Dashboard</h1>

    <p>
        Monitor feedback, track progress, and help improve Swahilipot.
    </p>
</div>
            <div class="dash-top-actions">

    <div class="dash-user-menu">
        <div class="dash-user-avatar">
            <i class="fa-solid fa-user"></i>
        </div>
        <span><?= e($_SESSION['admin_username']) ?></span>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</div>
        </header>

        <main class="dash-main">
            <?php if ($successMessage): ?>
                <div class="sp-alert sp-alert-success dash-alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <div><?= e($successMessage) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="sp-alert sp-alert-error dash-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><?= e($errorMessage) ?></div>
                </div>
            <?php endif; ?>

            <section class="dash-stats">
                <article class="dash-stat-card">
                    <div class="dash-stat-icon dash-stat-blue"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="dash-stat-body">
                        <span class="dash-stat-num"><?= number_format($stats['total']) ?></span>
                        <span class="dash-stat-label">Total Suggestions</span>
                        <a href="<?= e(dashUrl(['category' => null, 'status' => null, 'page' => null])) ?>" class="dash-stat-link dash-link-blue">View all</a>
                    </div>
                </article>
                <article class="dash-stat-card">
                    <div class="dash-stat-icon dash-stat-green"><i class="fa-solid fa-lightbulb"></i></div>
                    <div class="dash-stat-body">
                        <span class="dash-stat-num"><?= number_format($stats['suggestions']) ?></span>
                        <span class="dash-stat-label">Ideas</span>
                        <a href="<?= e(dashUrl(['category' => 'Suggestion', 'page' => null])) ?>" class="dash-stat-link dash-link-green">View all</a>
                    </div>
                </article>
                <article class="dash-stat-card">
                    <div class="dash-stat-icon dash-stat-orange"><i class="fa-solid fa-comments"></i></div>
                    <div class="dash-stat-body">
                        <span class="dash-stat-num"><?= number_format($stats['complaints']) ?></span>
                        <span class="dash-stat-label">Complaints</span>
                        <a href="<?= e(dashUrl(['category' => 'Complaint', 'page' => null])) ?>" class="dash-stat-link dash-link-orange">View all</a>
                    </div>
                </article>
                <article class="dash-stat-card">
                    <div class="dash-stat-icon dash-stat-purple"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="dash-stat-body">
                        <span class="dash-stat-num"><?= number_format($stats['resolved']) ?></span>
                        <span class="dash-stat-label">Resolved</span>
                        <a href="<?= e(dashUrl(['status' => 'Resolved', 'page' => null])) ?>" class="dash-stat-link dash-link-purple">View all</a>
                    </div>
                </article>
            </section>
<section class="dash-analytics">

    <div class="dash-analytics-card">
        <div class="dash-analytics-header">
            <div>
                <h2>Feedback by Category</h2>
                <p>Overview of the feedback received</p>
            </div>
            <i class="fa-solid fa-chart-pie"></i>
        </div>

        <div class="dash-category-bars">
            <?php foreach ($categoryChart as $label => $count): ?>
                <div class="dash-category-row">
                    <div class="dash-category-label">
                        <span><?= e($label) ?></span>
                        <strong><?= number_format($count) ?></strong>
                    </div>

                    <div class="dash-progress">
                        <div
                            class="dash-progress-fill"
                            style="width: <?= $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 ?>%">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <div class="dash-analytics-card">
        <div class="dash-analytics-header">
            <div>
                <h2>Feedback Status</h2>
                <p>Track your feedback progress</p>
            </div>
            <i class="fa-solid fa-chart-simple"></i>
        </div>

        <div class="dash-status-summary">

            <div class="dash-status-item pending">
                <span class="dash-status-number">
                    <?= number_format($stats['pending']) ?>
                </span>
                <span>Pending</span>
            </div>

            <div class="dash-status-item resolved">
                <span class="dash-status-number">
                    <?= number_format($stats['resolved']) ?>
                </span>
                <span>Resolved</span>
            </div>

        </div>
    </div>

</section>
            <section class="dash-panel">
                <form method="GET" action="dashboard.php" class="dash-search-bar">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= e($category) ?>">
                    <?php endif; ?>
                    <?php if ($status): ?>
                        <input type="hidden" name="status" value="<?= e($status) ?>">
                    <?php endif; ?>
                    <div class="dash-search-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="search" value="<?= e($search) ?>"
                            placeholder="Search by name, category or suggestion...">
                    </div>
                    <button type="submit" class="dash-btn dash-btn-primary">Search</button>
                    <a href="export.php?<?= e(http_build_query($queryParams)) ?>" class="dash-btn dash-btn-export">
                        <i class="fa-solid fa-download"></i> Export to CSV
                    </a>
                </form>

                <?php if ($category || $status): ?>
                    <div class="dash-active-filters">
                        <?php if ($category): ?>
                            <span class="dash-filter-tag">Category: <?= e(getCategoryLabel($category)) ?>
                                <a href="<?= e(dashUrl(['category' => null, 'page' => null])) ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                        <?php if ($status): ?>
                            <span class="dash-filter-tag">Status: <?= e(getStatusLabel($status)) ?>
                                <a href="<?= e(dashUrl(['status' => null, 'page' => null])) ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>Suggestion</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suggestions)): ?>
                                <tr>
                                    <td colspan="8" class="dash-empty">No suggestions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($suggestions as $row): ?>
                                    <tr>
                                        <td data-label="ID"><?= (int) $row['id'] ?></td>
                                        <td data-label="Name"><?= e($row['name']) ?></td>
                                        <td data-label="Branch">
    <?= e($row['branch_name'] ?? 'Unknown') ?>
</td>
                                        <td data-label="Category">
                                            <span class="dash-badge dash-badge-<?= e(getCategoryBadgeClass($row['category'])) ?>">
                                                <?= e(getCategoryLabel($row['category'])) ?>
                                            </span>
                                        </td>
                                        <td data-label="Suggestion">
                                            <span class="dash-msg" title="<?= e($row['message']) ?>">
                                                <?= e(mb_strlen($row['message']) > 60 ? mb_substr($row['message'], 0, 60) . '…' : $row['message']) ?>
                                            </span>
                                        </td>
                                        <td data-label="Status">
                                            <span class="dash-badge dash-status-<?= e(getStatusBadgeClass($row['status'])) ?>">
                                                <?= e(getStatusLabel($row['status'])) ?>
                                            </span>
                                        </td>
                                        <td data-label="Date"><?= e(date('d M Y h:i A', strtotime($row['created_at']))) ?></td>
                                       <td data-label="Action">
<a href="view_suggestion.php?id=<?= (int) $row['id'] ?>" class="dash-btn dash-btn-sm">
    <i class="fa-solid fa-eye"></i> View
</a>
    <form method="POST"
          action="<?= e(dashUrl(['page' => $page > 1 ? $page : null])) ?>"
          class="dash-status-form">

        <?= csrfField() ?>

        <input type="hidden"
               name="action"
               value="update_status">

        <input type="hidden"
               name="id"
               value="<?= (int) $row['id'] ?>">

        <select name="status"
                aria-label="Update status">

            <?php foreach ($statuses as $st): ?>

                <option value="<?= e($st) ?>"
                    <?= $row['status'] === $st ? 'selected' : '' ?>>

                    <?= e(getStatusLabel($st)) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit"
                class="dash-btn dash-btn-sm">

            Update

        </button>

    </form>


    <a href="respond.php?id=<?= (int) $row['id'] ?>"
       class="dash-btn dash-btn-sm"
       style="margin-top: 6px; display: inline-block;">

        <i class="fa-solid fa-reply"></i>

        Respond

    </a>

</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalRows > 0): ?>
                    <div class="dash-pagination">
                        <span class="dash-page-info">
                            Showing <?= $showFrom ?> to <?= $showTo ?> of <?= number_format($totalRows) ?> entries
                        </span>
                        <div class="dash-page-btns">
                            <?php if ($page > 1): ?>
                                <a href="<?= e(dashUrl(['page' => $page - 1])) ?>" class="dash-page-btn">Previous</a>
                            <?php else: ?>
                                <span class="dash-page-btn disabled">Previous</span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            $startPage = max(1, $endPage - 4);

                            if ($startPage > 1): ?>
                                <a href="<?= e(dashUrl(['page' => 1])) ?>" class="dash-page-btn">1</a>
                                <?php if ($startPage > 2): ?><span class="dash-page-ellipsis">...</span><?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i === $page): ?>
                                    <span class="dash-page-btn active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= e(dashUrl(['page' => $i])) ?>" class="dash-page-btn"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?><span class="dash-page-ellipsis">...</span><?php endif; ?>
                                <a href="<?= e(dashUrl(['page' => $totalPages])) ?>" class="dash-page-btn"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= e(dashUrl(['page' => $page + 1])) ?>" class="dash-page-btn">Next</a>
                            <?php else: ?>
                                <span class="dash-page-btn disabled">Next</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <footer class="dash-footer">
            <p>&copy; <?= date('Y') ?> Swahilipot. All rights reserved.</p>
        </footer>
    </div>

    <script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
