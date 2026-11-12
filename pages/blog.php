<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Blog — Coming Soon';
$pageDescription = 'Behind-the-scenes crochet stories, plushie care guides, and studio updates from Loops By Bex — coming soon.';
$activeNav = 'blog';
$pageStyles = ['/style/blog.css'];
$instagramUrl = safe_external_url(env_val('INSTAGRAM_URL', 'https://www.instagram.com/bex2_011/'), 'https://www.instagram.com/bex2_011/');

$upcomingTopics = [
    [
        'tag' => 'Behind the Stitches',
        'title' => 'From Yarn Stash to Finished Plushie',
        'blurb' => 'A peek inside Bex’s wool stash — choosing super-soft chunky chenille, pairing pastel shades, and bringing each character to life.',
        'image' => '/assets/love-bunny.webp',
        'alt' => 'Handmade white crochet Heart Bunny holding a pastel pink heart',
    ],
    [
        'tag' => 'Care Guide',
        'title' => 'Keeping Your Crochet Friends Fluffy',
        'blurb' => 'Simple, gentle spot-cleaning and air-drying tips so your handmade plushies, scrunchies, and pet bandanas stay looking brand new.',
        'image' => '/assets/green-axolotl.webp',
        'alt' => 'Mint green handmade chunky crochet axolotl plushie',
    ],
    [
        'tag' => 'Maker Diary',
        'title' => 'New Drops & Overseas Fundraising',
        'blurb' => 'Seasonal makes, fresh restock announcements, and updates on fundraising towards overseas voluntary work after GCSEs.',
        'image' => '/assets/bright-pink-christmas-loaf-cat.webp',
        'alt' => 'Festive pink and white crochet loaf cat wearing a sparkly Santa hat',
    ],
];

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container blog-coming-wrap">
        <section class="blog-coming-hero" aria-labelledby="blog-coming-heading">
            <div class="blog-orb blog-orb-1" aria-hidden="true"></div>
            <div class="blog-orb blog-orb-2" aria-hidden="true"></div>
            <div class="blog-orb blog-orb-3" aria-hidden="true"></div>

            <div class="blog-hero-copy">
                <div class="blog-live-pill">
                    <span class="pulse-dot" aria-hidden="true"></span>
                    <span>Studio Journal &middot; Coming Soon</span>
                </div>

                <h1 id="blog-coming-heading">Fresh Stories Are on the Hook&hellip;</h1>
                <p class="blog-hero-lead">
                    Bex is currently busy stitching new makes for the shop (and revising for upcoming GCSEs!), while putting together the very first studio journal posts.
                </p>
                <p class="blog-hero-sub">
                    Check back soon for behind-the-scenes crochet notes, plushie care guides, wool stash favourites, and fundraising updates.
                </p>

                <div class="blog-hero-actions">
                    <a href="/shop" class="btn btn-primary">Explore the Shop &rarr;</a>
                    <a href="<?php echo e($instagramUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-pink">
                        Follow @bex2_011 &rarr;
                    </a>
                    <a href="/about" class="btn btn-soft-pink">Read Bex&rsquo;s Story</a>
                </div>
            </div>

            <div class="blog-hero-stage" aria-hidden="true">
                <div class="yarn-scene-card">
                    <svg class="yarn-illustration" viewBox="0 0 280 175" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- twinkling sparkles -->
                        <g class="sparkle-group sparkle-1">
                            <path d="M46 28L49 36L57 39L49 42L46 50L43 42L35 39L43 36L46 28Z" fill="#d03874"/>
                        </g>
                        <g class="sparkle-group sparkle-2">
                            <path d="M236 24L238.2 30L244 32.2L238.2 34.4L236 40.5L233.8 34.4L228 32.2L233.8 30L236 24Z" fill="#9b6fd6"/>
                        </g>
                        <g class="sparkle-group sparkle-3">
                            <path d="M214 134L215.8 139L221 140.8L215.8 142.6L214 147.6L212.2 142.6L207 140.8L212.2 139L214 134Z" fill="#f59e0b"/>
                        </g>

                        <!-- soft shadow -->
                        <ellipse cx="118" cy="148" rx="58" ry="11" fill="#e5d8ec" opacity="0.65"/>

                        <!-- animated unspooling yarn loop -->
                        <path class="yarn-trail-bg" d="M156 122C186 126 196 92 222 96C245 100 246 132 266 126" stroke="#f3c5da" stroke-width="5" stroke-linecap="round"/>
                        <path class="yarn-trail-anim" d="M156 122C186 126 196 92 222 96C245 100 246 132 266 126" stroke="#d03874" stroke-width="5" stroke-linecap="round"/>

                        <!-- crochet hook -->
                        <g class="crochet-hook-anim">
                            <line x1="154" y1="32" x2="80" y2="142" stroke="#8b7aa8" stroke-width="7" stroke-linecap="round"/>
                            <line x1="154" y1="32" x2="80" y2="142" stroke="#bbaad6" stroke-width="3" stroke-linecap="round"/>
                            <path d="M154 32C159 25 166 27 162 34L157 41" stroke="#8b7aa8" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>

                        <!-- yarn ball -->
                        <g class="yarn-ball-anim">
                            <circle cx="116" cy="96" r="46" fill="#f7bdd6"/>
                            <circle cx="116" cy="96" r="46" stroke="#d03874" stroke-width="3"/>
                            <path d="M76 76C96 62 134 64 156 80" stroke="#d03874" stroke-width="3" stroke-linecap="round"/>
                            <path d="M72 95C98 80 136 82 160 100" stroke="#d03874" stroke-width="3" stroke-linecap="round"/>
                            <path d="M79 115C102 101 134 103 155 118" stroke="#d03874" stroke-width="3" stroke-linecap="round"/>
                            <path d="M98 53C86 78 88 114 104 139" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" opacity="0.7"/>
                            <path d="M132 54C118 80 120 114 136 138" stroke="#d03874" stroke-width="2.8" stroke-linecap="round"/>
                        </g>
                    </svg>

                    <div class="stitch-status-list">
                        <div class="stitch-status-item">
                            <div class="stitch-status-top">
                                <span>Stitching new shop stock</span>
                                <span class="stitch-chip is-live">Active</span>
                            </div>
                            <div class="stitch-bar"><span class="bar-fill-86"></span></div>
                        </div>

                        <div class="stitch-status-item">
                            <div class="stitch-status-top">
                                <span>Behind-the-scenes photos</span>
                                <span class="stitch-chip">In Progress</span>
                            </div>
                            <div class="stitch-bar"><span class="bar-fill-58"></span></div>
                        </div>

                        <div class="stitch-status-item">
                            <div class="stitch-status-top">
                                <span>First journal articles</span>
                                <span class="stitch-chip">Brewing</span>
                            </div>
                            <div class="stitch-bar"><span class="bar-fill-34"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="blog-preview-section" aria-labelledby="upcoming-posts-heading">
            <div class="blog-preview-header">
                <div>
                    <h2 id="upcoming-posts-heading">Sneak Peek: What&rsquo;s Coming to the Journal</h2>
                    <p>Topics currently in the notebook for upcoming posts</p>
                </div>
                <span class="upcoming-count-badge">3 Upcoming Series</span>
            </div>

            <div class="blog-grid">
                <?php foreach ($upcomingTopics as $topic): ?>
                    <article class="blog-card is-upcoming">
                        <div class="blog-card-img">
                            <img src="<?php echo e($topic['image']); ?>" alt="<?php echo e($topic['alt']); ?>" width="400" height="260" loading="lazy">
                            <span class="coming-ribbon">Coming Soon</span>
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-meta"><?php echo e($topic['tag']); ?></span>
                            <h3><?php echo e($topic['title']); ?></h3>
                            <p><?php echo e($topic['blurb']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
