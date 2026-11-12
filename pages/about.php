<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'About Me';
$pageDescription = 'Meet Bex, the creator of Loops by Bex — handmade crochet plushies, accessories, and gifts made in Sheffield.';
$activeNav = 'about';
$pageStyles = ['/style/shop.css'];

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <section class="product-detail-card about-detail-card">
            <div class="product-detail-media about-portrait-media">
                <img src="/assets/bex.webp" alt="Bex with a selection of handmade crochet creations" width="450" height="600">
            </div>

            <div class="product-detail-info">
                <span class="badge-category">Handmade in Sheffield</span>
                <h1>Meet Bex, the Maker</h1>
                <p>
                    Hi there, I&rsquo;m Bex, the creator of &lsquo;Loops by Bex.&rsquo; Over the last few years, I have developed a passion for crochet, and I have lost track of the number of things I have made! Crochet quickly became my favourite hobby as well as being a great way for me to relieve stress.
                </p>
                <p>
                    For the last almost year I have been selling my handmade crochet items through a local craft caf&eacute; in Hillsborough, but sadly they have had to close. I had previously considered selling my crochet items online, and the closure of The Crafty Cat has been the encouragement I needed to take this next step.
                </p>
                <p>
                    Through selling my handmade items, I have raised funds that will enable me to complete some voluntary work overseas when all my studies are complete. I am so grateful for the support of so many people who have helped me with my fundraising. With the launch of my online shop, I will continue to put some of my earnings towards my fundraising, but I will also be using it as a personal income.
                </p>
                <p>
                    All the items available for sale can be seen and bought directly through my website&hellip; but once they have gone, they have gone! I will be regularly updating the shop as I finish new stock, so please keep an eye out. I am not taking custom orders while I focus on my upcoming GCSEs.
                </p>
                <p>
                    Thank you so much for your support!<br>
                    <strong>Bex.</strong>
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.35rem;">
                    <a href="/shop" class="btn btn-primary">Explore the Shop &rarr;</a>
                    <a href="/contact" class="btn btn-soft-pink">Get in Touch</a>
                </div>
            </div>
        </section>

        <div class="hook-banner-card" style="max-width: 720px; margin: 2rem auto 0;">
            <img src="/assets/hook-to-heart-banner.svg" alt="From my hook to your heart" width="640" height="210" loading="lazy">
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
