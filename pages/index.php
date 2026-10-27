<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Home';
$activeNav = 'home';

$mainArrivals = get_featured_main_products($pdo, 4);
$sidebarArrivals = get_featured_sidebar_products($pdo, 6);

$heroSlides = [
    [
        'tagline' => 'Handmade with love ♡',
        'heading' => 'Crochet Friends for Every Mood',
        'description' => 'Unique, handmade crochet creations, perfect for gifts, play and home.',
        'cta_label' => 'Shop Now →',
        'cta_href' => '/shop',
        'image' => '/assets/placeholders/hero-plushies.svg',
        'image_alt' => 'Placeholder illustration of three handmade crochet plushies',
        'is_h1' => true,
    ],
    [
        'tagline' => 'Crafted stitch by stitch ♡',
        'heading' => 'The Story Behind Every Loop',
        'description' => 'Discover how a passion for soft yarn and cosy designs grew into handmade keepsakes made with care.',
        'cta_label' => 'Read My Story →',
        'cta_href' => '/about',
        'image' => '/assets/placeholders/hero-story.svg',
        'image_alt' => 'Placeholder illustration of colourful yarn balls and a crochet hook',
        'is_h1' => false,
    ],
    [
        'tagline' => 'Custom ideas welcome ♡',
        'heading' => 'Have a Question or Custom Idea?',
        'description' => 'Looking for a particular colour combination or gifting advice? Reach out directly and say hello.',
        'cta_label' => 'Get in Touch →',
        'cta_href' => '/contact',
        'image' => '/assets/placeholders/hero-custom.svg',
        'image_alt' => 'Placeholder illustration of a wrapped handmade gift box',
        'is_h1' => false,
    ],
];
$totalSlides = count($heroSlides);

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content" tabindex="-1">
    <div class="site-container home-layout-grid">
        <div class="home-primary-col">
            <section class="hero-carousel"
                     data-carousel
                     role="region"
                     aria-roledescription="carousel"
                     aria-label="Featured Loops By Bex highlights">
                <div class="sr-only" data-carousel-liveregion aria-live="polite" aria-atomic="true"></div>

                <button type="button" class="carousel-arrow carousel-prev" data-carousel-prev aria-label="Previous slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                <div class="hero-slides">
                    <?php foreach ($heroSlides as $index => $slide): ?>
                        <?php $isActiveSlide = ($index === 0); ?>
                        <div class="hero-slide<?php echo $isActiveSlide ? ' is-active' : ''; ?>"
                             data-carousel-slide="<?php echo (int) $index; ?>"
                             role="group"
                             aria-roledescription="slide"
                             aria-label="<?php echo ($index + 1) . ' of ' . $totalSlides; ?>"
                             aria-hidden="<?php echo $isActiveSlide ? 'false' : 'true'; ?>"
                             <?php echo $isActiveSlide ? '' : 'inert'; ?>>
                            <div class="hero-copy">
                                <span class="hero-handwritten"><?php echo e($slide['tagline']); ?></span>
                                <?php if ($slide['is_h1']): ?>
                                    <h1 class="hero-heading"><?php echo e($slide['heading']); ?></h1>
                                <?php else: ?>
                                    <h2 class="hero-heading"><?php echo e($slide['heading']); ?></h2>
                                <?php endif; ?>
                                <p class="hero-description"><?php echo e($slide['description']); ?></p>
                                <a href="<?php echo e($slide['cta_href']); ?>" class="btn btn-primary">
                                    <?php echo e($slide['cta_label']); ?>
                                </a>
                            </div>
                            <div class="hero-visual">
                                <img src="<?php echo e($slide['image']); ?>" alt="<?php echo e($slide['image_alt']); ?>" width="390" height="278">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="carousel-arrow carousel-next" data-carousel-next aria-label="Next slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>

                <div class="carousel-dots" role="group" aria-label="Choose slide">
                    <?php foreach ($heroSlides as $index => $slide): ?>
                        <button type="button"
                                class="carousel-dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-carousel-dot="<?php echo (int) $index; ?>"
                                aria-label="Slide <?php echo (int) ($index + 1); ?>: <?php echo e($slide['heading']); ?>"
                                <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>></button>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="section-new-arrivals" aria-labelledby="new-arrivals-heading">
                <div class="arrivals-header">
                    <div class="arrivals-title-group">
                        <h2 class="sparkle-heading" id="new-arrivals-heading">
                            <svg class="sparkle-rays" viewBox="0 0 24 28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
                                <line x1="18" y1="5" x2="6" y2="2"></line>
                                <line x1="18" y1="14" x2="3" y2="14"></line>
                                <line x1="18" y1="23" x2="6" y2="26"></line>
                            </svg>
                            <span>New Arrivals</span>
                            <svg class="sparkle-rays" viewBox="0 0 24 28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
                                <line x1="6" y1="5" x2="18" y2="2"></line>
                                <line x1="6" y1="14" x2="21" y2="14"></line>
                                <line x1="6" y1="23" x2="18" y2="26"></line>
                            </svg>
                        </h2>
                        <p class="section-subtitle">Freshly made, just for you</p>
                    </div>
                    <div class="arrivals-action">
                        <a href="/shop" class="btn btn-outline-pink btn-sm" aria-label="View all products in shop">View All &rarr;</a>
                    </div>
                </div>

                <div class="product-grid-4">
                    <?php foreach ($mainArrivals as $product): ?>
                        <article class="product-card">
                            <a href="/product?slug=<?php echo urlencode($product['slug']); ?>" class="product-card-media" tabindex="-1" aria-hidden="true">
                                <img src="<?php echo e($product['image_path']); ?>" alt="" width="320" height="256" loading="lazy">
                            </a>
                            <h3 class="product-card-title">
                                <a href="/product?slug=<?php echo urlencode($product['slug']); ?>"><?php echo e($product['name']); ?></a>
                            </h3>
                            <p class="product-card-price"><?php echo e(format_gbp((int) $product['price_pence'])); ?></p>
                            <form action="/cart-action.php" method="POST" class="product-card-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="slug" value="<?php echo e($product['slug']); ?>">
                                <input type="hidden" name="return_to" value="/">
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
            </section>
        </div>

        <aside class="home-sidebar-col" aria-label="Store highlights and maker story">
            <section class="sidebar-card" aria-labelledby="sidebar-arrivals-heading">
                <div class="sidebar-card-header">
                    <h2 id="sidebar-arrivals-heading">New Arrivals</h2>
                    <a href="/shop" class="text-arrow-link" aria-label="View all new arrivals in shop">View All &rarr;</a>
                </div>
                <div class="sidebar-arrivals-grid">
                    <?php foreach ($sidebarArrivals as $item): ?>
                        <a href="/product?slug=<?php echo urlencode($item['slug']); ?>"
                           class="mini-product-card"
                           aria-label="<?php echo e($item['name'] . ', ' . format_gbp((int) $item['price_pence'])); ?>">
                            <div class="mini-product-thumb">
                                <img src="<?php echo e($item['image_path']); ?>" alt="" width="160" height="160" loading="lazy">
                            </div>
                            <span class="mini-product-name"><?php echo e($item['name']); ?></span>
                            <span class="mini-product-price"><?php echo e(format_gbp((int) $item['price_pence'])); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="hook-banner-card">
                <img src="/assets/hook-to-heart-banner.svg" alt="From my hook to your heart" width="640" height="210" loading="lazy">
            </div>

            <section class="sidebar-card about-preview-card" aria-labelledby="about-preview-heading">
                <h2 class="about-card-heading" id="about-preview-heading">About Me</h2>
                <div class="about-preview-body">
                    <div class="about-preview-photo">
                        <img src="/assets/placeholders/maker-placeholder.svg" alt="Placeholder portrait of Bex, the maker" width="150" height="180" loading="lazy">
                    </div>
                    <div class="about-preview-content">
                        <h3>About Me</h3>
                        <p class="about-maker-sub">Meet Bex, the Maker</p>
                        <p>
                            <strong>Crocheting,</strong> creativity and a love for handmade. Each piece is made with care, using quality yarn and a big heart.
                        </p>
                        <a href="/about" class="btn btn-soft-pink btn-sm">Read My Story &rarr;</a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
