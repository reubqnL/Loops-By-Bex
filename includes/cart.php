<?php
declare(strict_types=1);

const CART_MAX_QTY_PER_ITEM = 10;

function get_raw_cart(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function get_cart_item_count(): int
{
    $total = 0;
    foreach (get_raw_cart() as $qty) {
        $qty = (int) $qty;
        if ($qty > 0) {
            $total += $qty;
        }
    }
    return $total;
}

function cart_add_item(?PDO $pdo, string $slug, int $quantity = 1): bool
{
    $product = get_product_by_slug($pdo, $slug);
    if ($product === null || (int) $product['stock_qty'] <= 0) {
        return false;
    }

    $cart = get_raw_cart();
    $current = (int) ($cart[$slug] ?? 0);
    $maxAllowed = min(CART_MAX_QTY_PER_ITEM, (int) $product['stock_qty']);
    $cart[$slug] = max(1, min($maxAllowed, $current + max(1, $quantity)));
    $_SESSION['cart'] = $cart;
    return true;
}

function cart_set_quantity(?PDO $pdo, string $slug, int $quantity): void
{
    $cart = get_raw_cart();
    if ($quantity <= 0) {
        unset($cart[$slug]);
        $_SESSION['cart'] = $cart;
        return;
    }

    $product = get_product_by_slug($pdo, $slug);
    if ($product === null || (int) $product['stock_qty'] <= 0) {
        unset($cart[$slug]);
        $_SESSION['cart'] = $cart;
        return;
    }

    $maxAllowed = min(CART_MAX_QTY_PER_ITEM, (int) $product['stock_qty']);
    $cart[$slug] = min($maxAllowed, $quantity);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Resolves cart items and authoritative totals strictly from server-side catalog prices (in integer pence).
 */
function get_cart_summary(?PDO $pdo): array
{
    $raw = get_raw_cart();
    $items = [];
    $subtotalPence = 0;
    $totalCount = 0;

    foreach ($raw as $slug => $qty) {
        $qty = (int) $qty;
        if ($qty <= 0 || !is_string($slug)) {
            continue;
        }
        $product = get_product_by_slug($pdo, $slug);
        if ($product === null) {
            unset($_SESSION['cart'][$slug]);
            continue;
        }

        $clampedQty = min($qty, CART_MAX_QTY_PER_ITEM, max(1, (int) $product['stock_qty']));
        $unitPence = (int) $product['price_pence'];
        $linePence = $unitPence * $clampedQty;

        $items[] = [
            'product' => $product,
            'quantity' => $clampedQty,
            'unit_price_pence' => $unitPence,
            'line_total_pence' => $linePence,
        ];

        $subtotalPence += $linePence;
        $totalCount += $clampedQty;
    }

    // Shipping is £0.00 while placeholder items are £0.00
    $shippingPence = 0;
    $totalPence = $subtotalPence + $shippingPence;

    return [
        'items' => $items,
        'item_count' => $totalCount,
        'subtotal_pence' => $subtotalPence,
        'shipping_pence' => $shippingPence,
        'total_pence' => $totalPence,
    ];
}
