<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Your Cart';
$activeNav = 'cart';
$pageStyles = ['/style/cart.css'];
$summary = get_cart_summary($pdo);

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <div class="page-header-band">
            <div>
                <h1>Your Shopping Cart</h1>
                <p><?php echo (int) $summary['item_count']; ?> item<?php echo $summary['item_count'] === 1 ? '' : 's'; ?> in your basket</p>
            </div>
            <a href="/shop" class="btn btn-outline-pink btn-sm">&larr; Continue Shopping</a>
        </div>

        <?php if (empty($summary['items'])): ?>
            <div class="panel-card" style="text-align: center; padding: 3rem 1.5rem;">
                <h2>Your cart is currently empty</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.35rem;">Explore our handmade crochet placeholder items in the shop.</p>
                <a href="/shop" class="btn btn-primary">Browse Shop &rarr;</a>
            </div>
        <?php else: ?>
            <div class="two-col-panel">
                <section class="panel-card">
                    <div class="sidebar-card-header">
                        <h2>Items</h2>
                        <form action="/cart-action.php" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="clear">
                            <input type="hidden" name="return_to" value="/cart">
                            <button type="submit" class="text-arrow-link" style="background: none; border: none; cursor: pointer;">Clear cart</button>
                        </form>
                    </div>

                    <?php foreach ($summary['items'] as $line): ?>
                        <?php $item = $line['product']; ?>
                        <div class="cart-item-row">
                            <a href="/product?slug=<?php echo urlencode($item['slug']); ?>" class="cart-thumb">
                                <img src="<?php echo e($item['image_path']); ?>" alt="<?php echo e($item['name']); ?>" width="84" height="70">
                            </a>

                            <div>
                                <h3 style="font-size: 1rem;">
                                    <a href="/product?slug=<?php echo urlencode($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                                </h3>
                                <p style="font-size: 0.88rem; color: var(--text-secondary);">
                                    Unit price: <?php echo e(format_gbp((int) $line['unit_price_pence'])); ?>
                                </p>
                            </div>

                            <form action="/cart-action.php" method="POST" class="cart-qty-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="slug" value="<?php echo e($item['slug']); ?>">
                                <input type="hidden" name="return_to" value="/cart">
                                <label for="qty-<?php echo e($item['slug']); ?>" class="sr-only">Quantity for <?php echo e($item['name']); ?></label>
                                <input type="number" id="qty-<?php echo e($item['slug']); ?>" name="quantity" value="<?php echo (int) $line['quantity']; ?>" min="0" max="10">
                                <button type="submit" class="btn btn-soft-pink btn-sm" aria-label="Update quantity for <?php echo e($item['name']); ?>">Update</button>
                            </form>

                            <div style="text-align: right;">
                                <p style="font-weight: 700;"><?php echo e(format_gbp((int) $line['line_total_pence'])); ?></p>
                                <form action="/cart-action.php" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="slug" value="<?php echo e($item['slug']); ?>">
                                    <input type="hidden" name="return_to" value="/cart">
                                    <button type="submit" class="text-arrow-link" style="background: none; border: none; cursor: pointer; font-size: 0.82rem;" aria-label="Remove <?php echo e($item['name']); ?> from cart">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>

                <aside class="panel-card">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo e(format_gbp((int) $summary['subtotal_pence'])); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>UK Delivery</span>
                        <span><?php echo e(format_gbp((int) $summary['shipping_pence'])); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span><?php echo e(format_gbp((int) $summary['total_pence'])); ?></span>
                    </div>

                    <p style="font-size: 0.84rem; color: var(--text-muted); margin: 0.85rem 0 1.2rem;">
                        Prices are calculated on the server in GBP. All placeholder items are currently <?php echo e(format_gbp(0)); ?>.
                    </p>

                    <a href="/checkout" class="btn btn-primary" style="width: 100%;">Proceed to Checkout &rarr;</a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
