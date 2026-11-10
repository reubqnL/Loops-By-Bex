<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Shipping & Returns';
$activeNav = '';
$contactEmail = env_val('CONTACT_EMAIL', 'hello@loopsbybex.co.uk');

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <article class="prose-card">
            <h1>Shipping &amp; Returns Policy</h1>
            <p style="font-size: 0.88rem; color: var(--text-muted);">Last updated: October 2026</p>

            <h2>1. UK Dispatch &amp; Delivery</h2>
            <p>
                Delivery is <strong>free within 5 miles of Sheffield S6</strong>, and <strong>&pound;2.50</strong> across the rest of the United Kingdom. Ready-made items are carefully packaged and dispatched within 2–4 working days.
            </p>

            <h2>2. 14-Day Right to Cancel</h2>
            <p>
                Under the UK Consumer Contracts Regulations 2013, you have 14 calendar days from the day after receiving an item to notify us that you wish to cancel and return it in unused, original condition for a full refund. Your statutory rights under the Consumer Rights Act 2015 remain unaffected.
            </p>

            <h2>3. Starting a Return</h2>
            <p>
                Email <a href="mailto:<?php echo e($contactEmail); ?>?subject=Order%20Return"><?php echo e($contactEmail); ?></a> with your order reference before posting any return parcel.
            </p>
        </article>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
