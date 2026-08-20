document.addEventListener('DOMContentLoaded', function () {
    var messageField = document.getElementById('message');
    var charCountEl = document.getElementById('charCount');
    var categorySelect = document.getElementById('category');
    var categoryTiles = document.querySelectorAll('.sp-category-tile');
    var anonymousCheckbox = document.getElementById('anonymous');
    var nameField = document.getElementById('full_name');
    var emailField = document.getElementById('email');

    if (messageField && charCountEl) {

    function updateCount() {

        var length = messageField.value.length;

        charCountEl.textContent = length;

        // Remove previous states
        charCountEl.classList.remove(
            'count-warning',
            'count-danger'
        );

        // Add warning state
        if (length >= 350 && length < 450) {
            charCountEl.classList.add('count-warning');
        }

        // Add danger state
        if (length >= 450) {
            charCountEl.classList.add('count-danger');
        }
    }

    messageField.addEventListener('input', updateCount);

    // Update immediately on page load
    updateCount();
}

    categoryTiles.forEach(function (tile) {
    tile.addEventListener('click', function () {
        var cat = tile.getAttribute('data-category');

        if (categorySelect) {
            categorySelect.value = cat;
        }

        categoryTiles.forEach(function (t) {
            t.classList.remove('active');
        });

        tile.classList.add('active');

        // Click animation
        tile.classList.add('clicked');

        setTimeout(function () {
            tile.classList.remove('clicked');
        }, 250);
    });
});

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            categoryTiles.forEach(function (tile) {
                tile.classList.toggle('active', tile.getAttribute('data-category') === categorySelect.value);
            });
        });
    }

    function toggleAnonymous() {
        if (!anonymousCheckbox) return;
        var isAnon = anonymousCheckbox.checked;
        if (nameField) {
            nameField.disabled = isAnon;
            nameField.style.opacity = isAnon ? '0.5' : '1';
            if (isAnon) nameField.value = '';
        }
        if (emailField) {
            emailField.disabled = isAnon;
            emailField.style.opacity = isAnon ? '0.5' : '1';
            if (isAnon) emailField.value = '';
        }
    }

    if (anonymousCheckbox) {
        anonymousCheckbox.addEventListener('change', toggleAnonymous);
        toggleAnonymous();
    }

    var successAlert = document.querySelector('.sp-alert-success');
    if (successAlert) {
        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    // =================================
    // SUBMIT BUTTON LOADING STATE
    // =================================

    var feedbackForm = document.getElementById('feedbackForm');
    var submitButton = document.querySelector('.sp-submit-btn');

    if (feedbackForm && submitButton) {

        feedbackForm.addEventListener('submit', function () {

            submitButton.disabled = true;

            submitButton.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> SUBMITTING...';

            submitButton.classList.add('is-submitting');

        });

    }
   // =================================
    // SMOOTH SCROLL TO FEEDBACK FORM
    // =================================

    var feedbackLink = document.querySelector('.sp-hero-btn');
    var feedbackForm = document.getElementById('feedbackForm');

    if (feedbackLink && feedbackForm) {
        feedbackLink.addEventListener('click', function (event) {
            event.preventDefault();

            feedbackForm.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    } 
    var feedbackCard = document.querySelector('.sp-form-card');

if (feedbackLink && feedbackForm && feedbackCard) {
    feedbackLink.addEventListener('click', function (event) {
        event.preventDefault();

        feedbackForm.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        feedbackCard.classList.remove('form-highlight');

        setTimeout(function () {
            feedbackCard.classList.add('form-highlight');
        }, 500);
    });
}
    // =================================
    // BACK TO TOP BUTTON
    // =================================

    var backToTop = document.getElementById('backToTop');

    if (backToTop) {

        window.addEventListener('scroll', function () {

            if (window.scrollY > 400) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }

        });

        backToTop.addEventListener('click', function () {

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        });

    }
    // =================================
    // SCROLL PROGRESS BAR
    // =================================

    var scrollProgress = document.getElementById('scrollProgress');

    if (scrollProgress) {

        function updateScrollProgress() {

            var scrollTop = window.scrollY;

            var documentHeight =
                document.documentElement.scrollHeight -
                document.documentElement.clientHeight;

            var scrollPercentage =
                (scrollTop / documentHeight) * 100;

            scrollProgress.style.width = scrollPercentage + '%';
        }

        window.addEventListener('scroll', updateScrollProgress);

        updateScrollProgress();
    }
    // =================================
// SUBMIT ANOTHER FEEDBACK
// =================================

var newFeedbackBtn = document.getElementById('newFeedbackBtn');
var formCard = document.querySelector('.sp-form-card');

if (newFeedbackBtn && formCard) {

    newFeedbackBtn.addEventListener('click', function () {

        formCard.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        setTimeout(function () {

            var firstField = document.getElementById('full_name');

            if (firstField) {
                firstField.focus();
            }

        }, 700);

    });

}
});
