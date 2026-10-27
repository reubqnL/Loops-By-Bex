<?php
declare(strict_types=1);

/**
 * Built-in placeholder catalog used until live product records are populated in MySQL.
 * Every product price is explicitly 0 pence (£0.00).
 */
function default_placeholder_products(): array
{
    return [
        [
            'id' => 1,
            'slug' => 'placeholder-plush-01',
            'name' => 'Placeholder Plush 01',
            'category' => 'Plushies',
            'short_description' => 'Handmade crochet placeholder item.',
            'description' => 'Soft handmade crochet plush placeholder. Final product photography, yarn blend details, and sizing measurements will appear here once stock goes live.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-01.svg',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 2,
            'slug' => 'placeholder-plush-02',
            'name' => 'Placeholder Plush 02',
            'category' => 'Plushies',
            'short_description' => 'Handmade crochet placeholder item.',
            'description' => 'Soft handmade crochet plush placeholder. Final product photography, yarn blend details, and sizing measurements will appear here once stock goes live.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-02.svg',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 3,
            'slug' => 'placeholder-plush-03',
            'name' => 'Placeholder Plush 03',
            'category' => 'Plushies',
            'short_description' => 'Handmade crochet placeholder item.',
            'description' => 'Soft handmade crochet plush placeholder. Final product photography, yarn blend details, and sizing measurements will appear here once stock goes live.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-03.svg',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 4,
            'slug' => 'placeholder-plush-04',
            'name' => 'Placeholder Plush 04',
            'category' => 'Plushies',
            'short_description' => 'Handmade crochet placeholder item.',
            'description' => 'Soft handmade crochet plush placeholder. Final product photography, yarn blend details, and sizing measurements will appear here once stock goes live.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-04.svg',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 5,
            'slug' => 'placeholder-item-05',
            'name' => 'Placeholder Item 05',
            'category' => 'Amigurumi',
            'short_description' => 'Compact handmade crochet keepsake placeholder.',
            'description' => 'Handmade crochet amigurumi placeholder crafted stitch by stitch with soft yarn.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-05.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 6,
            'slug' => 'placeholder-item-06',
            'name' => 'Placeholder Item 06',
            'category' => 'Amigurumi',
            'short_description' => 'Compact handmade crochet keepsake placeholder.',
            'description' => 'Handmade crochet amigurumi placeholder crafted stitch by stitch with soft yarn.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-06.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 7,
            'slug' => 'placeholder-item-07',
            'name' => 'Placeholder Item 07',
            'category' => 'Amigurumi',
            'short_description' => 'Compact handmade crochet keepsake placeholder.',
            'description' => 'Handmade crochet amigurumi placeholder crafted stitch by stitch with soft yarn.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-07.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 8,
            'slug' => 'placeholder-item-08',
            'name' => 'Placeholder Item 08',
            'category' => 'Accessories',
            'short_description' => 'Cosy handmade crochet accessory placeholder.',
            'description' => 'Handmade crochet accessory placeholder made with care and quality yarn.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-08.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 9,
            'slug' => 'placeholder-item-09',
            'name' => 'Placeholder Item 09',
            'category' => 'Accessories',
            'short_description' => 'Cosy handmade crochet accessory placeholder.',
            'description' => 'Handmade crochet accessory placeholder made with care and quality yarn.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-09.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 10,
            'slug' => 'placeholder-item-10',
            'name' => 'Placeholder Item 10',
            'category' => 'Pet Toys',
            'short_description' => 'Placeholder pet toy — available in different styles.',
            'description' => 'Handmade crochet pet toy placeholder available in different colour styles.',
            'price_pence' => 0,
            'image_path' => '/assets/placeholders/product-10.svg',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
    ];
}

function get_all_products(?PDO $pdo): array
{
    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->query(
                'SELECT id, slug, name, category, short_description, description, price_pence, image_path, stock_qty, is_featured_main, is_featured_sidebar
                 FROM products
                 WHERE is_active = 1
                 ORDER BY sort_order ASC, id ASC'
            );
            $rows = $stmt ? $stmt->fetchAll() : [];
            if (!empty($rows)) {
                return array_map(static function (array $row): array {
                    $row['id'] = (int) $row['id'];
                    $row['price_pence'] = (int) $row['price_pence'];
                    $row['stock_qty'] = (int) $row['stock_qty'];
                    $row['is_featured_main'] = (int) $row['is_featured_main'];
                    $row['is_featured_sidebar'] = (int) $row['is_featured_sidebar'];
                    return $row;
                }, $rows);
            }
        } catch (PDOException $e) {
            error_log('[LoopsByBex Catalog] Falling back to built-in catalog: ' . $e->getMessage());
        }
    }

    return default_placeholder_products();
}

