<?php
const CART_MAX_QTY_PER_ITEM = 10;

// session stores cart_key => qty (cart_key is "slug" or "slug::Color"), prices come from server
function get_raw_cart(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function parse_cart_key(string $cartKey): array
{
    $parts = explode('::', trim($cartKey), 2);
    $slug = trim($parts[0]);
    $color = isset($parts[1]) ? trim($parts[1]) : '';
    return [$slug, $color];
}

function resolve_product_color(array $product, string $requestedColor = ''): string
{
    $options = isset($product['color_options']) && is_array($product['color_options'])
        ? $product['color_options']
        : [];
    if (empty($options)) {
        return '';
    }

    $requestedColor = trim($requestedColor);
    foreach ($options as $opt) {
        if (strcasecmp((string) $opt, $requestedColor) === 0) {
            return (string) $opt;
        }
    }

    return (string) $options[0];
}

function build_cart_key(string $slug, string $color = ''): string
{
    return $color !== '' ? ($slug . '::' . $color) : $slug;
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

function cart_add_item(?PDO $pdo, string $slug, int $quantity = 1, string $color = ''): array
{
    $product = get_product_by_slug($pdo, $slug);
    if ($product === null || (int) $product['stock_qty'] <= 0) {
        return ['ok' => false, 'clamped' => false, 'qty' => 0, 'display_name' => ''];
    }

    $resolvedColor = resolve_product_color($product, $color);
    $cartKey = build_cart_key($product['slug'], $resolvedColor);

    $cart = get_raw_cart();
    $current = (int) ($cart[$cartKey] ?? 0);
    $maxAllowed = min(CART_MAX_QTY_PER_ITEM, (int) $product['stock_qty']);
    $desired = $current + max(1, $quantity);
    $newQty = max(1, min($maxAllowed, $desired));

    $cart[$cartKey] = $newQty;
    $_SESSION['cart'] = $cart;

    $displayName = $resolvedColor !== ''
        ? sprintf('%s (%s)', $product['name'], $resolvedColor)
        : (string) $product['name'];

    return [
        'ok' => true,
        'clamped' => $desired > $maxAllowed,
        'qty' => $newQty,
        'display_name' => $displayName,
    ];
}

function cart_set_quantity(?PDO $pdo, string $cartKey, int $quantity, string $newColor = ''): array
{
    $cart = get_raw_cart();
    [$slug, $existingColor] = parse_cart_key($cartKey);

    if ($quantity <= 0) {
        unset($cart[$cartKey]);
        $_SESSION['cart'] = $cart;
        return ['ok' => true, 'removed' => true, 'clamped' => false, 'qty' => 0, 'max' => 0];
    }

    $product = get_product_by_slug($pdo, $slug);
    if ($product === null || (int) $product['stock_qty'] <= 0) {
        unset($cart[$cartKey]);
        $_SESSION['cart'] = $cart;
        return ['ok' => false, 'removed' => true, 'clamped' => false, 'qty' => 0, 'max' => 0];
    }

    $resolvedColor = resolve_product_color($product, $newColor !== '' ? $newColor : $existingColor);
    $targetKey = build_cart_key($product['slug'], $resolvedColor);

    $maxAllowed = min(CART_MAX_QTY_PER_ITEM, (int) $product['stock_qty']);
    if ($targetKey !== $cartKey) {
        unset($cart[$cartKey]);
        $combined = ((int) ($cart[$targetKey] ?? 0)) + $quantity;
        $finalQty = min($maxAllowed, $combined);
        $clamped = $combined > $maxAllowed;
    } else {
        $finalQty = min($maxAllowed, $quantity);
        $clamped = $quantity > $maxAllowed;
    }

    $cart[$targetKey] = $finalQty;
    $_SESSION['cart'] = $cart;

    return [
        'ok' => true,
        'removed' => false,
        'clamped' => $clamped,
        'qty' => $finalQty,
        'max' => $maxAllowed,
    ];
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function get_cart_summary(?PDO $pdo): array
{
    $raw = get_raw_cart();
    $items = [];
    $subtotalPence = 0;
    $totalCount = 0;

    foreach ($raw as $rawKey => $qty) {
        $qty = (int) $qty;
        if ($qty <= 0 || !is_string($rawKey)) {
            continue;
        }

        [$slug, $color] = parse_cart_key($rawKey);
        $product = get_product_by_slug($pdo, $slug);
        if ($product === null) {
            unset($_SESSION['cart'][$rawKey]);
            continue;
        }

        $resolvedColor = resolve_product_color($product, $color);
        $canonicalKey = build_cart_key($product['slug'], $resolvedColor);
        if ($canonicalKey !== $rawKey) {
            unset($_SESSION['cart'][$rawKey]);
            $_SESSION['cart'][$canonicalKey] = ((int) ($_SESSION['cart'][$canonicalKey] ?? 0)) + $qty;
            $qty = (int) $_SESSION['cart'][$canonicalKey];
        }

        $clampedQty = min($qty, CART_MAX_QTY_PER_ITEM, max(1, (int) $product['stock_qty']));
        $unitPence = (int) $product['price_pence'];
        $linePence = $unitPence * $clampedQty;

        $lineProduct = $product;
        if ($resolvedColor !== '') {
            $lineProduct['name'] = sprintf('%s (%s)', $product['name'], $resolvedColor);
        }

        $items[] = [
            'cart_key' => $canonicalKey,
            'selected_color' => $resolvedColor,
            'base_name' => (string) $product['name'],
            'product' => $lineProduct,
            'quantity' => $clampedQty,
            'unit_price_pence' => $unitPence,
            'line_total_pence' => $linePence,
        ];

        $subtotalPence += $linePence;
        $totalCount += $clampedQty;
    }

    // update postage pence here when live stock goes up
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
