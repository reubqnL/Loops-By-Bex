-- run in phpmyadmin on ionos db
-- prices are in pence (0 = £0.00) to avoid float rounding

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
    stock_qty INT UNSIGNED NOT NULL DEFAULT 10,
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
    payment_provider VARCHAR(32) NOT NULL DEFAULT 'placeholder_free',
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

-- seed £0 placeholder items for testing layout before real stock is added
INSERT INTO products (slug, name, category, short_description, description, price_pence, image_path, stock_qty, is_featured_main, is_featured_sidebar, is_active, sort_order)
VALUES
    ('placeholder-plush-01', 'Placeholder Plush 01', 'Plushies', 'Handmade crochet placeholder item.', 'Soft handmade crochet plush placeholder. Product imagery, final title, yarn details, and dimensions coming soon.', 0, '/assets/placeholders/product-01.svg', 10, 1, 0, 1, 1),
    ('placeholder-plush-02', 'Placeholder Plush 02', 'Plushies', 'Handmade crochet placeholder item.', 'Soft handmade crochet plush placeholder. Product imagery, final title, yarn details, and dimensions coming soon.', 0, '/assets/placeholders/product-02.svg', 10, 1, 0, 1, 2),
    ('placeholder-plush-03', 'Placeholder Plush 03', 'Plushies', 'Handmade crochet placeholder item.', 'Soft handmade crochet plush placeholder. Product imagery, final title, yarn details, and dimensions coming soon.', 0, '/assets/placeholders/product-03.svg', 10, 1, 0, 1, 3),
    ('placeholder-plush-04', 'Placeholder Plush 04', 'Plushies', 'Handmade crochet placeholder item.', 'Soft handmade crochet plush placeholder. Product imagery, final title, yarn details, and dimensions coming soon.', 0, '/assets/placeholders/product-04.svg', 10, 1, 0, 1, 4),
    ('placeholder-item-05', 'Placeholder Item 05', 'Amigurumi', 'Compact handmade crochet keepsake placeholder.', 'Handmade crochet amigurumi placeholder crafted with soft yarn and safety stitching.', 0, '/assets/placeholders/product-05.svg', 10, 0, 1, 1, 5),
    ('placeholder-item-06', 'Placeholder Item 06', 'Amigurumi', 'Compact handmade crochet keepsake placeholder.', 'Handmade crochet amigurumi placeholder crafted with soft yarn and safety stitching.', 0, '/assets/placeholders/product-06.svg', 10, 0, 1, 1, 6),
    ('placeholder-item-07', 'Placeholder Item 07', 'Amigurumi', 'Compact handmade crochet keepsake placeholder.', 'Handmade crochet amigurumi placeholder crafted with soft yarn and safety stitching.', 0, '/assets/placeholders/product-07.svg', 10, 0, 1, 1, 7),
    ('placeholder-item-08', 'Placeholder Item 08', 'Accessories', 'Cosy handmade crochet accessory placeholder.', 'Handmade crochet accessory placeholder made stitch by stitch with quality yarn.', 0, '/assets/placeholders/product-08.svg', 10, 0, 1, 1, 8),
    ('placeholder-item-09', 'Placeholder Item 09', 'Accessories', 'Cosy handmade crochet accessory placeholder.', 'Handmade crochet accessory placeholder made stitch by stitch with quality yarn.', 0, '/assets/placeholders/product-09.svg', 10, 0, 1, 1, 9),
    ('placeholder-item-10', 'Placeholder Item 10', 'Pet Toys', 'Handmade crochet pet toy placeholder — multiple styles.', 'Handmade crochet pet toy placeholder available in multiple colour styles.', 0, '/assets/placeholders/product-10.svg', 10, 0, 1, 1, 10)
ON DUPLICATE KEY UPDATE
    price_pence = VALUES(price_pence),
    image_path = VALUES(image_path);
