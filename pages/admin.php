<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow, noarchive');

$pageTitle = 'Owner Orders';
$activeNav = '';
$pageStyles = ['/style/cart.css'];
$adminPassword = env_val('ADMIN_PASSWORD', '');
$appEnvironment = strtolower(env_val('APP_ENV', 'production'));
$appUrlHost = strtolower((string) parse_url(env_val('APP_URL', ''), PHP_URL_HOST));
$adminPasswordIsLocalTest = $appEnvironment === 'development'
    && in_array($appUrlHost, ['localhost', '127.0.0.1', '::1'], true)
    && hash_equals('TEST_PASSWORD', $adminPassword);
$adminConfigured = strlen($adminPassword) >= 16 || $adminPasswordIsLocalTest;
$adminError = '';
$ordersError = '';
$restockError = '';
$now = time();

if (
    !empty($_SESSION['lbb_admin_authenticated'])
    && ($now - (int) ($_SESSION['lbb_admin_last_activity'] ?? 0)) > 1800
) {
    unset($_SESSION['lbb_admin_authenticated'], $_SESSION['lbb_admin_last_activity']);
    $adminError = 'Your private session expired. Please sign in again.';
}

$isAdmin = $adminConfigured && !empty($_SESSION['lbb_admin_authenticated']);

if (!$adminConfigured) {
    http_response_code(503);
} elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf_or_redirect('/admin');
    $action = trim((string) ($_POST['action'] ?? ''));

    if ($action === 'login') {
        $submittedPassword = (string) ($_POST['password'] ?? '');
        if (!check_rate_limit($pdo, 'admin_login', 5, 900)) {
            $adminError = 'Too many sign-in attempts. Please wait 15 minutes and try again.';
        } elseif ($submittedPassword === '' || !hash_equals($adminPassword, $submittedPassword)) {
            $adminError = 'The password was not recognised.';
        } else {
            regenerate_session_safely();
            $_SESSION['lbb_admin_authenticated'] = true;
            $_SESSION['lbb_admin_last_activity'] = time();
            header('Location: /admin', true, 303);
            exit();
        }
    } elseif ($action === 'logout' && $isAdmin) {
        unset($_SESSION['lbb_admin_authenticated'], $_SESSION['lbb_admin_last_activity']);
        regenerate_session_safely();
        header('Location: /admin?logged_out=1', true, 303);
        exit();
    } elseif (($action === 'mark_sold' || $action === 'restock') && $isAdmin) {
        $slug = trim((string) ($_POST['slug'] ?? ''));
        if (!preg_match('/^[a-z0-9-]{1,120}$/', $slug)) {
            $restockError = 'That product could not be updated.';
        } elseif (!($pdo instanceof PDO)) {
            $restockError = 'The product database is unavailable right now.';
        } else {
            try {
                $stockQty = $action === 'restock' ? 1 : 0;
                $stockStmt = $pdo->prepare(
                    'UPDATE products SET stock_qty = :stock_qty WHERE slug = :slug AND is_active = 1'
                );
                $stockStmt->execute([
                    'stock_qty' => $stockQty,
                    'slug' => $slug,
                ]);
                header('Location: /admin?inventory_updated=1', true, 303);
                exit();
            } catch (PDOException $e) {
                error_log('[LoopsByBex Admin] Inventory update failed: ' . $e->getMessage());
                $restockError = 'The product could not be updated right now.';
            }
        }
    } else {
        http_response_code(403);
        $adminError = 'Please sign in to view or update shop information.';
    }
}

$isAdmin = $adminConfigured && !empty($_SESSION['lbb_admin_authenticated']);
if ($isAdmin) {
    $_SESSION['lbb_admin_last_activity'] = time();
}