function get_featured_main_products(?PDO $pdo, int $limit = 4): array
{
    $all = get_all_products($pdo);
    $featured = array_values(array_filter($all, static fn(array $p): bool => (int) ($p['is_featured_main'] ?? 0) === 1));
    if (empty($featured)) {
        $featured = $all;
    }
    return array_slice($featured, 0, $limit);
}

function get_featured_sidebar_products(?PDO $pdo, int $limit = 6): array
{
    $all = get_all_products($pdo);
    $sidebar = array_values(array_filter($all, static fn(array $p): bool => (int) ($p['is_featured_sidebar'] ?? 0) === 1));
    if (empty($sidebar)) {
        $sidebar = array_slice($all, 0, $limit);
    }
    return array_slice($sidebar, 0, $limit);
}

function get_product_by_slug(?PDO $pdo, string $slug): ?array
{
    $slug = trim($slug);
    if ($slug === '') {
        return null;
    }

    foreach (get_all_products($pdo) as $product) {
        if ($product['slug'] === $slug) {
            return $product;
        }
    }

    return null;
}

function filter_products(array $products, string $query = '', string $category = ''): array
{
    $query = mb_strtolower(trim($query));
    $category = trim($category);

    return array_values(array_filter($products, static function (array $product) use ($query, $category): bool {
        if ($category !== '' && strcasecmp($product['category'], $category) !== 0) {
            return false;
        }
        if ($query !== '') {
            $haystack = mb_strtolower($product['name'] . ' ' . $product['category'] . ' ' . $product['short_description']);
            if (!str_contains($haystack, $query)) {
                return false;
            }
        }
        return true;
    }));
}

function get_product_categories(array $products): array
{
    $categories = [];
    foreach ($products as $product) {
        $cat = (string) ($product['category'] ?? '');
        if ($cat !== '' && !in_array($cat, $categories, true)) {
            $categories[] = $cat;
        }
    }
    return $categories;
}

function get_blog_posts(): array
{
    return [
        [
            'slug' => 'choosing-cosy-yarn-for-handmade-plushies',
            'title' => 'Placeholder Post: Choosing Cosy Yarn for Handmade Plushies',
            'date' => 'October 2026',
            'category' => 'Behind the Stitches',
            'excerpt' => 'A behind-the-scenes placeholder journal entry on selecting soft, durable yarns for every handmade creation.',
            'image' => '/assets/placeholders/product-01.svg',
        ],
        [
            'slug' => 'caring-for-your-crochet-friends',
            'title' => 'Placeholder Post: How to Care for Your Crochet Keepsakes',
            'date' => 'October 2026',
            'category' => 'Care Guide',
            'excerpt' => 'Simple gentle-wash and drying tips to keep handmade crochet friends looking fresh for years to come.',
            'image' => '/assets/placeholders/product-05.svg',
        ],
        [
            'slug' => 'from-sketchbook-to-crochet-hook',
            'title' => 'Placeholder Post: From Sketchbook Idea to Finished Loop',
            'date' => 'October 2026',
            'category' => 'Studio Notes',
            'excerpt' => 'How new character ideas move from rough stitch counts on paper to finished plush companions.',
            'image' => '/assets/placeholders/product-08.svg',
        ],
    ];
}
