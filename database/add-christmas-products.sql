-- add the two new christmas products; rerunning this does not reset stock
INSERT INTO products (
    slug, name, category, short_description, description, price_pence,
    image_path, side_image_path, stock_qty, is_featured_main,
    is_featured_sidebar, is_active, sort_order
)
VALUES
    (
        'christmas-nutcracker',
        'Christmas Nutcracker',
        'Christmas',
        'Festive crochet nutcracker with a tall black-and-yellow hat and red uniform.',
        'Handmade crochet nutcracker soldier with a tall black hat, yellow trim, a red jacket, blue trousers, and a white moustache.',
        1200,
        '/assets/christmas-nutcracker.webp',
        '/assets/christmas-nutcracker-side-view.webp',
        1,
        0,
        0,
        1,
        37
    ),
    (
        'winter-bunny',
        'Winter Bunny',
        'Christmas',
        'Soft white crochet bunny in a pale-blue winter outfit with a snowflake detail.',
        'A cuddly white handmade crochet bunny wearing a pale-blue winter outfit with a snowflake motif.',
        1200,
        '/assets/winter-bunny.webp',
        '',
        1,
        0,
        0,
        1,
        38
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    short_description = VALUES(short_description),
    description = VALUES(description),
    price_pence = VALUES(price_pence),
    image_path = VALUES(image_path),
    side_image_path = VALUES(side_image_path),
    sort_order = VALUES(sort_order);
