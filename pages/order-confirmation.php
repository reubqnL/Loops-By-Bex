<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$order = $_SESSION['last_order'] ?? null;
if (!is_array($order) || (time() - (int) ($order['created_at'] ?? 0)) > 1800) {
    unset($_SESSION['last_order']);
    header('Location: /shop', true, 302);
    exit();
}

$pageTitle = 'Order Confirmation';
$activeNav = 'shop';
$pageStyles = ['/style/cart.css'];

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <section class="prose-card" style="text-align: center;">
            <span class="badge-category" style="display: inline-block; padding: 0.35rem 1rem; border-radius: 999px; background: var(--bg-soft-pink); color: var(--brand-pink-soft-text); font-weight: 600; margin-bottom: 0.75rem;">
                Order Reference: <?php echo e($order['order_number']); ?>
            </span>
            <h1>Thank you, <?php echo e($order['customer_name']); ?>!</h1>
            <p>
                Your <?php echo e(format_gbp((int) $order['total_pence'])); ?> placeholder order has been recorded for
                <strong><?php echo e($order['customer_email']); ?></strong>.
            </p>

            <div style="text-align: left; margin: 1.5rem 0; padding: 1.25rem; border-radius: 14px; background: #faf8fb; border: 1px solid var(--border-subtle);">
                <h2 style="margin-top: 0;">Order Summary</h2>
                <?php foreach ($order['items'] as $line): ?>
                    <div class="summary-row">
                        <span><?php echo e($line['product']['name']); ?> &times; <?php echo (int) $line['quantity']; ?></span>
                        <span><?php echo e(format_gbp((int) $line['line_total_pence'])); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row summary-total">
                    <span>Total Paid</span>
                    <span><?php echo e(format_gbp((int) $order['total_pence'])); ?></span>
                </div>
            </div>

            <a href="/shop" class="btn btn-primary" style="text-decoration: none; color: #ffffff;">Continue Browsing &rarr;</a>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
