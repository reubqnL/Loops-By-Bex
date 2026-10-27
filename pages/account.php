<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Order Lookup';
$activeNav = 'account';
$pageStyles = ['/style/cart.css'];

$orderNumber = strtoupper(trim((string) ($_POST['order_number'] ?? '')));
$email = trim((string) ($_POST['email'] ?? ''));
$foundOrder = null;
$lookupError = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf_or_redirect('/account');

    if (is_honeypot_triggered()) {
        $lookupError = 'No matching order was found for those details.';
    } elseif (!check_rate_limit($pdo, 'order_lookup', 8, 600)) {
        $lookupError = 'Too many lookup attempts. Please wait a few minutes and try again.';
    } elseif ($orderNumber === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $lookupError = 'Please enter both your order reference (e.g. LBB-1A2B3C4D) and email address.';
    } else {
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT order_number, customer_name, shipping_city, shipping_postcode, total_pence, payment_status, created_at
                     FROM orders
                     WHERE order_number = :order_number AND LOWER(customer_email) = LOWER(:email)
                     LIMIT 1'
                );
                $stmt->execute([
                    'order_number' => $orderNumber,
                    'email' => $email,
                ]);
                $row = $stmt->fetch();
                if (is_array($row)) {
                    $foundOrder = $row;
                }
            } catch (PDOException $e) {
                error_log('[LoopsByBex Account] Order lookup failed: ' . $e->getMessage());
            }
        }

        if ($foundOrder === null && isset($_SESSION['last_order']) && is_array($_SESSION['last_order'])) {
            $recent = $_SESSION['last_order'];
            if (
                strcasecmp((string) ($recent['order_number'] ?? ''), $orderNumber) === 0
                && strcasecmp((string) ($recent['customer_email'] ?? ''), $email) === 0
            ) {
                $foundOrder = [
                    'order_number' => $recent['order_number'],
                    'customer_name' => $recent['customer_name'],
                    'shipping_city' => $recent['shipping_city'],
                    'shipping_postcode' => $recent['shipping_postcode'],
                    'total_pence' => (int) $recent['total_pence'],
                    'payment_status' => 'paid',
                    'created_at' => date('Y-m-d H:i:s', (int) ($recent['created_at'] ?? time())),
                ];
            }
        }

        if ($foundOrder === null) {
            $lookupError = 'No matching order was found for those details.';
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container" style="max-width: 680px;">
        <section class="panel-card">
            <h1 style="font-size: 1.65rem; margin-bottom: 0.35rem;">Track Your Order</h1>
            <p style="color: var(--text-secondary); margin-bottom: 1.25rem;">
                Check the status of an order using the reference from your confirmation screen and your email address.
            </p>

            <?php if ($lookupError !== ''): ?>
                <div class="flash-banner flash-error" role="alert" style="margin-bottom: 1rem;">
                    <?php echo e($lookupError); ?>
                </div>
            <?php endif; ?>

            <form action="/account" method="POST" novalidate aria-label="Order status lookup">
                <?php echo csrf_field(); ?>
                <?php echo honeypot_field(); ?>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="lookup_order_number">Order Reference *</label>
                        <input type="text" id="lookup_order_number" name="order_number" placeholder="LBB-XXXXXXXX" required maxlength="32" value="<?php echo e($orderNumber); ?>">
                    </div>
                    <div class="form-field">
                        <label for="lookup_email">Email Address *</label>
                        <input type="email" id="lookup_email" name="email" required maxlength="254" autocomplete="email" value="<?php echo e($email); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Find Order &rarr;</button>
            </form>

            <?php if ($foundOrder !== null): ?>
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-subtle);">
                    <h2>Order <?php echo e($foundOrder['order_number']); ?></h2>
                    <div class="summary-row">
                        <span>Customer</span>
                        <span><?php echo e($foundOrder['customer_name']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Destination</span>
                        <span><?php echo e($foundOrder['shipping_city'] . ', ' . $foundOrder['shipping_postcode']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Payment Status</span>
                        <span style="text-transform: capitalize; font-weight: 600;"><?php echo e($foundOrder['payment_status']); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Order Total</span>
                        <span><?php echo e(format_gbp((int) $foundOrder['total_pence'])); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
