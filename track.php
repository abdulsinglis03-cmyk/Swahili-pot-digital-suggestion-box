<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$feedback = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $referenceCode = trim($_POST['reference_code'] ?? '');

    if ($referenceCode === '') {

        $error = 'Please enter your reference number.';

    } else {

        try {

            $pdo = getDB();

            $stmt = $pdo->prepare(
    'SELECT reference_code, category, message, status, admin_response, responded_at
     FROM suggestions
     WHERE reference_code = ?
     LIMIT 1'
);

            $stmt->execute([$referenceCode]);

            $feedback = $stmt->fetch();

            if (!$feedback) {

                $error = 'No feedback found with that reference number.';

            }

        } catch (PDOException $e) {

            $error = 'Unable to check your feedback right now.';

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Track Feedback — Swahilipot</title>

    <link rel="stylesheet"
          href="<?= e(asset('assets/css/style.css')) ?>">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link
    rel="icon"
    type="image/png"
    href="<?= e(asset('assets/images/logo.png')) ?>"
>

</head>

<body class="public-page">

    <header class="sp-header">

        <div class="sp-header-inner">

            <a href="index.php" class="sp-logo">

                <img
                    src="<?= e(asset('assets/images/logo.png')) ?>"
                    alt="Swahilipot"
                >

            </a>

        </div>

    </header>


    <main class="sp-main">

        <section class="sp-form-section">

            <div class="sp-form-card">

                <div class="sp-form-heading">

                    <div class="sp-hero-badge">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        Track Your Feedback

                    </div>

                    <h1>

                        Check Your Submission Status

                    </h1>

                    <p>

                        Enter the reference number you received after submitting your feedback.

                    </p>

                </div>


                <form method="POST">

                    <div class="sp-field">

                        <label for="reference_code">

                            <i class="fa-solid fa-hashtag"></i>

                            Reference Number

                        </label>

                        <input
                            type="text"
                            id="reference_code"
                            name="reference_code"
                            placeholder="Example: SP-2026-A7F3C2"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="sp-submit-btn"
                    >

                        <i class="fa-solid fa-magnifying-glass"></i>

                        CHECK STATUS

                    </button>

                </form>


                <?php if ($error): ?>

                    <div class="sp-alert sp-alert-error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <div>

                            <?= e($error) ?>

                        </div>

                    </div>

                <?php endif; ?>


                <?php if ($feedback): ?>

                    <div class="sp-alert sp-alert-success">

                        <i class="fa-solid fa-circle-check"></i>

                        <div>

                            <strong>

                                Feedback Found

                            </strong>

                            <p>

                                Reference Number:

                                <strong>

                                    <?= e($feedback['reference_code']) ?>

                                </strong>

                            </p>

                            <p>

                                Category:

                                <?= e($feedback['category']) ?>

                            </p>

                            <p>

                                Status:

                                <strong>

                                    <?= e($feedback['status']) ?>

                                </strong>

                            </p>


                            <?php if (!empty($feedback['admin_response'])): ?>

                                <hr>

                                <p>

                                    <strong>

                                        Response from Swahilipot:

                                    </strong>

                                </p>

                                <p>

                                    <?= nl2br(e($feedback['admin_response'])) ?>

                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>