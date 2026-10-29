<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Terms & Conditions';
$activeNav = '';
$contactEmail = env_val('CONTACT_EMAIL', 'hello@loopsbybex.co.uk');

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <article class="prose-card">
            <h1>Terms &amp; Conditions</h1>
            <p style="font-size: 0.88rem; color: var(--text-muted);">Last updated: October 2026</p>

            <h2>1. Handmade Products</h2>
            <p>
                Every item sold by Loops By Bex is individually handmade in the United Kingdom. Slight stitch or tension variations are part of the unique character of handmade crochet work.
            </p>

            <h2>2. Pricing &amp; Payment</h2>
            <p>
                All prices are displayed in Pounds Sterling (&pound; GBP). Online card payments are processed securely through Stripe Hosted Checkout; card details are never stored on Loops By Bex servers.
            </p>

            <h2>3. Product Safety</h2>
            <p>
                Unless explicitly certified and labelled as suitable for infants, decorative crochet plushies and keepsakes may contain small components (such as safety eyes) and should be kept out of reach of children under 36 months.
            </p>

            <h2>4. Contact</h2>
            <p>
                For any questions regarding these terms, please contact <a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a>.
            </p>
        </article>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
