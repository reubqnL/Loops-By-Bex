<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'About Me';
$activeNav = 'about';
$pageStyles = ['/style/shop.css'];

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <section class="product-detail-card">
            <div class="product-detail-media">
                <img src="/assets/placeholders/maker-placeholder.svg" alt="Placeholder portrait of Bex, the maker" width="420" height="504">
            </div>

            <div class="product-detail-info">
                <span class="badge-category">Handmade in the UK</span>
                <h1>Meet Bex, the Maker</h1>
                <p>
                    Welcome to <strong>Loops By Bex</strong> — a small handmade crochet studio built around creativity, cosy textures, and joyful keepsakes.
                </p>
                <p>
                    Every plushie and accessory starts as a ball of yarn on a single crochet hook. Each piece is stitched by hand with care, quality yarn, and attention to the tiny details that give handmade friends their personality.
                </p>
                <p>
                    Whether you are picking out a thoughtful gift, decorating a cosy corner at home, or asking about a bespoke colourway, thank you for supporting slow, handmade craft.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.5rem;">
                    <a href="/shop" class="btn btn-primary">Explore the Shop &rarr;</a>
                    <a href="/contact" class="btn btn-soft-pink">Ask About Custom Orders</a>
                </div>
            </div>
        </section>

        <div class="hook-banner-card" style="max-width: 720px; margin: 2rem auto 0;">
            <img src="/assets/hook-to-heart-banner.svg" alt="From my hook to your heart" width="640" height="210" loading="lazy">
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
