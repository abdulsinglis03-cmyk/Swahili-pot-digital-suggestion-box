<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

startSecureSession();
$errors = [];

$formData = [
    'full_name' => '',
    'email'     => '',
    'category'  => '',
    'message'   => '',
    'branch_id' => '',
    'anonymous' => false,
];
$submitted = false;
$submittedReferenceCode = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {

        $errors[] = 'Invalid form submission. Please try again.';

    } else {

        $formData['anonymous'] = !empty($_POST['anonymous']);

        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $formData['email']     = trim($_POST['email'] ?? '');
        $formData['category']  = trim($_POST['category'] ?? '');
        $formData['message']   = trim($_POST['message'] ?? '');
        $formData['branch_id'] = (int)($_POST['branch_id'] ?? 0);

        

        if ($formData['anonymous']) {

            $formData['full_name'] = 'Anonymous';
            $formData['email'] = '';

        } else {

            if ($formData['full_name'] === '') {

                $errors[] = 'Full name is required.';

            } elseif (strlen($formData['full_name']) > 100) {

                $errors[] = 'Full name must not exceed 100 characters.';

            }

            if ($formData['email'] !== '' && !isValidEmail($formData['email'])) {

                $errors[] = 'Please enter a valid email address.';

            } elseif (strlen($formData['email']) > 150) {

                $errors[] = 'Email must not exceed 150 characters.';

            }

        }



if ($formData['branch_id'] <= 0) {
    $errors[] = 'Please select a Swahilipot branch.';
}
        
        

        if (!normalizeCategory($formData['category'])) {

            $errors[] = 'Please select a category.';

        }

      

        if ($formData['message'] === '') {

            $errors[] = 'Suggestion or feedback is required.';

        } elseif (strlen($formData['message']) < 10) {

            $errors[] = 'Please provide at least 10 characters of feedback.';

        } elseif (strlen($formData['message']) > 500) {

            $errors[] = 'Feedback must not exceed 500 characters.';

        }

        if (empty($errors)) {
    try {
        $pdo = getDB();

        
        $referenceCode = 'SP-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

$stmt = $pdo->prepare(
    'INSERT INTO suggestions
     (reference_code, name, email, category, message, is_anonymous, status,branch_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([
    $referenceCode,
    $formData['full_name'],
    $formData['email'] !== '' ? $formData['email'] : null,
    $formData['category'],
    $formData['message'],
    $formData['anonymous'] ? 1 : 0,
    'Pending',
    $formData['branch_id']
]);

        $submitted = true;
        $submittedReferenceCode = $referenceCode;

        $formData = [
            'full_name' => '',
            'email'     => '',
            'category'  => '',
            'message'   => '',
            'anonymous' => false,
        ];

    } catch (PDOException $e) {
        die($e->getMessage());
    }
}

    }

}

$categories = getCategories();

$categoryMeta = [

    'Suggestion' => [

        'icon'  => 'fa-lightbulb',
        'color' => 'green',
        'desc'  => 'I have an idea'

    ],

    'Complaint' => [

        'icon'  => 'fa-triangle-exclamation',
        'color' => 'red',
        'desc'  => 'I want to report an issue'

    ],

    'Compliment' => [

        'icon'  => 'fa-heart',
        'color' => 'pink',
        'desc'  => 'I want to appreciate something'

    ],

    'Recommendation' => [

        'icon'  => 'fa-bullhorn',
        'color' => 'blue',
        'desc'  => 'I have a recommendation'

    ],

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Share your suggestions, complaints, compliments, and recommendations with Swahilipot."
    >

    <title>Digital Suggestion Box — Swahilipot</title>

    <link
    rel="icon"
    type="image/png"
    href="<?= e(asset('assets/images/logo.png')) ?>"
>
    
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= e(asset('assets/css/style.css')) ?>"
    >

</head>

<body class="public-page">

    <div class="sp-scroll-progress">

        <div id="scrollProgress"></div>

    </div>


    <header class="sp-header">

        <div class="sp-header-inner">

            <a
                href="index.php"
                class="sp-logo"
            >

                <img
                    src="<?= e(asset('assets/images/logo.png')) ?>"
                    alt="Swahilipot"
                >

            </a>

        </div>

    </header>


    <main class="sp-main">


        <section class="sp-hero">

            <div class="sp-hero-content">


                <div class="sp-hero-badge">

                    <i class="fa-solid fa-comments"></i>

                    Your Voice Matters

                </div>


                <h1>

                    Your Voice Can Shape

                    <span>A Better Swahilipot.</span>

                </h1>


                <p class="sp-hero-description">

                    Share your ideas, report challenges, appreciate what is working,

                    and help us build a stronger community together.

                </p>


                <div class="sp-hero-actions">


                    <a
                        href="#feedbackForm"
                        class="sp-hero-btn"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Share Your Feedback

                    </a>


                </div>


                <div class="sp-hero-note">

                    <i class="fa-solid fa-shield-halved"></i>

                    Anonymous submissions are welcome

                </div>


            </div>


            <div class="sp-hero-visual">


                <div class="sp-hero-icon-card">


                    <i class="fa-solid fa-comments"></i>


                    <div class="sp-floating-icon icon-one">

                        <i class="fa-solid fa-lightbulb"></i>

                    </div>


                    <div class="sp-floating-icon icon-two">

                        <i class="fa-solid fa-heart"></i>

                    </div>


                    <div class="sp-floating-icon icon-three">

                        <i class="fa-solid fa-bullhorn"></i>

                    </div>


                </div>


            </div>


        </section>


        <section class="sp-form-section">


            <div class="sp-form-card">


                <?php if ($submitted): ?>


                    <div
                        class="sp-alert sp-alert-success"
                        role="alert"
                    >

                        <i class="fa-solid fa-circle-check"></i>


                        <div>


                            <strong>

                                Thank you for your feedback!

                            </strong>


                            <p>
    Your submission has been received successfully.
    Our team will review it shortly.
</p>

<div class="sp-reference-box">
    <strong>Your Reference Number</strong>
    <span><?= e($submittedReferenceCode) ?></span>
    <small>Save this number to track your feedback later.</small>
</div>


                            <button
                                type="button"
                                class="sp-new-feedback-btn"
                                id="newFeedbackBtn"
                            >

                                <i class="fa-solid fa-plus"></i>

                                Submit Another Feedback

                            </button>


                        </div>


                    </div>


                <?php endif; ?>


                <?php if (!empty($errors)): ?>


                    <div
                        class="sp-alert sp-alert-error"
                        role="alert"
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>


                        <div>


                            <strong>

                                Please fix the following:

                            </strong>


                            <ul>


                                <?php foreach ($errors as $error): ?>


                                    <li>

                                        <?= e($error) ?>

                                    </li>


                                <?php endforeach; ?>


                            </ul>


                        </div>


                    </div>


                <?php endif; ?>


                <form
                    method="POST"
                    action="index.php"
                    class="sp-feedback-form"
                    id="feedbackForm"
                    novalidate
                >


                    <?= csrfField() ?>


                    <div class="sp-form-columns">


                        <div class="sp-form-left">


                            <div class="sp-field">


                                <label for="full_name">

                                    <i class="fa-solid fa-user sp-field-icon"></i>

                                    Your Name

                                </label>


                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    value="<?= e($formData['anonymous'] ? '' : $formData['full_name']) ?>"
                                    placeholder="Enter your full name"
                                    maxlength="100"
                                >


                            </div>


                            <div class="sp-field">


                                <label for="email">

                                    <i class="fa-solid fa-envelope sp-field-icon"></i>

                                    Your Email (Optional)

                                </label>


                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= e($formData['email']) ?>"
                                    placeholder="Enter your email address"
                                    maxlength="150"
                                >


                            </div>

<div class="sp-field">

    <label for="branch_id">

        <i class="fa-solid fa-building sp-field-icon"></i>

        Swahilipot Branch

    </label>

    <select
        id="branch_id"
        name="branch_id"
        required
    >

        <option value="" disabled selected>
            Select your branch
        </option>

        <option value="1">
            Swahilipot Hub
        </option>

        <option value="2">
            Swahilipot FM
        </option>

        <option value="3">
            Swahilipot Kilifi
        </option>

    </select>

</div>
                            <div class="sp-field">


                                <label for="category">

                                    <i class="fa-solid fa-list sp-field-icon"></i>

                                    Category

                                </label>


                                <select
                                    id="category"
                                    name="category"
                                    required
                                >


                                    <option
                                        value=""
                                        disabled
                                        <?= $formData['category'] === '' ? 'selected' : '' ?>
                                    >

                                        Select a category

                                    </option>


                                    <?php foreach ($categories as $cat): ?>


                                        <option
                                            value="<?= e($cat) ?>"
                                            <?= $formData['category'] === $cat ? 'selected' : '' ?>
                                        >

                                            <?= e($cat) ?>

                                        </option>


                                    <?php endforeach; ?>


                                </select>


                            </div>


                            <div class="sp-category-grid">


                                <?php foreach ($categories as $cat): ?>


                                    <?php

                                    $meta = $categoryMeta[$cat];

                                    ?>


                                    <button
                                        type="button"
                                        class="sp-category-tile sp-cat-<?= e($meta['color']) ?><?= $formData['category'] === $cat ? ' active' : '' ?>"
                                        data-category="<?= e($cat) ?>"
                                    >


                                        <i
                                            class="fa-solid <?= e($meta['icon']) ?>"
                                        ></i>


                                        <span class="sp-cat-title">

                                            <?= e($cat) ?>

                                        </span>


                                        <span class="sp-cat-desc">

                                            <?= e($meta['desc']) ?>

                                        </span>


                                    </button>


                                <?php endforeach; ?>


                            </div>


                            <label class="sp-anonymous">


                                <input
                                    type="checkbox"
                                    id="anonymous"
                                    name="anonymous"
                                    value="1"
                                    <?= $formData['anonymous'] ? 'checked' : '' ?>
                                >


                                <span>

                                    Submit anonymously

                                </span>


                            </label>


                        </div>


                        <div class="sp-form-right">


                            <div class="sp-field sp-field-grow">


                                <label for="message">


                                    <i class="fa-solid fa-pencil sp-field-icon"></i>

                                    Your Suggestion / Complaint / Feedback


                                </label>


                                <textarea
                                    id="message"
                                    name="message"
                                    rows="10"
                                    placeholder="Type your feedback here..."
                                    maxlength="500"
                                    required
                                ><?= e($formData['message']) ?></textarea>


                                <span class="sp-char-count">


                                    <span id="charCount">0</span>

                                    / 500 characters


                                </span>


                            </div>


                            <div class="sp-info-box">


                                <i class="fa-solid fa-circle-info"></i>


                                <p>


                                    <strong>Please note:</strong>

                                    Your feedback helps us improve our services.

                                    We appreciate your time and honesty.


                                </p>


                            </div>


                        </div>


                    </div>


                    <button
                        type="submit"
                        class="sp-submit-btn"
                    >


                        <i class="fa-solid fa-paper-plane"></i>

                        SUBMIT SUGGESTION


                    </button>


                </form>


            </div>


        </section>


    </main>


    <footer class="sp-footer">


        <div class="sp-footer-inner">


            <div class="sp-footer-col sp-footer-logo">


                <img
                    src="<?= e(asset('assets/images/logo.png')) ?>"
                    alt="Swahilipot"
                >


            </div>


            <div class="sp-footer-col">


                <h3>

                    About Swahilipot

                </h3>


                <p>

                    Swahilipot is an organization that empowers young people

                    and communities to embrace and use technology for innovation,

                    growth, and development.

                    The digital suggestion box was proudly made by Abdul Singlis.

                </p>


            </div>


            <div class="sp-footer-col">


                <h3>

                    Quick Links

                </h3>


                <ul>


                    <li>

                        <a href="#">

                            About Us

                        </a>

                    </li>


                    <li>

                        <a href="#">

                            Contact Us

                        </a>

                    </li>


                    <li>
    <a href="track.php">
        Track Feedback
    </a>
</li>

<li>
    <a href="login.php">
        Admin Login
    </a>
</li>


                </ul>


            </div>


            <div class="sp-footer-col">


                <h3>

                    Connect With Us

                </h3>


                <div class="sp-social">

    <!-- Facebook -->
    <a
        href="https://www.facebook.com/swahilipothub"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Swahilipot Hub on Facebook"
        title="Facebook"
    >
        <i class="fa-brands fa-facebook-f"></i>
    </a>


    <!-- Instagram -->
    <a
        href="https://www.instagram.com/swahilipothub/"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Swahilipot Hub on Instagram"
        title="Instagram"
    >
        <i class="fa-brands fa-instagram"></i>
    </a>


    <!-- X -->
    <a
        href="https://x.com/swahilipothub"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Swahilipot Hub on X"
        title="X"
    >
        <i class="fa-brands fa-x-twitter"></i>
    </a>


    <!-- WhatsApp -->
    <a
        href="https://wa.me/254760001111"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Contact Swahilipot Hub on WhatsApp"
        title="WhatsApp"
    >
        <i class="fa-brands fa-whatsapp"></i>
    </a>

</div>


            </div>


        </div>


        <div class="sp-footer-bar">


            <p>

                &copy; <?= date('Y') ?>

                Swahilipot. All rights reserved.

            </p>


        </div>


    </footer>


    <button
        type="button"
        id="backToTop"
        class="sp-back-to-top"
        aria-label="Back to top"
    >


        <i class="fa-solid fa-arrow-up"></i>


    </button>


    <script
        src="<?= e(asset('assets/js/app.js')) ?>"
    ></script>


</body>

</html>
