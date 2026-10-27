<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Shop';
$activeNav = 'shop';

$allProducts = get_all_products($pdo);
$categories = get_product_categories($allProducts);
$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$filteredProducts = filter_products($allProducts, $searchQuery, $selectedCategory);

$returnUrl = '/shop';
$queryParts = [];
if ($selectedCategory !== '') {
    $queryParts['category'] = $selectedCategory;
}
if ($searchQuery !== '') {
    $queryParts['q'] = $searchQuery;
}
if (!empty($queryParts)) {
    $returnUrl .= '?' . http_build_query($queryParts);
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <div class="page-header-band">
            <div>
                <h1>Handmade Crochet Shop</h1>
                <p>Placeholder product catalogue — all items are currently set to <?php echo e(format_gbp(0)); ?> prior to launch.</p>
            </div>
            <a href="/cart" class="btn btn-soft-pink btn-sm">View Cart (<?php echo (int) get_cart_item_count(); ?>) &rarr;</a>
        </div>

        <div class="filter-bar">
            <nav class="category-pills" aria-label="Product categories">
                <a href="/shop<?php echo $searchQuery !== '' ? '?q=' . urlencode($searchQuery) : ''; ?>"
                   class="category-pill<?php echo $selectedCategory === '' ? ' is-active' : ''; ?>"
                   <?php echo $selectedCategory === '' ? 'aria-current="page"' : ''; ?>>
                    All Items
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $catParams = ['category' => $cat];
                    if ($searchQuery !== '') {
                        $catParams['q'] = $searchQuery;
                    }
                    $isSelectedCat = strcasecmp($selectedCategory, $cat) === 0;
                    ?>
                    <a href="/shop?<?php echo e(http_build_query($catParams)); ?>"
                       class="category-pill<?php echo $isSelectedCat ? ' is-active' : ''; ?>"
                       <?php echo $isSelectedCat ? 'aria-current="page"' : ''; ?>>
                        <?php echo e($cat); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($searchQuery !== '' || $selectedCategory !== ''): ?>
                <a href="/shop" class="text-arrow-link">Clear filters &times;</a>
            <?php endif; ?>
        </div>

        <?php if (empty($filteredProducts)): ?>
            <div class="panel-card">
                <h2>No matching crochet items found</h2>
                <p>Try clearing your search filter or browsing all categories.</p>
                <p style="margin-top: 1rem;">
                    <a href="/shop" class="btn btn-primary btn-sm">Show All Products</a>
                </p>
            </div>
        <?php else: ?>
            <div class="shop-grid">
                <?php foreach ($filteredProducts as $product): ?>
                    <article class="product-card">
                        <a href="/product?slug=<?php echo urlencode($product['slug']); ?>" class="product-card-media">
                            <img src="<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>" width="320" height="256" loading="lazy">
                        </a>
                        <h2 class="product-card-title">
                            <a href="/product?slug=<?php echo urlencode($product['slug']); ?>"><?php echo e($product['name']); ?></a>
                        </h2>
                        <p class="product-card-price"><?php echo e(format_gbp((int) $product['price_pence'])); ?></p>
                        <form action="/cart-action.php" method="POST" class="product-card-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
                            <input type="hidden" name="return_to" value="<?php echo e($returnUrl); ?>">
                            <button type="submit" class="btn btn-primary" aria-label="Add <?php echo e($product['name']); ?> to cart">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <circle cx="9" cy="20" r="1.5"></circle>
                                    <circle cx="18" cy="20" r="1.5"></circle>
                                    <path d="M2.5 3h2.7l2.2 11.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 1.9-1.4L21.5 7H6.2"></path>
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
