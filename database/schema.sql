-- run in phpmyadmin on ionos db
-- prices are in pence (e.g. 700 = £7.00, 1200 = £12.00) to avoid float rounding

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS waitlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_waitlist_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Plushies',
    short_description VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    price_pence INT UNSIGNED NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NOT NULL,
    side_image_path VARCHAR(255) NOT NULL DEFAULT '',
    stock_qty INT UNSIGNED NOT NULL DEFAULT 1,
    is_featured_main TINYINT(1) NOT NULL DEFAULT 0,
    is_featured_sidebar TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(32) NOT NULL,
    idempotency_key CHAR(64) NOT NULL,
    customer_name VARCHAR(140) NOT NULL,
    customer_email VARCHAR(254) NOT NULL,
    shipping_line1 VARCHAR(180) NOT NULL,
    shipping_line2 VARCHAR(180) NOT NULL DEFAULT '',
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postcode VARCHAR(24) NOT NULL,
    shipping_country CHAR(2) NOT NULL DEFAULT 'GB',
    order_notes VARCHAR(500) NOT NULL DEFAULT '',
    subtotal_pence INT UNSIGNED NOT NULL DEFAULT 0,
    shipping_pence INT UNSIGNED NOT NULL DEFAULT 0,
    total_pence INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'GBP',
    payment_provider VARCHAR(32) NOT NULL DEFAULT 'stripe_hosted',
    payment_reference VARCHAR(191) NOT NULL DEFAULT '',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_orders_number (order_number),
    UNIQUE KEY uq_orders_idempotency (idempotency_key),
    KEY idx_orders_email (customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_slug VARCHAR(120) NOT NULL,
    product_name VARCHAR(160) NOT NULL,
    unit_price_pence INT UNSIGNED NOT NULL DEFAULT 0,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    line_total_pence INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    subject VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_key VARCHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 1,
    window_started_at INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_rate_limits_action_ip (action_key, ip_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- clear any pre-launch £0 placeholder rows if re-importing
DELETE FROM products WHERE slug LIKE 'placeholder-%';

INSERT INTO products (slug, name, category, short_description, description, price_pence, image_path, side_image_path, stock_qty, is_featured_main, is_featured_sidebar, is_active, sort_order)
VALUES
    ('love-bunny', 'Love Bunny', 'Plushies', 'Soft chunky crochet bunny holding a pastel pink heart.', 'Handmade chunky chenille crochet bunny finished with floppy pink-trimmed ears and a soft pastel heart.', 1200, '/assets/love-bunny.webp', '', 1, 1, 0, 1, 1),
    ('bee', 'Bee', 'Plushies', 'Fluffy blue and brown striped crochet bumblebee.', 'Handmade chunky crochet bee in pastel blue and chocolate brown stripes with soft cream wings and a stitched smile.', 700, '/assets/bee.webp', '/assets/bee-side-view.webp', 1, 1, 0, 1, 2),
    ('green-axolotl', 'Green Axolotl', 'Plushies', 'Mint green chunky crochet axolotl plushie.', 'Super soft mint and leaf-green handmade crochet axolotl with frilly side gills.', 800, '/assets/green-axolotl.webp', '/assets/green-axolotl-side-view.webp', 1, 1, 0, 1, 3),
    ('dinosaur', 'Dinosaur', 'Plushies', 'Pastel variegated long-neck crochet dinosaur.', 'Handmade long-neck dinosaur crocheted in pastel blue, lilac, pink, and white yarn.', 1000, '/assets/dinosaur.webp', '/assets/dinosaur-side-view.webp', 1, 1, 0, 1, 4),
    ('toad-style', 'Toad Style', 'Plushies', 'Large handmade mushroom-cap character plushie.', 'Large detailed handmade crochet character plushie with a spotted red-and-white mushroom cap and blue waistcoat.', 2500, '/assets/toad-style.webp', '/assets/toad-style-side-view.webp', 1, 0, 1, 1, 5),
    ('flounder-style', 'Flounder Style', 'Plushies', 'Pastel yellow and sky-blue striped tropical fish plushie.', 'Cuddly handmade crochet tropical fish in soft lemon yellow and baby-blue stripes with rosy cheeks.', 1000, '/assets/flounder-style.webp', '/assets/flounder-style-side-view.webp', 1, 0, 1, 1, 6),
    ('star', 'Star', 'Plushies', 'Soft pastel yellow smiling crochet star cushion plush.', 'Plump handmade pastel yellow crochet star with rosy pink cheeks and a stitched happy face.', 1000, '/assets/star.webp', '', 1, 0, 1, 1, 7),
    ('tall-penguin', 'Tall Penguin', 'Plushies', 'Cuddly standing baby-blue and white crochet penguin.', 'Standing handmade chunky crochet penguin in baby blue and cream with bright orange feet and beak.', 800, '/assets/tall-penguin.webp', '', 1, 0, 1, 1, 8),
    ('mushroom', 'Mushroom', 'Plushies', 'Terracotta spotted toadstool crochet plushie.', 'Handmade chunky crochet toadstool mushroom with a spotted rust-orange cap and smiling cream stem.', 700, '/assets/mushroom.webp', '', 1, 0, 1, 1, 9),
    ('bright-pink-christmas-loaf-cat', 'Bright Pink Christmas Loaf Cat', 'Christmas', 'Festive pink-and-white crochet loaf cat wearing a sparkly Santa hat.', 'Handmade chunky crochet loaf cat in cream and candy pink patches, topped with a sparkly red-and-white Santa hat.', 600, '/assets/bright-pink-christmas-loaf-cat.webp', '/assets/bright-pink-christmas-loaf-cat-side-view.webp', 1, 0, 1, 1, 10),
    ('mini-love-bunny', 'Mini Love Bunny', 'Plushies', 'Pocket-sized crochet bunny hugging a pastel heart.', 'Mini handmade chunky crochet bunny in soft white and blush pink with a little crochet heart.', 500, '/assets/mini-love-bunny.webp', '', 1, 0, 0, 1, 11),
    ('octopus', 'Octopus', 'Plushies', 'Sage green chunky crochet mini octopus.', 'Cute handmade sage green crochet octopus with curly bobble tentacles and a stitched smile.', 500, '/assets/octopus.webp', '', 1, 0, 0, 1, 12),
    ('blue-penguin', 'Blue Penguin', 'Plushies', 'Chubby pastel blue crochet penguin.', 'Handmade round crochet penguin wearing a cosy pastel blue jumper.', 500, '/assets/blue-penguin.webp', '', 1, 0, 0, 1, 13),
    ('green-penguin', 'Green Penguin', 'Plushies', 'Chubby sage green crochet penguin.', 'Handmade round crochet penguin finished in soft sage and forest green yarn.', 500, '/assets/green-penguin.webp', '', 1, 0, 0, 1, 14),
    ('orange-penguin', 'Orange Penguin', 'Plushies', 'Chubby burnt-orange crochet penguin.', 'Handmade round crochet penguin crafted in warm terracotta orange yarn.', 500, '/assets/orange-penguin.webp', '', 1, 0, 0, 1, 15),
    ('purple-penguin', 'Purple Penguin', 'Plushies', 'Chubby lilac and violet crochet penguin.', 'Handmade round crochet penguin in two-tone lavender and deep purple yarn.', 500, '/assets/purple-penguin.webp', '', 1, 0, 0, 1, 16),
    ('small-penguin', 'Small Penguin', 'Plushies', 'Mini bright-blue pocket crochet penguin.', 'Desk-sized mini handmade crochet penguin in bright ocean blue and white yarn.', 300, '/assets/small-penguin.webp', '', 1, 0, 0, 1, 17),
    ('small-black-and-grey-penguin', 'Small Black and Grey Penguin', 'Plushies', 'Mini classic black and grey crochet penguin.', 'Classic mini handmade crochet penguin in charcoal, soft grey, and white with yellow feet.', 300, '/assets/small-black-and-grey-penguin.webp', '', 1, 0, 0, 1, 18),
    ('small-multicoloured-mushroom', 'Small Multicoloured Mushroom', 'Plushies', 'Mini confetti-cap smiling crochet mushroom.', 'Pocket-sized handmade crochet mushroom with a speckled rainbow pastel cap.', 400, '/assets/small-multicoloured-mushroom.webp', '', 1, 0, 0, 1, 19),
    ('small-yellow-mushroom', 'Small Yellow Mushroom', 'Plushies', 'Mini lemon-cap smiling crochet mushroom.', 'Pocket-sized handmade crochet mushroom with a soft butter-yellow cap and happy stitched face.', 400, '/assets/small-yellow-mushroom.webp', '', 1, 0, 0, 1, 20),
    ('christmas-penguin', 'Christmas Penguin', 'Christmas', 'Festive red-jumper crochet penguin.', 'Handmade round festive crochet penguin dressed in cosy Christmas red.', 500, '/assets/christmas-penguin.webp', '', 1, 0, 0, 1, 21),
    ('christmas-tree', 'Christmas Tree', 'Christmas', 'Mini crochet pine tree topped with a sparkly gold pom-pom.', 'Festive handmade crochet Christmas tree in textured pine green with a cute face and glittery gold topper.', 500, '/assets/christmas-tree.webp', '', 1, 0, 0, 1, 22),
    ('small-green-christmas-loaf-cat', 'Small Green Christmas Loaf Cat', 'Christmas', 'Mini green-and-white festive crochet loaf cat with Santa hat.', 'Small handmade crochet loaf cat in cream and sage green patches wearing a sparkly Santa hat.', 400, '/assets/small-green-christmas-loaf-cat.webp', '/assets/small-green-christmas-loaf-cat-side-view.webp', 1, 0, 0, 1, 23),
    ('small-pink-christmas-loaf-cat', 'Small Pink Christmas Loaf Cat', 'Christmas', 'Mini blush-pink festive crochet loaf cat with Santa hat.', 'Small handmade crochet loaf cat in cream and pastel pink patches finished with a sparkly Santa hat.', 400, '/assets/small-pink-christmas-loaf-cat.webp', '/assets/small-pink-christmas-loaf-cat-side-view.webp', 1, 0, 0, 1, 24),
    ('star-keyring', 'Star Keyring', 'Accessories', 'Mini smiling yellow crochet star bag charm & keyring.', 'Handmade pastel yellow crochet star keyring with a metal split-ring clasp — lovely for keys, backpacks, or zip pulls.', 250, '/assets/star-keyring.webp', '', 1, 0, 0, 1, 25),
    ('glitter-green-scrunchie', 'Glitter Green Scrunchie', 'Accessories', 'Soft mint crochet hair scrunchie with subtle sparkle thread.', 'Handmade ruffled crochet hair scrunchie in mint green yarn woven with delicate glitter shimmer.', 150, '/assets/glitter-green-scrunchie.webp', '', 1, 0, 0, 1, 26),
    ('multicoloured-scrunchie', 'Multicoloured Scrunchie', 'Accessories', 'Pastel rainbow crochet hair scrunchie.', 'Handmade pastel crochet scrunchie blending soft lilac, pink, lemon, and mint yarn.', 150, '/assets/multicoloured-scrunchie.webp', '', 1, 0, 0, 1, 27),
    ('pink-and-purple-scrunchie', 'Pink and Purple Scrunchie', 'Accessories', 'Two-tone berry pink and lavender crochet scrunchie.', 'Soft chunky handmade crochet hair scrunchie in rose pink and lavender purple.', 150, '/assets/pink-and-purple-scrunchie.webp', '', 1, 0, 0, 1, 28),
    ('pink-scrunchie', 'Pink Scrunchie', 'Accessories', 'Fluffy pastel pink ruffled crochet scrunchie.', 'Super soft handmade ruffled crochet hair scrunchie in candyfloss pink.', 150, '/assets/pink-scrunchie.webp', '', 1, 0, 0, 1, 29),
    ('harry-potter-style-coffee-holder', 'Harry Potter Style Coffee Holder', 'Accessories', 'Reusable house-colours crochet takeaway cup sleeve.', 'Handmade reusable crochet coffee cup cosy striped in red, green, yellow, and blue with a stitched initial detail.', 100, '/assets/harry-potter-style-coffee-holder.webp', '', 1, 0, 0, 1, 30),
    ('elephant-dog-bandana', 'Elephant Dog Bandana', 'Pets', 'Handmade slip-on pink elephant print dog bandana.', 'Handmade over-the-collar cotton dog bandana in dusty rose with an elephant family print.', 450, '/assets/elephant-dog-bandana.webp', '', 1, 0, 0, 1, 31),
    ('emoji-dog-bandana', 'Emoji Dog Bandana', 'Pets', 'Handmade slip-on red emoji print dog bandana.', 'Bright handmade over-the-collar cotton dog bandana with a cheerful emoji pattern.', 450, '/assets/emoji-dog-bandana.webp', '', 1, 0, 0, 1, 32),
    ('jungle-blue-dog-bandana', 'Jungle Blue Dog Bandana', 'Pets', 'Handmade slip-on mint blue bear & panda dog bandana.', 'Handmade over-the-collar cotton dog bandana in soft mint blue with cute bear illustrations.', 450, '/assets/jungle-blue-dog-bandana.webp', '', 1, 0, 0, 1, 33),
    ('jungle-pink-dog-bandana', 'Jungle Pink Dog Bandana', 'Pets', 'Handmade slip-on blush pink bear & panda dog bandana.', 'Handmade over-the-collar cotton dog bandana in dusky pink with cute bear illustrations.', 450, '/assets/jungle-pink-dog-bandana.webp', '', 1, 0, 0, 1, 34),
    ('swan-dog-bandana', 'Swan Dog Bandana', 'Pets', 'Handmade slip-on sunshine yellow swan print dog bandana.', 'Handmade over-the-collar cotton dog bandana in bright yellow patterned with white swans.', 450, '/assets/swan-dog-bandana.webp', '', 1, 0, 0, 1, 35),
    ('cat-nip-filled-toy', 'Cat Nip Filled Toy', 'Pets', 'Handmade yellow crochet catnip mouse/fish chaser toy.', 'Bright yellow handmade crochet cat toy stuffed with aromatic catnip and a dangling tail ribbon for batting and play.', 250, '/assets/cat-nip-filled-toy.webp', '', 1, 0, 0, 1, 36)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    short_description = VALUES(short_description),
    description = VALUES(description),
    price_pence = VALUES(price_pence),
    image_path = VALUES(image_path),
    side_image_path = VALUES(side_image_path),
    is_featured_main = VALUES(is_featured_main),
    is_featured_sidebar = VALUES(is_featured_sidebar),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order);
