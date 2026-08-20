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
    flash('error', 'Invalid feedback selected.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare(
    'SELECT *
     FROM suggestions
     WHERE id = ?
     LIMIT 1'
);

$stmt->execute([$id]);

$suggestion = $stmt->fetch();

if (!$suggestion) {
    flash('error', 'Feedback not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {

        flash('error', 'Invalid request. Please try again.');

    } else {

        $response = trim($_POST['admin_response'] ?? '');

        if ($response === '') {

            flash('error', 'Please write a response.');

        } else {

            $stmt = $pdo->prepare(
                'UPDATE suggestions
                 SET admin_response = ?,
                     responded_at = NOW(),
                     status = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $response,
                'Resolved',
                $id
            ]);

            flash('success', 'Response sent successfully.');

            redirect('dashboard.php');

        }

    }

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

    <title>Respond to Feedback — Swahilipot</title>

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

    <div class="dash-content-wrap">

        <header class="dash-topbar">

            <div class="dash-topbar-title">

                <span class="dash-eyebrow">

                    <i class="fa-solid fa-reply"></i>

                    Admin Response

                </span>

                <h1>Respond to Feedback</h1>

                <p>
                    Review the feedback and send an official response.
                </p>

            </div>

        </header>


        <main class="dash-main">

            <?php if ($successMessage): ?>

                <div class="sp-alert sp-alert-success">

                    <i class="fa-solid fa-circle-check"></i>

                    <?= e($successMessage) ?>

                </div>

            <?php endif; ?>


            <?php if ($errorMessage): ?>

                <div class="sp-alert sp-alert-error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= e($errorMessage) ?>

                </div>

            <?php endif; ?>


            <section class="dash-panel
             feedback-detail-card">
               
                <div class="dash-panel-header">

                    <div>

                        <h2>Feedback Details</h2>

                        <p>
                            Reference:
                            <strong>
                                <?= e($suggestion['reference_code']) ?>
                            </strong>
                        </p>

                    </div>

                    <a
                        href="dashboard.php"
                        class="dash-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Back to Dashboard

                    </a>

                </div>


                <div class="feedback-info-item">
    <span>Name</span>
    <strong><?= e($suggestion['name']) ?></strong>
</div>

<div class="feedback-info-item">
    <span>Email</span>
    <strong><?= e($suggestion['email']) ?></strong>
</div>

<div class="feedback-info-item">
    <span>Category</span>
    <strong><?= e($suggestion['category']) ?></strong>
</div>

<div class="feedback-info-item">
    <span>Status</span>
    <strong><?= e($suggestion['status']) ?></strong>
</div>
                    <p>

                        <?= nl2br(e($suggestion['message'])) ?>

                    </p>

                </div>


                <form method="POST">

                    <?= csrfField() ?>


                    <div class="sp-field">

                        <label for="admin_response">

                            <i class="fa-solid fa-reply"></i>

                            Your Response

                        </label>


                        <textarea
                            id="admin_response"
                            name="admin_response"
                            rows="8"
                            placeholder="Write your response to this feedback..."
                            required
                        ><?= e($suggestion['admin_response'] ?? '') ?></textarea>

                    </div>


                    <button
                        type="submit"
                        class="dash-btn dash-btn-primary"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Send Response

                    </button>

                </form>

            </section>

        </main>

    </div>

</body>

</html>