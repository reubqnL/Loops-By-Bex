<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/payments.php';
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

$summary = get_cart_summary($pdo, $values['shipping_postcode']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    assert_no_raw_card_data_posted();
    verify_csrf_or_redirect('/checkout');

    if (is_honeypot_triggered()) {
        header('Location: /shop', true, 303);
        exit();
    }

    if (!check_rate_limit($pdo, 'checkout_submit', 8, 600)) {
        $errors['general'] = 'Too many checkout attempts. Please wait a few minutes before trying again.';
    }

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
    $compactPostcode = (string) preg_replace('/\s+/', '', $values['shipping_postcode']);
    if ($compactPostcode === '' || !preg_match('/^([A-Z]{1,2}[0-9][0-9A-Z]?)([0-9][A-Z]{2})$/', $compactPostcode, $pcParts)) {
        $errors['shipping_postcode'] = 'Please enter a valid UK postcode (e.g. S6 4AB).';
    } else {
        $values['shipping_postcode'] = $pcParts[1] . ' ' . $pcParts[2];
    }
    if (mb_strlen($values['order_notes']) > 500) {
        $errors['order_notes'] = 'Order notes must be 500 characters or fewer.';
    }

    $submittedIdempotency = trim((string) ($_POST['idempotency_key'] ?? ''));
    if ($submittedIdempotency === '' || !hash_equals($idempotencyKey, $submittedIdempotency)) {
        $errors['general'] = 'This checkout session expired or was already submitted. Please review your details and try again.';
    }

    // recalculate total on server before saving order
    $summary = get_cart_summary($pdo, $values['shipping_postcode']);
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

        if (empty($errors) && $totalPence > 0) {
            $stripeSession = create_stripe_checkout_session(
                $stripeSecret,
                $orderNumber,
                $values['customer_email'],
                $summary,
                $idempotencyKey
            );
            if ($stripeSession === null) {
                if ($pdo instanceof PDO) {
                    try {
                        $failStmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_number = :num");
                        $failStmt->execute(['num' => $orderNumber]);
                    } catch (PDOException $e) {
                        error_log('[LoopsByBex Checkout] Failed marking order failed: ' . $e->getMessage());
                    }
                }
                $_SESSION['checkout_idempotency_key'] = bin2hex(random_bytes(32));
                $idempotencyKey = (string) $_SESSION['checkout_idempotency_key'];
                $errors['general'] = 'Unable to connect to our secure payment gateway. Your card has not been charged.';
            } else {
                if ($pdo instanceof PDO) {
                    try {
                        $refStmt = $pdo->prepare('UPDATE orders SET payment_reference = :ref WHERE order_number = :num');
                        $refStmt->execute(['ref' => $stripeSession['id'], 'num' => $orderNumber]);
                    } catch (PDOException $e) {
                        error_log('[LoopsByBex Checkout] Failed saving Stripe session reference: ' . $e->getMessage());
                    }
                }
                $_SESSION['pending_stripe_order'] = [
                    'stripe_session_id' => $stripeSession['id'],
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
                header('Location: ' . $stripeSession['url'], true, 303);
                exit();
            }
        }

        if (empty($errors)) {
            regenerate_session_safely();
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
$pageStyles = ['/style/cart.css'];

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
            <div class="flash-banner flash-error" role="alert" style="margin-bottom: 1.25rem;">
                <?php echo e($errors['general']); ?>
            </div>
        <?php elseif (isset($_GET['cancelled'])): ?>
            <div class="flash-banner flash-info" role="status" style="margin-bottom: 1.25rem;">
                Checkout was cancelled — your items are still in your cart whenever you're ready.
            </div>
        <?php endif; ?>

        <div class="two-col-panel">
            <form action="/checkout" method="POST" class="panel-card" novalidate aria-label="Checkout delivery details">
                <?php echo csrf_field(); ?>
                <?php echo honeypot_field(); ?>
                <input type="hidden" name="idempotency_key" value="<?php echo e($idempotencyKey); ?>">

                <h2>Delivery Information</h2>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="customer_name">Full Name <span aria-hidden="true">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required maxlength="140" autocomplete="name"
                               value="<?php echo e($values['customer_name']); ?>"
                               <?php echo isset($errors['customer_name']) ? 'aria-invalid="true" aria-describedby="err-customer-name"' : ''; ?>>
                        <?php if (isset($errors['customer_name'])): ?>
                            <small id="err-customer-name" role="alert" style="color: #8f1d35;"><?php echo e($errors['customer_name']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="customer_email">Email Address <span aria-hidden="true">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required maxlength="254" autocomplete="email"
                               value="<?php echo e($values['customer_email']); ?>"
                               <?php echo isset($errors['customer_email']) ? 'aria-invalid="true" aria-describedby="err-customer-email"' : ''; ?>>
                        <?php if (isset($errors['customer_email'])): ?>
                            <small id="err-customer-email" role="alert" style="color: #8f1d35;"><?php echo e($errors['customer_email']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field field-full">
                        <label for="shipping_line1">Address Line 1 <span aria-hidden="true">*</span></label>
                        <input type="text" id="shipping_line1" name="shipping_line1" required maxlength="180" autocomplete="address-line1"
                               value="<?php echo e($values['shipping_line1']); ?>"
                               <?php echo isset($errors['shipping_line1']) ? 'aria-invalid="true" aria-describedby="err-shipping-line1"' : ''; ?>>
                        <?php if (isset($errors['shipping_line1'])): ?>
                            <small id="err-shipping-line1" role="alert" style="color: #8f1d35;"><?php echo e($errors['shipping_line1']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field field-full">
                        <label for="shipping_line2">Address Line 2 (Optional)</label>
                        <input type="text" id="shipping_line2" name="shipping_line2" maxlength="180" autocomplete="address-line2" value="<?php echo e($values['shipping_line2']); ?>">
                    </div>

                    <div class="form-field">
                        <label for="shipping_city">Town / City <span aria-hidden="true">*</span></label>
                        <input type="text" id="shipping_city" name="shipping_city" required maxlength="100" autocomplete="address-level2"
                               value="<?php echo e($values['shipping_city']); ?>"
                               <?php echo isset($errors['shipping_city']) ? 'aria-invalid="true" aria-describedby="err-shipping-city"' : ''; ?>>
                        <?php if (isset($errors['shipping_city'])): ?>
                            <small id="err-shipping-city" role="alert" style="color: #8f1d35;"><?php echo e($errors['shipping_city']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-field">
                        <label for="shipping_postcode">Postcode (UK) <span aria-hidden="true">*</span></label>
                        <input type="text" id="shipping_postcode" name="shipping_postcode" required maxlength="10" autocomplete="postal-code"
                               data-checkout-postcode
                               value="<?php echo e($values['shipping_postcode']); ?>"
                               <?php echo isset($errors['shipping_postcode']) ? 'aria-invalid="true" aria-describedby="err-shipping-postcode"' : ''; ?>>
                        <small style="color: var(--text-muted); font-size: 0.8rem;">Free delivery within 5 miles of S6 &middot; &pound;2.50 further</small>
                        <?php if (isset($errors['shipping_postcode'])): ?>
                            <small id="err-shipping-postcode" role="alert" style="color: #8f1d35;"><?php echo e($errors['shipping_postcode']); ?></small>
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

                <button type="submit" class="btn btn-primary" style="width: 100%;" data-checkout-submit>
                    Complete Order (<span data-checkout-btn-total><?php echo e(format_gbp((int) $summary['total_pence'])); ?></span>)
                </button>
            </form>

            <aside class="panel-card" data-checkout-summary data-subtotal-pence="<?php echo (int) $summary['subtotal_pence']; ?>">
                <h2>Order Summary</h2>
                <?php foreach ($summary['items'] as $line): ?>
                    <div class="summary-row">
                        <span><?php echo e($line['product']['name']); ?> &times; <?php echo (int) $line['quantity']; ?></span>
                        <span><?php echo e(format_gbp((int) $line['line_total_pence'])); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row">
                    <span data-checkout-shipping-label><?php echo !empty($summary['is_free_local_delivery']) ? 'Local Delivery (within 5 miles of S6)' : 'UK Delivery'; ?></span>
                    <span data-checkout-shipping-amount><?php echo !empty($summary['is_free_local_delivery']) ? 'Free' : e(format_gbp((int) $summary['shipping_pence'])); ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total to Pay</span>
                    <span data-checkout-total><?php echo e(format_gbp((int) $summary['total_pence'])); ?></span>
                </div>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.75rem;" data-checkout-delivery-hint>
                    Free local delivery within 5 miles of Sheffield S6, or &pound;2.50 across the rest of the UK.
                </p>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
