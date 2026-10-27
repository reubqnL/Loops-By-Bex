<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$resolvedTitle = isset($pageTitle) && trim((string) $pageTitle) !== ''
    ? trim((string) $pageTitle) . ' | Loops By Bex'
    : 'Loops By Bex';
$currentNav = $activeNav ?? '';
$cartCount = get_cart_item_count();
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$isSearchOpen = $searchQuery !== '';
$flash = consume_flash();

$siteNavItems = [
    'home' => ['label' => 'Home', 'href' => '/'],
    'shop' => ['label' => 'Shop', 'href' => '/shop'],
    'about' => ['label' => 'About Me', 'href' => '/about'],
    'blog' => ['label' => 'Blog', 'href' => '/blog'],
    'contact' => ['label' => 'Contact', 'href' => '/contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($resolvedTitle); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style/global.css">
    <link rel="icon" type="image/png" href="/assets/favicon.png">
</head>
<body class="site-body">
<a href="#main-content" class="skip-link">Skip to main content</a>

<header class="site-header">
    <div class="site-container header-inner">
        <a href="/" class="brand-logo" aria-label="Loops by Bex — Home">
            <img src="/assets/logo.svg" alt="Loops by Bex" width="142" height="50">
        </a>

        <button type="button"
                class="mobile-menu-toggle"
                id="mobile-menu-toggle"
                aria-label="Toggle navigation menu"
                aria-controls="primary-nav"
                aria-expanded="false">
            <span class="hamburger-bar" aria-hidden="true"></span>
            <span class="hamburger-bar" aria-hidden="true"></span>
            <span class="hamburger-bar" aria-hidden="true"></span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="Primary navigation">
            <ul class="nav-list">
                <?php foreach ($siteNavItems as $key => $item): ?>
                    <?php $isActive = ($currentNav === $key); ?>
                    <li>
                        <a href="<?php echo e($item['href']); ?>"
                           class="nav-link<?php echo $isActive ? ' is-active' : ''; ?>"
                           <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                            <?php echo e($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <button type="button"
                    class="icon-btn search-toggle-btn"
                    id="search-toggle-btn"
                    aria-label="Toggle product search"
                    aria-controls="header-search-bar"
                    aria-expanded="<?php echo $isSearchOpen ? 'true' : 'false'; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="20" y1="20" x2="16.35" y2="16.35"></line>
                </svg>
            </button>

            <a href="/account"
               class="icon-btn<?php echo $currentNav === 'account' ? ' is-active' : ''; ?>"
               aria-label="Track order and account"
               <?php echo $currentNav === 'account' ? 'aria-current="page"' : ''; ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                    <circle cx="12" cy="8" r="4"></circle>
                </svg>
            </a>

            <a href="/cart"
               class="icon-btn cart-btn<?php echo $currentNav === 'cart' ? ' is-active' : ''; ?>"
               aria-label="Shopping cart, <?php echo (int) $cartCount; ?> item<?php echo $cartCount === 1 ? '' : 's'; ?>"
               <?php echo $currentNav === 'cart' ? 'aria-current="page"' : ''; ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="9" cy="20" r="1.5"></circle>
                    <circle cx="18" cy="20" r="1.5"></circle>
                    <path d="M2.5 3h2.7l2.2 11.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 1.9-1.4L21.5 7H6.2"></path>
                </svg>
                <span class="cart-badge" aria-hidden="true"><?php echo (int) $cartCount; ?></span>
            </a>
        </div>
    </div>

    <div class="header-search-bar<?php echo $isSearchOpen ? ' is-open' : ''; ?>" id="header-search-bar" <?php echo $isSearchOpen ? '' : 'hidden'; ?>>
        <div class="site-container">
            <form action="/shop" method="GET" class="header-search-form" role="search" aria-label="Shop product search">
                <label for="header-search-input" class="sr-only">Search handmade crochet items</label>
                <input type="search" id="header-search-input" name="q" value="<?php echo e($searchQuery); ?>" placeholder="Search handmade crochet items...">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
        </div>
    </div>
</header>

<?php if ($flash !== null): ?>
    <?php $isErrorFlash = (($flash['type'] ?? '') === 'error'); ?>
    <div class="site-container flash-wrapper">
        <div class="flash-banner flash-<?php echo e($flash['type'] ?? 'info'); ?>"
             role="<?php echo $isErrorFlash ? 'alert' : 'status'; ?>"
             aria-live="<?php echo $isErrorFlash ? 'assertive' : 'polite'; ?>">
            <span><?php echo e($flash['message'] ?? ''); ?></span>
        </div>
    </div>
<?php endif; ?>
