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
                Crochet items sold by Loops By Bex are individually handmade in the United Kingdom; slight stitch or tension variations are part of the unique character of handmade crochet work. Any items created by someone else for Loops By Bex to use and sell (such as fabric dog bandanas) are clearly noted on their product pages.
            </p>

            <h2>2. Pricing, Delivery &amp; Payment</h2>
            <p>
                All prices are displayed in Pounds Sterling (&pound; GBP). Delivery is free within 5 miles of Sheffield S6 and &pound;2.50 further across the UK. Online card payments are processed securely through Stripe Hosted Checkout; card details are never stored on Loops By Bex servers.
            </p>

            <h2>3. Product Safety</h2>
            <p>
                Small parts (such as safety eyes) are used in handmade creations — please bear this in mind when gifting to others, as items are not recommended for children under 4 years old.
            </p>

            <h2>4. Contact</h2>
            <p>
                For any questions regarding these terms, please contact <a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a>.
            </p>
        </article>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
