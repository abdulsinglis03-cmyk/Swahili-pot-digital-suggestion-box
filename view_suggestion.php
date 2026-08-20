<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireAdmin();

$pdo = getDB();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Invalid suggestion.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare('SELECT * FROM suggestions WHERE id = ? LIMIT 1');
$stmt->execute([$id]);

$suggestion = $stmt->fetch();

if (!$suggestion) {
    flash('error', 'Suggestion not found.');
    redirect('dashboard.php');
}

$successMessage = flash('success');
$errorMessage = flash('error');

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        View Suggestion — Swahilipot
    </title>

    <link
        rel="stylesheet"
        href="<?= e(asset('assets/css/style.css')) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= e(asset('assets/css/admin.css')) ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

</head>

<body class="dash-layout">

    <aside class="dash-sidebar">

        <nav class="dash-nav">

            <a
                href="dashboard.php"
                class="dash-nav-item"
            >

                <i class="fa-solid fa-house"></i>

                Dashboard

            </a>

            <a
                href="dashboard.php"
                class="dash-nav-item"
            >

                <i class="fa-solid fa-table-list"></i>

                All Suggestions

            </a>

            <a
                href="export.php"
                class="dash-nav-item"
            >

                <i class="fa-solid fa-file-csv"></i>

                Export to CSV

            </a>

            <a
                href="logout.php"
                class="dash-nav-item"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </nav>

    </aside>


    <div class="dash-content-wrap">

        <header class="dash-topbar">

            <a
                href="dashboard.php"
                class="dash-btn dash-btn-sm"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to Dashboard

            </a>

            <div class="dash-topbar-title">

                <span class="dash-eyebrow">

                    <i class="fa-solid fa-eye"></i>

                    Feedback Details

                </span>

                <h1>

                    View Suggestion

                </h1>

            </div>

        </header>


        <main class="dash-main">

            <?php if ($successMessage): ?>

                <div class="sp-alert sp-alert-success dash-alert">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= e($successMessage) ?>

                </div>

            <?php endif; ?>


            <?php if ($errorMessage): ?>

                <div class="sp-alert sp-alert-error dash-alert">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= e($errorMessage) ?>

                </div>

            <?php endif; ?>


            <section class="dash-panel
            view-suggestion-card">

                <div class="dash-analytics-header">

                    <div>

                        <h2>

                            Suggestion #<?= (int) $suggestion['id'] ?>

                        </h2>

                        <p>

                            Submitted on

                            <?= e(date('d M Y h:i A', strtotime($suggestion['created_at']))) ?>

                        </p>

                    </div>

                </div>


                <div class="suggestion-details">

                    <div class="suggestion-detail-item">

                        <strong>

                            <i class="fa-solid fa-user"></i>

                            Name

                        </strong>

                        <span>

                            <?= e($suggestion['name']) ?>

                        </span>

                    </div>


                    <div class="suggestion-detail-item">

                        <strong>

                            <i class="fa-solid fa-envelope"></i>

                            Email

                        </strong>

                        <span>

                            <?= e($suggestion['email'] ?: 'N/A') ?>

                        </span>

                    </div>


                    <div class="suggestion-detail-item">

                        <strong>

                            <i class="fa-solid fa-layer-group"></i>

                            Category

                        </strong>

                        <span>

                            <?= e(getCategoryLabel($suggestion['category'])) ?>

                        </span>

                    </div>


                    <div class="suggestion-detail-item">

                        <strong>

                            <i class="fa-solid fa-circle-info"></i>

                            Status

                        </strong>

                       <span class="dash-badge dash-status-<?= e(getStatusBadgeClass($suggestion['status'])) ?>">
    <?= e(getStatusLabel($suggestion['status'])) ?>
</span>

                    </div>


                    <div class="suggestion-detail-message">

                        <strong>

                            <i class="fa-solid fa-message"></i>

                            Full Suggestion / Feedback

                        </strong>

                        <p>

                            <?= nl2br(e($suggestion['message'])) ?>

                        </p>

                    </div>

                </div>

            </section>


            <section class="dash-panel">

    <h2>
        <i class="fa-solid fa-reply"></i>
        Admin Response
    </h2>

    <?php if (!empty($suggestion['admin_response'])): ?>

        <div class="sp-info-box">

            <strong>Response already sent:</strong>

            <p>
                <?= nl2br(e($suggestion['admin_response'])) ?>
            </p>

            <?php if (!empty($suggestion['responded_at'])): ?>

                <small>
                    Responded on:
                    <?= e(date('d M Y h:i A', strtotime($suggestion['responded_at']))) ?>
                </small>

            <?php endif; ?>

        </div>

    <?php endif; ?>

    <form method="GET" action="respond.php">

        <input
            type="hidden"
            name="id"
            value="<?= (int) $suggestion['id'] ?>"
        >

        <button
            type="submit"
            class="dash-btn dash-btn-primary"
        >

            <i class="fa-solid fa-reply"></i>

            <?= !empty($suggestion['admin_response'])
                ? 'Edit Response'
                : 'Respond to Feedback' ?>

        </button>

    </form>

</section>

        </main>

    </div>

</body>

</html>