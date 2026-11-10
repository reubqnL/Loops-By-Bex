<?php
if (!defined('LBB_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('403 Forbidden');
}
const CART_MAX_QTY_PER_ITEM = 10;
const STANDARD_UK_SHIPPING_PENCE = 250;

// sheffield outcodes within 5 miles of s6 (hillsborough / walkley / wadsley)
function is_free_local_s6_postcode(string $postcode): bool
{
    $clean = strtoupper((string) preg_replace('/\s+/', '', $postcode));
    if ($clean === '' || !preg_match('/^([A-Z]{1,2}[0-9][0-9A-Z]?)[0-9][A-Z]{2}$/', $clean, $matches)) {
        return false;
    }

    $freeLocalOutcodes = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'S10', 'S11', 'S14', 'S35'];
    return in_array($matches[1], $freeLocalOutcodes, true);
}

function calculate_shipping_pence(int $subtotalPence, ?string $shippingPostcode = null): int
{
    if ($subtotalPence <= 0) {
        return 0;
    }
    if ($shippingPostcode === null) {
        return 0;
    }
    if (is_free_local_s6_postcode($shippingPostcode)) {
        return 0;
    }
    return STANDARD_UK_SHIPPING_PENCE;
}

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

function get_cart_summary(?PDO $pdo, ?string $shippingPostcode = null): array
{
    $raw = get_raw_cart();
    $normalized = [];

    foreach ($raw as $rawKey => $qty) {
        $qty = (int) $qty;
        if ($qty <= 0 || !is_string($rawKey)) {
            continue;
        }

        [$slug, $color] = parse_cart_key($rawKey);
        $product = get_product_by_slug($pdo, $slug);
        if ($product === null) {
            continue;
        }

        $resolvedColor = resolve_product_color($product, $color);
        $canonicalKey = build_cart_key($product['slug'], $resolvedColor);
        $normalized[$canonicalKey] = ((int) ($normalized[$canonicalKey] ?? 0)) + $qty;
    }

    $_SESSION['cart'] = $normalized;

    $items = [];
    $subtotalPence = 0;
    $totalCount = 0;

    foreach ($normalized as $canonicalKey => $qty) {
        [$slug, $resolvedColor] = parse_cart_key($canonicalKey);
        $product = get_product_by_slug($pdo, $slug);
        if ($product === null) {
            continue;
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

    $shippingPence = calculate_shipping_pence($subtotalPence, $shippingPostcode);
    $totalPence = $subtotalPence + $shippingPence;

    return [
        'items' => $items,
        'item_count' => $totalCount,
        'subtotal_pence' => $subtotalPence,
        'shipping_pence' => $shippingPence,
        'is_free_local_delivery' => ($shippingPostcode !== null && $shippingPostcode !== '' && is_free_local_s6_postcode($shippingPostcode)),
        'total_pence' => $totalPence,
    ];
}
