<?php
// product list fallback when mysql products table isnt populated yet
function default_placeholder_products(): array
{
    return [
        [
            'id' => 1,
            'slug' => 'love-bunny',
            'name' => 'Love Bunny',
            'category' => 'Plushies',
            'short_description' => 'Soft chunky crochet bunny holding a pastel pink heart.',
            'description' => 'Handmade chunky chenille crochet bunny finished with floppy pink-trimmed ears and a soft pastel heart.',
            'price_pence' => 1200,
            'image_path' => '/assets/love-bunny.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 2,
            'slug' => 'bee',
            'name' => 'Bee',
            'category' => 'Plushies',
            'short_description' => 'Fluffy blue and brown striped crochet bumblebee.',
            'description' => 'Handmade chunky crochet bee in pastel blue and chocolate brown stripes with soft cream wings and a stitched smile.',
            'price_pence' => 700,
            'image_path' => '/assets/bee.webp',
            'side_image_path' => '/assets/bee-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 3,
            'slug' => 'green-axolotl',
            'name' => 'Green Axolotl',
            'category' => 'Plushies',
            'short_description' => 'Mint green chunky crochet axolotl plushie.',
            'description' => 'Super soft mint and leaf-green handmade crochet axolotl with frilly side gills.',
            'price_pence' => 800,
            'image_path' => '/assets/green-axolotl.webp',
            'side_image_path' => '/assets/green-axolotl-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 4,
            'slug' => 'dinosaur',
            'name' => 'Dinosaur',
            'category' => 'Plushies',
            'short_description' => 'Pastel variegated long-neck crochet dinosaur.',
            'description' => 'Handmade long-neck dinosaur crocheted in pastel blue, lilac, pink, and white yarn.',
            'price_pence' => 1000,
            'image_path' => '/assets/dinosaur.webp',
            'side_image_path' => '/assets/dinosaur-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 1,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 5,
            'slug' => 'toad-style',
            'name' => 'Toad Style',
            'category' => 'Plushies',
            'short_description' => 'Large handmade mushroom-cap character plushie.',
            'description' => 'Large detailed handmade crochet character plushie with a spotted red-and-white mushroom cap and blue waistcoat.',
            'price_pence' => 2500,
            'image_path' => '/assets/toad-style.webp',
            'side_image_path' => '/assets/toad-style-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 6,
            'slug' => 'flounder-style',
            'name' => 'Flounder Style',
            'category' => 'Plushies',
            'short_description' => 'Pastel yellow and sky-blue striped tropical fish plushie.',
            'description' => 'Cuddly handmade crochet tropical fish in soft lemon yellow and baby-blue stripes with rosy cheeks.',
            'price_pence' => 1000,
            'image_path' => '/assets/flounder-style.webp',
            'side_image_path' => '/assets/flounder-style-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 7,
            'slug' => 'star',
            'name' => 'Star',
            'category' => 'Plushies',
            'short_description' => 'Soft pastel yellow smiling crochet star cushion plush.',
            'description' => 'Plump handmade pastel yellow crochet star with rosy pink cheeks and a stitched happy face.',
            'price_pence' => 1000,
            'image_path' => '/assets/star.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 8,
            'slug' => 'tall-penguin',
            'name' => 'Tall Penguin',
            'category' => 'Plushies',
            'short_description' => 'Cuddly standing baby-blue and white crochet penguin.',
            'description' => 'Standing handmade chunky crochet penguin in baby blue and cream with bright orange feet and beak.',
            'price_pence' => 800,
            'image_path' => '/assets/tall-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 9,
            'slug' => 'mushroom',
            'name' => 'Mushroom',
            'category' => 'Plushies',
            'short_description' => 'Terracotta spotted toadstool crochet plushie.',
            'description' => 'Handmade chunky crochet toadstool mushroom with a spotted rust-orange cap and smiling cream stem.',
            'price_pence' => 700,
            'image_path' => '/assets/mushroom.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 10,
            'slug' => 'bright-pink-christmas-loaf-cat',
            'name' => 'Bright Pink Christmas Loaf Cat',
            'category' => 'Christmas',
            'short_description' => 'Festive pink-and-white crochet loaf cat wearing a sparkly Santa hat.',
            'description' => 'Handmade chunky crochet loaf cat in cream and candy pink patches, topped with a sparkly red-and-white Santa hat.',
            'price_pence' => 600,
            'image_path' => '/assets/bright-pink-christmas-loaf-cat.webp',
            'side_image_path' => '/assets/bright-pink-christmas-loaf-cat-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 1,
        ],
        [
            'id' => 11,
            'slug' => 'mini-love-bunny',
            'name' => 'Mini Love Bunny',
            'category' => 'Plushies',
            'short_description' => 'Pocket-sized crochet bunny hugging a pastel heart.',
            'description' => 'Mini handmade chunky crochet bunny in soft white and blush pink with a little crochet heart.',
            'price_pence' => 500,
            'image_path' => '/assets/mini-love-bunny.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 12,
            'slug' => 'octopus',
            'name' => 'Octopus',
            'category' => 'Plushies',
            'short_description' => 'Sage green chunky crochet mini octopus.',
            'description' => 'Cute handmade sage green crochet octopus with curly bobble tentacles and a stitched smile.',
            'price_pence' => 500,
            'image_path' => '/assets/octopus.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 13,
            'slug' => 'blue-penguin',
            'name' => 'Blue Penguin',
            'category' => 'Plushies',
            'short_description' => 'Chubby pastel blue crochet penguin.',
            'description' => 'Handmade round crochet penguin wearing a cosy pastel blue jumper.',
            'price_pence' => 500,
            'image_path' => '/assets/blue-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 14,
            'slug' => 'green-penguin',
            'name' => 'Green Penguin',
            'category' => 'Plushies',
            'short_description' => 'Chubby sage green crochet penguin.',
            'description' => 'Handmade round crochet penguin finished in soft sage and forest green yarn.',
            'price_pence' => 500,
            'image_path' => '/assets/green-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 15,
            'slug' => 'orange-penguin',
            'name' => 'Orange Penguin',
            'category' => 'Plushies',
            'short_description' => 'Chubby burnt-orange crochet penguin.',
            'description' => 'Handmade round crochet penguin crafted in warm terracotta orange yarn.',
            'price_pence' => 500,
            'image_path' => '/assets/orange-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 16,
            'slug' => 'purple-penguin',
            'name' => 'Purple Penguin',
            'category' => 'Plushies',
            'short_description' => 'Chubby lilac and violet crochet penguin.',
            'description' => 'Handmade round crochet penguin in two-tone lavender and deep purple yarn.',
            'price_pence' => 500,
            'image_path' => '/assets/purple-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 17,
            'slug' => 'small-penguin',
            'name' => 'Small Penguin',
            'category' => 'Plushies',
            'short_description' => 'Mini bright-blue pocket crochet penguin.',
            'description' => 'Desk-sized mini handmade crochet penguin in bright ocean blue and white yarn.',
            'price_pence' => 300,
            'image_path' => '/assets/small-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 18,
            'slug' => 'small-black-and-grey-penguin',
            'name' => 'Small Black and Grey Penguin',
            'category' => 'Plushies',
            'short_description' => 'Mini classic black and grey crochet penguin.',
            'description' => 'Classic mini handmade crochet penguin in charcoal, soft grey, and white with yellow feet.',
            'price_pence' => 300,
            'image_path' => '/assets/small-black-and-grey-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 19,
            'slug' => 'small-multicoloured-mushroom',
            'name' => 'Small Multicoloured Mushroom',
            'category' => 'Plushies',
            'short_description' => 'Mini confetti-cap smiling crochet mushroom.',
            'description' => 'Pocket-sized handmade crochet mushroom with a speckled rainbow pastel cap.',
            'price_pence' => 400,
            'image_path' => '/assets/small-multicoloured-mushroom.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 20,
            'slug' => 'small-yellow-mushroom',
            'name' => 'Small Yellow Mushroom',
            'category' => 'Plushies',
            'short_description' => 'Mini lemon-cap smiling crochet mushroom.',
            'description' => 'Pocket-sized handmade crochet mushroom with a soft butter-yellow cap and happy stitched face.',
            'price_pence' => 400,
            'image_path' => '/assets/small-yellow-mushroom.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 21,
            'slug' => 'christmas-penguin',
            'name' => 'Christmas Penguin',
            'category' => 'Christmas',
            'short_description' => 'Festive red-jumper crochet penguin.',
            'description' => 'Handmade round festive crochet penguin dressed in cosy Christmas red.',
            'price_pence' => 500,
            'image_path' => '/assets/christmas-penguin.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 22,
            'slug' => 'christmas-tree',
            'name' => 'Christmas Tree',
            'category' => 'Christmas',
            'short_description' => 'Mini crochet pine tree topped with a sparkly gold pom-pom.',
            'description' => 'Festive handmade crochet Christmas tree in textured pine green with a cute face and glittery gold topper.',
            'price_pence' => 500,
            'image_path' => '/assets/christmas-tree.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 23,
            'slug' => 'small-green-christmas-loaf-cat',
            'name' => 'Small Green Christmas Loaf Cat',
            'category' => 'Christmas',
            'short_description' => 'Mini green-and-white festive crochet loaf cat with Santa hat.',
            'description' => 'Small handmade crochet loaf cat in cream and sage green patches wearing a sparkly Santa hat.',
            'price_pence' => 400,
            'image_path' => '/assets/small-green-christmas-loaf-cat.webp',
            'side_image_path' => '/assets/small-green-christmas-loaf-cat-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 24,
            'slug' => 'small-pink-christmas-loaf-cat',
            'name' => 'Small Pink Christmas Loaf Cat',
            'category' => 'Christmas',
            'short_description' => 'Mini blush-pink festive crochet loaf cat with Santa hat.',
            'description' => 'Small handmade crochet loaf cat in cream and pastel pink patches finished with a sparkly Santa hat.',
            'price_pence' => 400,
            'image_path' => '/assets/small-pink-christmas-loaf-cat.webp',
            'side_image_path' => '/assets/small-pink-christmas-loaf-cat-side-view.webp',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 25,
            'slug' => 'star-keyring',
            'name' => 'Star Keyring',
            'category' => 'Accessories',
            'short_description' => 'Mini smiling yellow crochet star bag charm & keyring.',
            'description' => 'Handmade pastel yellow crochet star keyring with a metal split-ring clasp — lovely for keys, backpacks, or zip pulls.',
            'price_pence' => 250,
            'image_path' => '/assets/star-keyring.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 26,
            'slug' => 'glitter-green-scrunchie',
            'name' => 'Glitter Green Scrunchie',
            'category' => 'Accessories',
            'short_description' => 'Soft mint crochet hair scrunchie with subtle sparkle thread.',
            'description' => 'Handmade ruffled crochet hair scrunchie in mint green yarn woven with delicate glitter shimmer.',
            'price_pence' => 150,
            'image_path' => '/assets/glitter-green-scrunchie.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 27,
            'slug' => 'multicoloured-scrunchie',
            'name' => 'Multicoloured Scrunchie',
            'category' => 'Accessories',
            'short_description' => 'Pastel rainbow crochet hair scrunchie.',
            'description' => 'Handmade pastel crochet scrunchie blending soft lilac, pink, lemon, and mint yarn.',
            'price_pence' => 150,
            'image_path' => '/assets/multicoloured-scrunchie.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 28,
            'slug' => 'pink-and-purple-scrunchie',
            'name' => 'Pink and Purple Scrunchie',
            'category' => 'Accessories',
            'short_description' => 'Two-tone berry pink and lavender crochet scrunchie.',
            'description' => 'Soft chunky handmade crochet hair scrunchie in rose pink and lavender purple.',
            'price_pence' => 150,
            'image_path' => '/assets/pink-and-purple-scrunchie.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 29,
            'slug' => 'pink-scrunchie',
            'name' => 'Pink Scrunchie',
            'category' => 'Accessories',
            'short_description' => 'Fluffy pastel pink ruffled crochet scrunchie.',
            'description' => 'Super soft handmade ruffled crochet hair scrunchie in candyfloss pink.',
            'price_pence' => 150,
            'image_path' => '/assets/pink-scrunchie.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 30,
            'slug' => 'harry-potter-style-coffee-holder',
            'name' => 'Harry Potter Style Coffee Holder',
            'category' => 'Accessories',
            'short_description' => 'Reusable house-colours crochet takeaway cup sleeve.',
            'description' => 'Handmade reusable crochet coffee cup cosy striped in red, green, yellow, and blue with a stitched initial detail.',
            'price_pence' => 100,
            'image_path' => '/assets/harry-potter-style-coffee-holder.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 31,
            'slug' => 'elephant-dog-bandana',
            'name' => 'Elephant Dog Bandana',
            'category' => 'Pets',
            'short_description' => 'Handmade slip-on pink elephant print dog bandana.',
            'description' => 'Handmade over-the-collar cotton dog bandana in dusty rose with an elephant family print.',
            'price_pence' => 450,
            'image_path' => '/assets/elephant-dog-bandana.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 32,
            'slug' => 'emoji-dog-bandana',
            'name' => 'Emoji Dog Bandana',
            'category' => 'Pets',
            'short_description' => 'Handmade slip-on red emoji print dog bandana.',
            'description' => 'Bright handmade over-the-collar cotton dog bandana with a cheerful emoji pattern.',
            'price_pence' => 450,
            'image_path' => '/assets/emoji-dog-bandana.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 33,
            'slug' => 'jungle-blue-dog-bandana',
            'name' => 'Jungle Blue Dog Bandana',
            'category' => 'Pets',
            'short_description' => 'Handmade slip-on mint blue bear & panda dog bandana.',
            'description' => 'Handmade over-the-collar cotton dog bandana in soft mint blue with cute bear illustrations.',
            'price_pence' => 450,
            'image_path' => '/assets/jungle-blue-dog-bandana.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 34,
            'slug' => 'jungle-pink-dog-bandana',
            'name' => 'Jungle Pink Dog Bandana',
            'category' => 'Pets',
            'short_description' => 'Handmade slip-on blush pink bear & panda dog bandana.',
            'description' => 'Handmade over-the-collar cotton dog bandana in dusky pink with cute bear illustrations.',
            'price_pence' => 450,
            'image_path' => '/assets/jungle-pink-dog-bandana.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 35,
            'slug' => 'swan-dog-bandana',
            'name' => 'Swan Dog Bandana',
            'category' => 'Pets',
            'short_description' => 'Handmade slip-on sunshine yellow swan print dog bandana.',
            'description' => 'Handmade over-the-collar cotton dog bandana in bright yellow patterned with white swans.',
            'price_pence' => 450,
            'image_path' => '/assets/swan-dog-bandana.webp',
            'side_image_path' => '',
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
        [
            'id' => 36,
            'slug' => 'cat-nip-filled-toy',
            'name' => 'Cat Nip Filled Toy',
            'category' => 'Pets',
            'short_description' => 'Handmade crochet catnip toy — available in different colours and styles.',
            'description' => 'Handmade crochet cat toy stuffed with aromatic catnip and a dangling tail ribbon for batting and play. Available in different colours and styles — select your preferred colour below.',
            'price_pence' => 250,
            'image_path' => '/assets/cat-nip-filled-toy.webp',
            'side_image_path' => '',
            'color_options' => ['Blue', 'Green', 'Indigo', 'Orange', 'Red', 'Violet', 'Yellow'],
            'stock_qty' => 10,
            'is_featured_main' => 0,
            'is_featured_sidebar' => 0,
        ],
    ];
}

function get_all_products(?PDO $pdo): array
{
    $defaultsBySlug = [];
    foreach (default_placeholder_products() as $item) {
        $defaultsBySlug[$item['slug']] = $item;
    }

    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->query(
                "SELECT * FROM products
                 WHERE is_active = 1 AND slug NOT LIKE 'placeholder-%'
                 ORDER BY sort_order ASC, id ASC"
            );
            $rows = $stmt ? $stmt->fetchAll() : [];
            if (!empty($rows)) {
                return array_map(static function (array $row) use ($defaultsBySlug): array {
                    $slug = (string) ($row['slug'] ?? '');
                    $fallbackSide = $defaultsBySlug[$slug]['side_image_path'] ?? '';
                    $row['id'] = (int) $row['id'];
                    $row['price_pence'] = (int) $row['price_pence'];
                    $row['stock_qty'] = (int) $row['stock_qty'];
                    $row['is_featured_main'] = (int) $row['is_featured_main'];
                    $row['is_featured_sidebar'] = (int) $row['is_featured_sidebar'];
                    $row['side_image_path'] = trim((string) ($row['side_image_path'] ?? '')) !== ''
                        ? (string) $row['side_image_path']
                        : $fallbackSide;
                    $row['color_options'] = $defaultsBySlug[$slug]['color_options'] ?? [];
                    return $row;
                }, $rows);
            }
        } catch (PDOException $e) {
            error_log('catalog query fallback: ' . $e->getMessage());
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
