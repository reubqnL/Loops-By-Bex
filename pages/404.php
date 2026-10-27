<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
http_response_code(404);

$pageTitle = 'Page Not Found';
$activeNav = '';

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <section class="prose-card" style="text-align: center;">
            <h1>Dropped a Stitch! (404)</h1>
            <p>Sorry, we couldn't find the page you were looking for.</p>
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.25rem;">
                <a href="/" class="btn btn-primary" style="text-decoration: none; color: #ffffff;">Back to Home</a>
                <a href="/shop" class="btn btn-soft-pink" style="text-decoration: none;">Visit Shop</a>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