$orders = [];
$inventory = [];
if ($isAdmin) {
    if (!($pdo instanceof PDO)) {
        $ordersError = 'The order database is unavailable right now.';
    } else {
        try {
            $ordersStmt = $pdo->query(
                'SELECT
                    o.id AS order_id,
                    o.order_number,
                    o.customer_name,
                    o.customer_email,
                    o.shipping_line1,
                    o.shipping_line2,
                    o.shipping_city,
                    o.shipping_postcode,
                    o.shipping_country,
                    o.order_notes,
                    o.subtotal_pence,
                    o.shipping_pence,
                    o.total_pence,
                    o.payment_status,
                    o.created_at,
                    i.id AS order_item_id,
                    i.product_name,
                    i.quantity,
                    i.unit_price_pence,
                    i.line_total_pence
                 FROM (
                    SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT 100
                 ) AS o
                 LEFT JOIN order_items AS i ON i.order_id = o.id
                 ORDER BY o.created_at DESC, o.id DESC, i.id ASC'
            );
            $rows = $ordersStmt ? ($ordersStmt->fetchAll() ?: []) : [];
            foreach ($rows as $row) {
                $orderId = (int) $row['order_id'];
                if (!isset($orders[$orderId])) {
                    $orders[$orderId] = [
                        'order_number' => (string) $row['order_number'],
                        'customer_name' => (string) $row['customer_name'],
                        'customer_email' => (string) $row['customer_email'],
                        'shipping_line1' => (string) $row['shipping_line1'],
                        'shipping_line2' => (string) $row['shipping_line2'],
                        'shipping_city' => (string) $row['shipping_city'],
                        'shipping_postcode' => (string) $row['shipping_postcode'],
                        'shipping_country' => (string) $row['shipping_country'],
                        'order_notes' => (string) $row['order_notes'],
                        'subtotal_pence' => (int) $row['subtotal_pence'],
                        'shipping_pence' => (int) $row['shipping_pence'],
                        'total_pence' => (int) $row['total_pence'],
                        'payment_status' => (string) $row['payment_status'],
                        'created_at' => (string) $row['created_at'],
                        'items' => [],
                    ];
                }
                if ($row['order_item_id'] !== null) {
                    $orders[$orderId]['items'][] = [
                        'product_name' => (string) $row['product_name'],
                        'quantity' => (int) $row['quantity'],
                        'unit_price_pence' => (int) $row['unit_price_pence'],
                        'line_total_pence' => (int) $row['line_total_pence'],
                    ];
                }
            }
            $orders = array_values($orders);

            $inventory = array_map(
                static fn(array $product): array => [
                    'slug' => (string) $product['slug'],
                    'name' => (string) $product['name'],
                    'stock_qty' => (int) $product['stock_qty'],
                ],
                get_all_products($pdo)
            );
        } catch (PDOException $e) {
            error_log('[LoopsByBex Admin] Order list failed: ' . $e->getMessage());
            $ordersError = 'Orders could not be loaded right now.';
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container admin-page">
        <?php if (!$adminConfigured): ?>
            <section class="panel-card">
                <h1>Owner Orders</h1>
                <p>This private page needs an <code>ADMIN_PASSWORD</code> of at least 16 characters in server-only environment settings. A short test password works only for local development on localhost. Never add a real password to GitHub or share it in chat.</p>
                <p>Customers do not sign in here and can still check out as guests.</p>
            </section>
        <?php elseif (!$isAdmin): ?>
            <section class="panel-card admin-login-card">
                <h1>Owner Orders</h1>
                <p>Sign in to view private order and delivery details. This page is only for the shop owner.</p>
                <?php if ($adminError !== ''): ?>
                    <div class="flash-banner flash-error" role="alert"><?php echo e($adminError); ?></div>
                <?php elseif (isset($_GET['logged_out'])): ?>
                    <div class="flash-banner flash-info" role="status">You have signed out.</div>
                <?php endif; ?>
                <form action="/admin" method="POST" class="admin-login-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="login">
                    <div class="form-field">
                        <label for="admin-password">Owner password</label>
                        <input type="password" id="admin-password" name="password" required maxlength="512" autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Sign in</button>
                </form>
            </section>
        <?php else: ?>
            <div class="admin-page-heading">
                <div>
                    <p class="admin-eyebrow">Private shop area</p>
                    <h1>Orders</h1>
                    <p>Recent orders and delivery details. Customers do not need an account to buy.</p>
                    <?php if ($adminPasswordIsLocalTest): ?>
                        <div class="flash-banner flash-info" role="status">Local test password active. Change <code>ADMIN_PASSWORD</code> before making this site public.</div>
                    <?php endif; ?>
                </div>
                <form action="/admin" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-outline-pink btn-sm">Sign out</button>
                </form>
            </div>

            <?php if (isset($_GET['inventory_updated'])): ?>
                <div class="flash-banner flash-success" role="status">Product availability updated.</div>
            <?php endif; ?>
            <?php if ($restockError !== ''): ?>
                <div class="flash-banner flash-error" role="alert"><?php echo e($restockError); ?></div>
            <?php endif; ?>
            <?php if ($ordersError !== ''): ?>
                <div class="flash-banner flash-error" role="alert"><?php echo e($ordersError); ?></div>
            <?php endif; ?>

            <?php if ($ordersError === '' && empty($orders)): ?>
                <section class="panel-card"><p>No orders have been recorded yet.</p></section>
            <?php endif; ?>

            <div class="admin-order-list">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $status = strtolower((string) $order['payment_status']);
                    if (!in_array($status, ['paid', 'pending', 'failed', 'refunded'], true)) {
                        $status = 'unknown';
                    }
                    try {
                        $orderDate = (new DateTimeImmutable($order['created_at'], new DateTimeZone('UTC')))
                            ->setTimezone(new DateTimeZone('Europe/London'))
                            ->format('j M Y, H:i');
                    } catch (Exception $e) {
                        $orderDate = $order['created_at'];
                    }
                    ?>
                    <article class="admin-order-card">
                        <header class="admin-order-header">
                            <div>
                                <h2><?php echo e($order['order_number']); ?></h2>
                                <p><?php echo e($orderDate); ?></p>
                            </div>
                            <span class="admin-order-status is-<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
                        </header>
                        <div class="admin-order-columns">
                            <section>
                                <h3>Items</h3>
                                <?php if (empty($order['items'])): ?>
                                    <p>No item details were stored for this order.</p>
                                <?php else: ?>
                                    <ul class="admin-order-items">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li>
                                                <span><?php echo e($item['product_name']); ?> &times; <?php echo (int) $item['quantity']; ?></span>
                                                <strong><?php echo e(format_gbp((int) $item['line_total_pence'])); ?></strong>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <div class="admin-order-total">
                                    <span>Order total</span>
                                    <strong><?php echo e(format_gbp((int) $order['total_pence'])); ?></strong>
                                </div>
                            </section>
                            <section>
                                <h3>Deliver to</h3>
                                <p><strong><?php echo e($order['customer_name']); ?></strong><br>
                                    <?php echo e($order['customer_email']); ?><br>
                                    <?php echo e($order['shipping_line1']); ?><?php if ($order['shipping_line2'] !== ''): ?><br><?php echo e($order['shipping_line2']); ?><?php endif; ?><br>
                                    <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_postcode']); ?><br>
                                    <?php echo e($order['shipping_country']); ?>
                                </p>
                                <?php if ($order['order_notes'] !== ''): ?>
                                    <h3>Customer notes</h3>
                                    <p class="admin-order-notes"><?php echo e($order['order_notes']); ?></p>
                                <?php endif; ?>
                            </section>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <section class="panel-card admin-inventory-panel">
                <h2>Product availability</h2>
                <p>Paid orders mark their items SOLD automatically. Use these controls to mark an existing item sold or relist one after Bex has made another.</p>
                <?php if (empty($inventory)): ?>
                    <p>No active products were found.</p>
                <?php else: ?>
                    <div class="admin-inventory-list">
                        <?php foreach ($inventory as $item): ?>
                            <?php $itemIsSold = (int) $item['stock_qty'] <= 0; ?>
                            <div class="admin-inventory-row">
                                <div>
                                    <strong><?php echo e($item['name']); ?></strong>
                                    <span class="admin-order-status <?php echo $itemIsSold ? 'is-sold' : 'is-available'; ?>">
                                        <?php echo $itemIsSold ? 'Sold' : 'Available'; ?>
                                    </span>
                                </div>
                                <form action="/admin" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="<?php echo $itemIsSold ? 'restock' : 'mark_sold'; ?>">
                                    <input type="hidden" name="slug" value="<?php echo e($item['slug']); ?>">
                                    <button type="submit" class="btn btn-soft-pink btn-sm">
                                        <?php echo $itemIsSold ? 'Restock one' : 'Mark SOLD'; ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
