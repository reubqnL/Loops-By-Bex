<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/catalog.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$baseUrl = 'https://loopsbybex.co.uk';
$today = gmdate('Y-m-d');

$staticRoutes = [
    ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
    ['path' => '/shop', 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['path' => '/about', 'changefreq' => 'monthly', 'priority' => '0.8'],
    ['path' => '/blog', 'changefreq' => 'weekly', 'priority' => '0.7'],
    ['path' => '/contact', 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['path' => '/privacy', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ['path' => '/terms', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ['path' => '/returns', 'changefreq' => 'yearly', 'priority' => '0.4'],
];

$products = get_all_products($pdo ?? null);
$categories = get_product_categories($products);

$xmlEscape = static fn(string $val): string => htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticRoutes as $route) {
    echo "  <url>\n";
    echo '    <loc>' . $xmlEscape($baseUrl . $route['path']) . "</loc>\n";
    echo '    <lastmod>' . $xmlEscape($today) . "</lastmod>\n";
    echo '    <changefreq>' . $xmlEscape($route['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . $xmlEscape($route['priority']) . "</priority>\n";
    echo "  </url>\n";
}

foreach ($categories as $category) {
    $catUrl = $baseUrl . '/shop?category=' . rawurlencode($category);
    echo "  <url>\n";
    echo '    <loc>' . $xmlEscape($catUrl) . "</loc>\n";
    echo '    <lastmod>' . $xmlEscape($today) . "</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

foreach ($products as $product) {
    $slug = trim((string) ($product['slug'] ?? ''));
    if ($slug === '') {
        continue;
    }
    $prodUrl = $baseUrl . '/product?slug=' . rawurlencode($slug);
    echo "  <url>\n";
    echo '    <loc>' . $xmlEscape($prodUrl) . "</loc>\n";
    echo '    <lastmod>' . $xmlEscape($today) . "</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
