<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$summary = get_cart_summary($pdo);
if (empty($summary['items'])) {
    set_flash('info', 'Please add an item to your cart before checking out.');
    header('Location: /shop', true, 302);
    exit();
}

if (!isset($_SESSION['checkout_idempotency_key'])) {
    $_SESSION['checkout_idempotency_key'] = bin2hex(random_bytes(32));
}
$idempotencyKey = (string) $_SESSION['checkout_idempotency_key'];

$errors = [];
$values = [
    'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
    'customer_email' => trim((string) ($_POST['customer_email'] ?? '')),
    'shipping_line1' => trim((string) ($_POST['shipping_line1'] ?? '')),
    'shipping_line2' => trim((string) ($_POST['shipping_line2'] ?? '')),
    'shipping_city' => trim((string) ($_POST['shipping_city'] ?? '')),
    'shipping_postcode' => strtoupper(trim((string) ($_POST['shipping_postcode'] ?? ''))),
    'shipping_country' => 'GB',
    'order_notes' => trim((string) ($_POST['order_notes'] ?? '')),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($values['customer_name'] === '' || mb_strlen($values['customer_name']) > 140) {
        $errors['customer_name'] = 'Please enter your full name.';
    }
    if ($values['customer_email'] === '' || strlen($values['customer_email']) > 254 || !filter_var($values['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['customer_email'] = 'Please enter a valid email address.';
    }
    if ($values['shipping_line1'] === '' || mb_strlen($values['shipping_line1']) > 180) {
        $errors['shipping_line1'] = 'Please enter the first line of your delivery address.';
    }
    if ($values['shipping_city'] === '' || mb_strlen($values['shipping_city']) > 100) {
        $errors['shipping_city'] = 'Please enter your town or city.';
    }
    if ($values['shipping_postcode'] === '' || !preg_match('/^[A-Z0-9 ]{5,10}$/', $values['shipping_postcode'])) {
        $errors['shipping_postcode'] = 'Please enter a valid UK postcode.';
    }
    if (mb_strlen($values['order_notes']) > 500) {
        $errors['order_notes'] = 'Order notes must be 500 characters or fewer.';
    }

    $submittedIdempotency = trim((string) ($_POST['idempotency_key'] ?? ''));
    if ($submittedIdempotency === '' || !hash_equals($idempotencyKey, $submittedIdempotency)) {
        $errors['general'] = 'This checkout session expired or was already submitted. Please review your details and try again.';
    }

    // Re-compute authoritative totals server-side immediately prior to creating the order
    $summary = get_cart_summary($pdo);
    $totalPence = (int) $summary['total_pence'];
    $stripeSecret = env_val('STRIPE_SECRET_KEY', '');

    if ($totalPence > 0 && $stripeSecret === '') {
        $errors['general'] = 'Live card payments are not yet enabled. Checkout cannot charge orders above £0.00 until Stripe Hosted Checkout is configured.';
    }

    if (empty($errors)) {
        $orderNumber = 'LBB-' . strtoupper(bin2hex(random_bytes(4)));
        $paymentProvider = $totalPence === 0 ? 'placeholder_free' : 'stripe_hosted';
        $paymentStatus = $totalPence === 0 ? 'paid' : 'pending';
        $savedInDb = false;

        if ($pdo instanceof PDO) {
            try {
                $pdo->beginTransaction();

                $orderStmt = $pdo->prepare(
                    'INSERT INTO orders (
                        order_number, idempotency_key, customer_name, customer_email,
                        shipping_line1, shipping_line2, shipping_city, shipping_postcode, shipping_country,
                        order_notes, subtotal_pence, shipping_pence, total_pence, currency,
                        payment_provider, payment_status
                    ) VALUES (
                        :order_number, :idempotency_key, :customer_name, :customer_email,
                        :shipping_line1, :shipping_line2, :shipping_city, :shipping_postcode, :shipping_country,
                        :order_notes, :subtotal_pence, :shipping_pence, :total_pence, :currency,
                        :payment_provider, :payment_status
                    )'
                );
                $orderStmt->execute([
                    'order_number' => $orderNumber,
                    'idempotency_key' => hash('sha256', $idempotencyKey),
                    'customer_name' => $values['customer_name'],
                    'customer_email' => $values['customer_email'],
                    'shipping_line1' => $values['shipping_line1'],
                    'shipping_line2' => $values['shipping_line2'],
                    'shipping_city' => $values['shipping_city'],
                    'shipping_postcode' => $values['shipping_postcode'],
                    'shipping_country' => $values['shipping_country'],
                    'order_notes' => $values['order_notes'],
                    'subtotal_pence' => (int) $summary['subtotal_pence'],
                    'shipping_pence' => (int) $summary['shipping_pence'],
                    'total_pence' => $totalPence,
                    'currency' => 'GBP',
                    'payment_provider' => $paymentProvider,
                    'payment_status' => $paymentStatus,
                ]);

                $orderId = (int) $pdo->lastInsertId();
                $itemStmt = $pdo->prepare(
                    'INSERT INTO order_items (
                        order_id, product_slug, product_name, unit_price_pence, quantity, line_total_pence
                    ) VALUES (
                        :order_id, :product_slug, :product_name, :unit_price_pence, :quantity, :line_total_pence
                    )'
                );

                foreach ($summary['items'] as $line) {
                    $itemStmt->execute([
                        'order_id' => $orderId,
                        'product_slug' => $line['product']['slug'],
                        'product_name' => $line['product']['name'],
                        'unit_price_pence' => (int) $line['unit_price_pence'],
                        'quantity' => (int) $line['quantity'],
                        'line_total_pence' => (int) $line['line_total_pence'],
                    ]);
                }

                $pdo->commit();
                $savedInDb = true;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('[LoopsByBex Checkout] Failed to store order: ' . $e->getMessage());
                if ($totalPence > 0) {
                    $errors['general'] = 'We could not process your order right now. Your card has not been charged.';
                }
            }
        } elseif ($totalPence > 0) {
            $errors['general'] = 'Order database is currently unavailable. Your card has not been charged.';
        }

        if (empty($errors)) {
            $_SESSION['last_order'] = [
                'order_number' => $orderNumber,
                'customer_name' => $values['customer_name'],
                'customer_email' => $values['customer_email'],
                'shipping_city' => $values['shipping_city'],
                'shipping_postcode' => $values['shipping_postcode'],
                'items' => $summary['items'],
                'total_pence' => $totalPence,
                'saved_in_db' => $savedInDb,
                'created_at' => time(),
            ];
            unset($_SESSION['checkout_idempotency_key']);
            cart_clear();

            header('Location: /order-confirmation', true, 303);
            exit();
        }
    }
}

