-- keep the product slug unchanged so existing links continue to work
UPDATE products
SET name = 'Heart Bunny'
WHERE slug = 'love-bunny';
