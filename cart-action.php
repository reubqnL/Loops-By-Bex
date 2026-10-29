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
$cartKey = trim((string) ($_POST['cart_key'] ?? ''));
if ($cartKey === '') {
    $cartKey = $slug;
}
$color = trim((string) ($_POST['color'] ?? ''));
$quantity = (int) ($_POST['quantity'] ?? 1);

switch ($action) {
    case 'add':
        $product = get_product_by_slug($pdo, $slug);
        if ($product !== null) {
            $res = cart_add_item($pdo, $slug, max(1, $quantity), $color);
            if ($res['ok']) {
                set_flash('success', sprintf('Added "%s" (%s) to your cart.', $res['display_name'], format_gbp((int) $product['price_pence'])));
            } else {
                set_flash('error', 'Sorry, that item is currently out of stock.');
            }
        } else {
            set_flash('error', 'Sorry, that item could not be added to your cart.');
        }
        break;

    case 'update':
        $res = cart_set_quantity($pdo, $cartKey, $quantity, $color);
        if ($res['removed']) {
            set_flash('info', 'Item removed from your cart.');
        } elseif ($res['clamped']) {
            set_flash('info', sprintf('Maximum available quantity is %d — cart updated to %d.', $res['max'], $res['qty']));
        } else {
            set_flash('success', 'Your shopping cart has been updated.');
        }
        break;

    case 'remove':
        cart_set_quantity($pdo, $cartKey, 0);
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
