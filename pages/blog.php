<?php
require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Blog';
$activeNav = 'blog';
$pageStyles = ['/style/blog.css'];

$posts = [
    [
        'slug' => 'choosing-cosy-yarn-for-handmade-plushies',
        'title' => 'Choosing Cosy Yarn for Handmade Plushies',
        'date' => 'October 2026',
        'category' => 'Behind the Stitches',
        'excerpt' => 'A behind-the-scenes look at selecting super-soft chunky chenille and durable yarns for every handmade creation.',
        'image' => '/assets/love-bunny.webp',
    ],
    [
        'slug' => 'caring-for-your-crochet-friends',
        'title' => 'How to Care for Your Crochet Keepsakes',
        'date' => 'October 2026',
        'category' => 'Care Guide',
        'excerpt' => 'Simple gentle spot-clean and air-drying tips to keep handmade crochet friends looking fluffy for years to come.',
        'image' => '/assets/green-axolotl.webp',
    ],
    [
        'slug' => 'from-sketchbook-to-crochet-hook',
        'title' => 'From Idea to Finished Loop',
        'date' => 'October 2026',
        'category' => 'Studio Notes',
        'excerpt' => 'How colour combinations and stitch counts turn into finished plushies, scrunchies, and gifts.',
        'image' => '/assets/pink-and-purple-scrunchie.webp',
    ],
];
$selectedSlug = trim((string) ($_GET['post'] ?? ''));
$selectedPost = null;

if ($selectedSlug !== '') {
    foreach ($posts as $post) {
        if ($post['slug'] === $selectedSlug) {
            $selectedPost = $post;
            break;
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <?php if ($selectedPost !== null): ?>
            <p style="margin-bottom: 1rem;">
                <a href="/blog" class="text-arrow-link">&larr; Back to All Posts</a>
            </p>
            <article class="prose-card">
                <p style="font-size: 0.85rem; font-weight: 600; color: var(--brand-pink);">
                    <?php echo e($selectedPost['category']); ?> &middot; <?php echo e($selectedPost['date']); ?>
                </p>
                <h1><?php echo e($selectedPost['title']); ?></h1>
                <div style="margin: 1.25rem 0; border-radius: 14px; overflow: hidden;">
                    <img src="<?php echo e($selectedPost['image']); ?>" alt="<?php echo e($selectedPost['title']); ?>" width="640" height="400">
                </div>
                <p><?php echo e($selectedPost['excerpt']); ?></p>
                <p>Full journal article content will be published here once Loops By Bex launches.</p>
            </article>
        <?php else: ?>
            <div class="page-header-band">
                <div>
                    <h1>Studio Notes &amp; Crochet Journal</h1>
                    <p>Behind-the-scenes updates, yarn care guides, and maker stories.</p>
                </div>
            </div>

            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card">
                        <a href="/blog?post=<?php echo urlencode($post['slug']); ?>" class="blog-card-img">
                            <img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" width="400" height="250" loading="lazy">
                        </a>
                        <div class="blog-card-body">
                            <span class="blog-meta"><?php echo e($post['category']); ?> &middot; <?php echo e($post['date']); ?></span>
                            <h2>
                                <a href="/blog?post=<?php echo urlencode($post['slug']); ?>"><?php echo e($post['title']); ?></a>
                            </h2>
                            <p><?php echo e($post['excerpt']); ?></p>
                            <a href="/blog?post=<?php echo urlencode($post['slug']); ?>" class="text-arrow-link" style="margin-top: auto;">Read post &rarr;</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
