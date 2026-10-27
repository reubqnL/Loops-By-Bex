<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /cart', true, 302);
    exit();
}

$action = trim((string) ($_POST['action'] ?? ''));
$slug = trim((string) ($_POST['slug'] ?? ''));
$quantity = (int) ($_POST['quantity'] ?? 1);
$rawReturn = trim((string) ($_POST['return_to'] ?? '/cart'));

// Only allow safe local relative paths starting with a single "/"
$returnTo = '/cart';
if ($rawReturn !== '' && str_starts_with($rawReturn, '/') && !str_starts_with($rawReturn, '//') && !str_contains($rawReturn, "\r") && !str_contains($rawReturn, "\n")) {
    $returnTo = $rawReturn;
}

switch ($action) {
    case 'add':
        $product = get_product_by_slug($pdo, $slug);
        if ($product !== null && cart_add_item($pdo, $slug, max(1, $quantity))) {
            set_flash('success', sprintf('Added "%s" (%s) to your cart.', $product['name'], format_gbp((int) $product['price_pence'])));
        } else {
            set_flash('error', 'Sorry, that item could not be added to your cart.');
        }
        break;

    case 'update':
        cart_set_quantity($pdo, $slug, $quantity);
        set_flash('info', 'Your shopping cart has been updated.');
        break;

    case 'remove':
        cart_set_quantity($pdo, $slug, 0);
        set_flash('info', 'Item removed from your cart.');
        break;

    case 'clear':
        cart_clear();
        set_flash('info', 'Your shopping cart has been cleared.');
        break;

    default:
        set_flash('error', 'Invalid cart action.');
        break;
}

header('Location: ' . $returnTo, true, 303);
exit();