$pageTitle = 'Checkout';
$activeNav = 'cart';

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <div class="page-header-band">
            <div>
                <h1>Checkout</h1>
                <p>Complete your delivery details below.</p>
            </div>
            <a href="/cart" class="btn btn-outline-pink btn-sm">&larr; Back to Cart</a>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="flash-banner flash-error" style="margin-bottom: 1.25rem;">
                <?php echo e($errors['general']); ?>
            </div>
        <?php endif; ?>

        <div class="two-col-panel">
            <form action="/checkout" method="POST" class="panel-card" novalidate>
                <input type="hidden" name="idempotency_key" value="<?php echo e($idempotencyKey); ?>">

                <h2>Delivery Information</h2>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required maxlength="140" autocomplete="name" value="<?php echo e($values['customer_name']); ?>">
                        <?php if (isset($errors['customer_name'])): ?>
                            <small style="color: #8f1d35;"><?php echo e($errors['customer_name']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required maxlength="254" autocomplete="email" value="<?php echo e($values['customer_email']); ?>">
                        <?php if (isset($errors['customer_email'])): ?>
                            <small style="color: #8f1d35;"><?php echo e($errors['customer_email']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field field-full">
                        <label for="shipping_line1">Address Line 1 *</label>
                        <input type="text" id="shipping_line1" name="shipping_line1" required maxlength="180" autocomplete="address-line1" value="<?php echo e($values['shipping_line1']); ?>">
                        <?php if (isset($errors['shipping_line1'])): ?>
                            <small style="color: #8f1d35;"><?php echo e($errors['shipping_line1']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field field-full">
                        <label for="shipping_line2">Address Line 2 (Optional)</label>
                        <input type="text" id="shipping_line2" name="shipping_line2" maxlength="180" autocomplete="address-line2" value="<?php echo e($values['shipping_line2']); ?>">
                    </div>

                    <div class="form-field">
                        <label for="shipping_city">Town / City *</label>
                        <input type="text" id="shipping_city" name="shipping_city" required maxlength="100" autocomplete="address-level2" value="<?php echo e($values['shipping_city']); ?>">
                        <?php if (isset($errors['shipping_city'])): ?>
                            <small style="color: #8f1d35;"><?php echo e($errors['shipping_city']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="shipping_postcode">Postcode (UK) *</label>
                        <input type="text" id="shipping_postcode" name="shipping_postcode" required maxlength="10" autocomplete="postal-code" value="<?php echo e($values['shipping_postcode']); ?>">
                        <?php if (isset($errors['shipping_postcode'])): ?>
                            <small style="color: #8f1d35;"><?php echo e($errors['shipping_postcode']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field field-full">
                        <label for="order_notes">Gift Message or Order Notes (Optional)</label>
                        <textarea id="order_notes" name="order_notes" maxlength="500"><?php echo e($values['order_notes']); ?></textarea>
                    </div>
                </div>

                <h2 style="margin-top: 1.75rem;">Payment</h2>
                <div class="flash-banner flash-info" style="margin-bottom: 1.25rem;">
                    <?php if ((int) $summary['total_pence'] === 0): ?>
                        <strong>Pre-Launch <?php echo e(format_gbp(0)); ?> Mode:</strong> All placeholder products are currently priced at <?php echo e(format_gbp(0)); ?>. No payment card details are requested or stored on this server.
                    <?php else: ?>
                        <strong>Secure Hosted Payment:</strong> Card payments are processed exclusively through Stripe Hosted Checkout (PCI-DSS SAQ-A) — card numbers never touch this server.
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Complete Order (<?php echo e(format_gbp((int) $summary['total_pence'])); ?>)
                </button>
            </form>

            <aside class="panel-card">
                <h2>Order Summary</h2>
                <?php foreach ($summary['items'] as $line): ?>
                    <div class="summary-row">
                        <span><?php echo e($line['product']['name']); ?> &times; <?php echo (int) $line['quantity']; ?></span>
                        <span><?php echo e(format_gbp((int) $line['line_total_pence'])); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row">
                    <span>UK Delivery</span>
                    <span><?php echo e(format_gbp((int) $summary['shipping_pence'])); ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total to Pay</span>
                    <span><?php echo e(format_gbp((int) $summary['total_pence'])); ?></span>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
