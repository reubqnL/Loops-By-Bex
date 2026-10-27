<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /cart', true, 302);
    exit();
}

$rawReturn = trim((string) ($_POST['return_to'] ?? '/cart'));

// make sure redirect stays on our own site
$returnTo = '/cart';
if ($rawReturn !== '' && str_starts_with($rawReturn, '/') && !str_starts_with($rawReturn, '//') && !str_contains($rawReturn, "\r") && !str_contains($rawReturn, "\n")) {
    $returnTo = $rawReturn;
}

verify_csrf_or_redirect($returnTo);

$action = trim((string) ($_POST['action'] ?? ''));
$slug = trim((string) ($_POST['slug'] ?? ''));
$quantity = (int) ($_POST['quantity'] ?? 1);

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
