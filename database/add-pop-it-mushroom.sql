-- add the Pop it Mushroom accessory; rerunning this does not reset stock
INSERT INTO products (
    slug, name, category, short_description, description, price_pence,
    image_path, side_image_path, stock_qty, is_featured_main,
    is_featured_sidebar, is_active, sort_order
)
VALUES (
    'pop-it-mushroom',
    'Pop it Mushroom',
    'Accessories',
    'Handmade crochet mushroom keyring charm.',
    'A cute handmade crochet mushroom charm finished with a metal keyring loop.',
    400,
    '/assets/pop-it-mushroom.webp',
    '',
    1,
    0,
    0,
    1,
    39
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
