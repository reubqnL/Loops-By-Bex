<?php
declare(strict_types=1);

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
$activeNav = 'shop';

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
            <div class="product-detail-media">
                <img src="<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>" width="500" height="400">
            </div>

            <div class="product-detail-info">
                <span class="badge-category"><?php echo e($product['category']); ?></span>
                <h1><?php echo e($product['name']); ?></h1>
                <p class="detail-price"><?php echo e(format_gbp((int) $product['price_pence'])); ?></p>
                <p><?php echo e($product['description']); ?></p>

                <form action="/cart-action.php" method="POST" class="add-detail-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
                    <input type="hidden" name="return_to" value="/cart">
                    <label for="product-quantity" class="sr-only">Quantity for <?php echo e($product['name']); ?></label>
                    <input type="number" id="product-quantity" name="quantity" value="1" min="1" max="<?php echo (int) min(10, (int) $product['stock_qty']); ?>" aria-label="Quantity">
                    <button type="submit" class="btn btn-primary">Add to Cart &rarr;</button>
                </form>
            </div>
        </section>

        <section class="section-new-arrivals" style="margin-top: 2.5rem;">
            <div class="sidebar-card-header">
                <h2>More Handmade Placeholders</h2>
                <a href="/shop" class="text-arrow-link">View All &rarr;</a>
            </div>
            <div class="product-grid-4">
                <?php foreach ($related as $item): ?>
                    <article class="product-card">
                        <a href="/product?slug=<?php echo urlencode($item['slug']); ?>" class="product-card-media">
                            <img src="<?php echo e($item['image_path']); ?>" alt="<?php echo e($item['name']); ?>" width="320" height="256" loading="lazy">
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
