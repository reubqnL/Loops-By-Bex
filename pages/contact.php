<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Contact Bex';
$activeNav = 'contact';
$contactEmail = trim(env_val('CONTACT_EMAIL', ''));
$emailIsValid = filter_var($contactEmail, FILTER_VALIDATE_EMAIL) !== false;

if (!in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container" style="max-width: 680px;">
        <section class="panel-card">
            <h1>Email Bex</h1>
            <p style="color: var(--text-secondary); margin-bottom: 1.25rem;">
                For questions about an item or an existing order, email Bex directly.
            </p>
            <?php if ($emailIsValid): ?>
                <p class="contact-email-display">
                    <a href="mailto:<?php echo e($contactEmail); ?>" class="btn btn-primary">Email <?php echo e($contactEmail); ?> &rarr;</a>
                </p>
                <p class="contact-email-copy">Or copy this address: <strong><?php echo e($contactEmail); ?></strong></p>
            <?php else: ?>
                <p role="status">Email contact is not available right now. Please try again later.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
