-- normalize one-off stock and enable manual full-refund tracking for overlapping checkouts
-- run in phpmyadmin on the live mysql database after deploying the matching site files

SET NAMES utf8mb4;

ALTER TABLE orders
    MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'refund_pending')
    NOT NULL DEFAULT 'pending';

ALTER TABLE products
    MODIFY stock_qty INT UNSIGNED NOT NULL DEFAULT 1;

UPDATE products
SET stock_qty = CASE WHEN stock_qty > 0 THEN 1 ELSE 0 END;

UPDATE products
SET stock_qty = 0
WHERE slug = 'octopus';
