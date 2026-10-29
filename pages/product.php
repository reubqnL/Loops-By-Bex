<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$slug = trim((string) ($_GET['slug'] ?? ''));
$product = get_product_by_slug($pdo, $slug);

if ($product === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit();
}

$pageTitle = (string) $product['name'];
$pageDescription = (string) ($product['short_description'] ?? $product['description']);
$activeNav = 'shop';
$pageStyles = ['/style/shop.css'];

$sideImage = trim((string) ($product['side_image_path'] ?? ''));
$activeView = (($_GET['view'] ?? '') === 'side' && $sideImage !== '') ? 'side' : 'front';
$initialImage = $activeView === 'side' ? $sideImage : (string) $product['image_path'];
$initialAlt = $activeView === 'side'
    ? $product['name'] . ' — side view'
    : (string) $product['name'];

$related = array_values(array_filter(
    get_all_products($pdo),
    static fn(array $item): bool => $item['slug'] !== $product['slug']
));
$related = array_slice($related, 0, 4);

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <p style="margin-bottom: 1rem;">
            <a href="/shop" class="text-arrow-link">&larr; Back to Shop</a>
        </p>

        <section class="product-detail-card">
            <div class="product-detail-media-wrap">
                <div class="product-detail-media">
                    <img src="<?php echo e($initialImage); ?>"
                         alt="<?php echo e($initialAlt); ?>"
                         width="600"
                         height="600"
                         data-product-main-img>
                </div>

                <?php if ($sideImage !== ''): ?>
                    <div class="product-thumb-row" role="group" aria-label="Product photo views">
                        <a href="/product?slug=<?php echo urlencode($product['slug']); ?>"
                           class="product-thumb-btn<?php echo $activeView === 'front' ? ' is-active' : ''; ?>"
                           data-product-thumb
                           data-src="<?php echo e($product['image_path']); ?>"
                           data-alt="<?php echo e($product['name']); ?>"
                           aria-pressed="<?php echo $activeView === 'front' ? 'true' : 'false'; ?>">
                            <img src="<?php echo e($product['image_path']); ?>" alt="" width="44" height="44">
                            <span>Front view</span>
                        </a>
                        <a href="/product?slug=<?php echo urlencode($product['slug']); ?>&amp;view=side"
                           class="product-thumb-btn<?php echo $activeView === 'side' ? ' is-active' : ''; ?>"
                           data-product-thumb
                           data-src="<?php echo e($sideImage); ?>"
                           data-alt="<?php echo e($product['name'] . ' — side view'); ?>"
                           aria-pressed="<?php echo $activeView === 'side' ? 'true' : 'false'; ?>">
                            <img src="<?php echo e($sideImage); ?>" alt="" width="44" height="44">
                            <span>Side view</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-info">
                <span class="badge-category"><?php echo e($product['category']); ?></span>
                <h1><?php echo e($product['name']); ?></h1>
                <p class="detail-price"><?php echo e(format_gbp((int) $product['price_pence'])); ?></p>
                <p><?php echo e($product['description']); ?></p>

                <?php if ((int) $product['stock_qty'] > 0): ?>
                    <form action="/cart-action.php" method="POST" class="add-detail-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
                        <input type="hidden" name="return_to" value="/cart">

                        <?php if (!empty($product['color_options']) && is_array($product['color_options'])): ?>
                            <div class="product-option-box">
                                <label for="product-color">Colour <span class="option-note">(available in different colours &amp; styles)</span></label>
                                <select id="product-color" name="color">
                                    <?php foreach ($product['color_options'] as $colorOpt): ?>
                                        <option value="<?php echo e((string) $colorOpt); ?>"><?php echo e((string) $colorOpt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="add-detail-controls">
                            <label for="product-quantity" class="sr-only">Quantity for <?php echo e($product['name']); ?></label>
                            <input type="number" id="product-quantity" name="quantity" value="1" min="1" max="<?php echo (int) min(10, (int) $product['stock_qty']); ?>" aria-label="Quantity">
                            <button type="submit" class="btn btn-primary">Add to Cart &rarr;</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p><strong>Out of stock</strong> — message Bex on the <a href="/contact" class="text-arrow-link">Contact page</a> to request one made to order.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section-new-arrivals" style="margin-top: 2.5rem;">
            <div class="sidebar-card-header">
                <h2>You Might Also Like</h2>
                <a href="/shop" class="text-arrow-link">View All &rarr;</a>
            </div>
            <div class="product-grid-4">
                <?php foreach ($related as $item): ?>
                    <article class="product-card">
                        <a href="/product?slug=<?php echo urlencode($item['slug']); ?>" class="product-card-media">
                            <img src="<?php echo e($item['image_path']); ?>" alt="<?php echo e($item['name']); ?>" width="320" height="320" loading="lazy">
                        </a>
                        <h3 class="product-card-title">
                            <a href="/product?slug=<?php echo urlencode($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                        </h3>
                        <p class="product-card-price"><?php echo e(format_gbp((int) $item['price_pence'])); ?></p>
                        <form action="/cart-action.php" method="POST" class="product-card-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="slug" value="<?php echo e($item['slug']); ?>">
                            <input type="hidden" name="return_to" value="/product?slug=<?php echo e($product['slug']); ?>">
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
