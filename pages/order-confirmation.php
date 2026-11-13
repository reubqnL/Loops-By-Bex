<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/payments.php';
enforce_site_mode('storefront');

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');

$order = null;
$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$pendingStripeOrder = $_SESSION['pending_stripe_order'] ?? null;
$pendingSessionId = is_array($pendingStripeOrder)
    ? (string) ($pendingStripeOrder['stripe_session_id'] ?? '')
    : '';
$pendingOrderNumber = is_array($pendingStripeOrder)
    ? (string) ($pendingStripeOrder['order_number'] ?? '')
    : '';
$hasMatchingPendingSession = $sessionId !== ''
    && $pendingSessionId !== ''
    && hash_equals($pendingSessionId, $sessionId);

// Stripe session IDs are bearer references. Bind confirmation to the browser session
// that initiated checkout before making an API lookup or displaying order details.
if ($sessionId !== '' && !$hasMatchingPendingSession) {
    header('Location: /shop', true, 302);
    exit();
}

if ($sessionId !== '') {
    $stripeSecret = env_val('STRIPE_SECRET_KEY', '');
    $stripeSession = retrieve_stripe_checkout_session($stripeSecret, $sessionId);

    if ($stripeSession === null || ($stripeSession['payment_status'] ?? '') !== 'paid') {
        header('Location: /checkout?cancelled=1', true, 302);
        exit();
    }

    $orderNumber = trim((string) ($stripeSession['client_reference_id'] ?? ($stripeSession['metadata']['order_number'] ?? '')));
    if (
        $orderNumber === ''
        || $pendingOrderNumber === ''
        || !hash_equals($pendingOrderNumber, $orderNumber)
    ) {
        header('Location: /shop', true, 302);
        exit();
    }

    $amountTotal = isset($stripeSession['amount_total']) ? (int) $stripeSession['amount_total'] : -1;
    $currency = strtoupper((string) ($stripeSession['currency'] ?? ''));

    if ($pdo instanceof PDO && $orderNumber !== '' && $currency === 'GBP' && $amountTotal >= 0) {
        try {
            mark_order_paid_and_mark_products_sold($pdo, $orderNumber, $sessionId, $amountTotal);

            $orderStmt = $pdo->prepare(
                'SELECT id, order_number, customer_name, customer_email, shipping_city, shipping_postcode, total_pence, payment_status
                 FROM orders
                 WHERE order_number = :order_number AND payment_reference = :session_id
                 LIMIT 1'
            );
            $orderStmt->execute([
                'order_number' => $orderNumber,
                'session_id' => $sessionId,
            ]);
            $orderRow = $orderStmt->fetch();

            if (is_array($orderRow)) {
                $itemsStmt = $pdo->prepare(
                    'SELECT product_slug, product_name, unit_price_pence, quantity, line_total_pence
                     FROM order_items
                     WHERE order_id = :order_id
                     ORDER BY id ASC'
                );
                $itemsStmt->execute(['order_id' => (int) $orderRow['id']]);
                $itemRows = $itemsStmt->fetchAll() ?: [];

                $formattedItems = [];
                foreach ($itemRows as $ir) {
                    $formattedItems[] = [
                        'product' => [
                            'slug' => (string) $ir['product_slug'],
                            'name' => (string) $ir['product_name'],
                        ],
                        'quantity' => (int) $ir['quantity'],
                        'unit_price_pence' => (int) $ir['unit_price_pence'],
                        'line_total_pence' => (int) $ir['line_total_pence'],
                    ];
                }

                $order = [
                    'order_number' => (string) $orderRow['order_number'],
                    'customer_name' => (string) $orderRow['customer_name'],
                    'customer_email' => (string) $orderRow['customer_email'],
                    'shipping_city' => (string) $orderRow['shipping_city'],
                    'shipping_postcode' => (string) $orderRow['shipping_postcode'],
                    'items' => $formattedItems,
                    'total_pence' => (int) $orderRow['total_pence'],
                    'payment_status' => (string) $orderRow['payment_status'],
                    'saved_in_db' => true,
                    'created_at' => time(),
                ];
            }
        } catch (PDOException $e) {
            error_log('[LoopsByBex Confirmation] Order lookup failed: ' . $e->getMessage());
        }
    }

    if ($order === null && isset($_SESSION['pending_stripe_order']) && is_array($_SESSION['pending_stripe_order'])) {
        $pending = $_SESSION['pending_stripe_order'];
        if (($pending['stripe_session_id'] ?? '') === $sessionId) {
            $order = $pending;
            $order['payment_status'] = (string) ($order['payment_status'] ?? 'stock_review');
            $order['created_at'] = time();
        }
    }

    if ($order !== null) {
        regenerate_session_safely();
        $_SESSION['last_order'] = $order;
        unset($_SESSION['pending_stripe_order']);
        cart_clear();
        header('Location: /order-confirmation', true, 303);
        exit();
    }
} else {
    $recent = $_SESSION['last_order'] ?? null;
    if (is_array($recent) && (time() - (int) ($recent['created_at'] ?? 0)) <= 1800) {
        $order = $recent;
    } else {
        unset($_SESSION['last_order']);
    }
}

if ($order === null) {
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
            <?php if (($order['payment_status'] ?? '') === 'refund_pending'): ?>
                <p>Your payment was received, but the order could not be completed because a one-off item may have overlapped with another checkout or the payment details need review. A full refund will be processed manually in Stripe. No action is needed from you.</p>
            <?php elseif (($order['payment_status'] ?? '') === 'stock_review'): ?>
                <p>Your payment was received and your order is being checked against one-off stock and payment details. If it cannot be fulfilled, it will be refunded in full.</p>
            <?php else: ?>
                <p>
                    Your <?php echo e(format_gbp((int) $order['total_pence'])); ?> order has been recorded for
                    <strong><?php echo e($order['customer_email']); ?></strong>.
                </p>
            <?php endif; ?>

            <div style="text-align: left; margin: 1.5rem 0; padding: 1.25rem; border-radius: 14px; background: #faf8fb; border: 1px solid var(--border-subtle);">
                <h2 style="margin-top: 0;">Order Summary</h2>
                <?php foreach ($order['items'] as $line): ?>
                    <div class="summary-row">
                        <span><?php echo e($line['product']['name']); ?> &times; <?php echo (int) $line['quantity']; ?></span>
                        <span><?php echo e(format_gbp((int) $line['line_total_pence'])); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row summary-total">
                    <span><?php
                        if (($order['payment_status'] ?? '') === 'refund_pending') {
                            echo 'Amount paid (refund pending)';
                        } elseif (($order['payment_status'] ?? '') === 'stock_review') {
                            echo 'Amount paid (stock review)';
                        } else {
                            echo 'Total paid';
                        }
                    ?></span>
                    <span><?php echo e(format_gbp((int) $order['total_pence'])); ?></span>
                </div>
            </div>

            <a href="/shop" class="btn btn-primary" style="text-decoration: none; color: #ffffff;">Continue Browsing &rarr;</a>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
