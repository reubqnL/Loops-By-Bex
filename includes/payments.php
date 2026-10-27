<?php
// stripe handles all card inputs on their hosted page - block raw card fields if anything ever posts them here
function assert_no_raw_card_data_posted(): void
{
    $forbiddenKeys = [
        'card_number',
        'cardnumber',
        'cc_number',
        'ccnum',
        'pan',
        'cvc',
        'cvv',
        'cvv2',
        'security_code',
        'exp_month',
        'exp_year',
        'card_expiry',
    ];

    foreach (array_keys($_POST) as $postKey) {
        $normalized = strtolower(preg_replace('/[^a-z0-9_]/i', '', (string) $postKey) ?? '');
        if (in_array($normalized, $forbiddenKeys, true)) {
            error_log('blocked raw card field: ' . $normalized);
            http_response_code(400);
            exit('Invalid payment request.');
        }
    }
}

function create_stripe_checkout_session(
    string $stripeSecretKey,
    string $orderNumber,
    string $customerEmail,
    array $cartSummary,
    string $idempotencyKey
): ?array {
    if (!function_exists('curl_init')) {
        error_log('curl extension missing for stripe checkout');
        return null;
    }

    $appUrl = rtrim(env_val('APP_URL', 'https://loopsbybex.co.uk'), '/');
    $params = [
        'mode' => 'payment',
        'client_reference_id' => $orderNumber,
        'customer_email' => $customerEmail,
        'success_url' => $appUrl . '/order-confirmation?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $appUrl . '/checkout?cancelled=1',
        'payment_method_types' => ['card'],
        'metadata' => [
            'order_number' => $orderNumber,
        ],
    ];

    $lineIdx = 0;
    foreach ($cartSummary['items'] as $line) {
        $params['line_items'][$lineIdx] = [
            'quantity' => (int) $line['quantity'],
            'price_data' => [
                'currency' => 'gbp',
                'unit_amount' => (int) $line['unit_price_pence'],
                'product_data' => [
                    'name' => (string) $line['product']['name'],
                ],
            ],
        ];
        $lineIdx++;
    }

    if ((int) $cartSummary['shipping_pence'] > 0) {
        $params['line_items'][$lineIdx] = [
            'quantity' => 1,
            'price_data' => [
                'currency' => 'gbp',
                'unit_amount' => (int) $cartSummary['shipping_pence'],
                'product_data' => [
                    'name' => 'UK Tracked Delivery',
                ],
            ],
        ];
    }

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    if ($ch === false) {
        return null;
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $stripeSecretKey,
            'Content-Type: application/x-www-form-urlencoded',
            'Idempotency-Key: checkout-' . hash('sha256', $idempotencyKey),
        ],
    ]);

    $rawResponse = curl_exec($ch);
    $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    unset($ch);

    if (!is_string($rawResponse) || $httpStatus < 200 || $httpStatus >= 300) {
        error_log('stripe session failed http ' . $httpStatus);
        return null;
    }

    $decoded = json_decode($rawResponse, true);
    if (!is_array($decoded) || empty($decoded['id']) || empty($decoded['url'])) {
        return null;
    }

    $checkoutUrl = (string) $decoded['url'];
    $checkoutHost = strtolower((string) parse_url($checkoutUrl, PHP_URL_HOST));
    if ($checkoutHost !== 'checkout.stripe.com') {
        return null;
    }

    return [
        'id' => (string) $decoded['id'],
        'url' => $checkoutUrl,
    ];
}

// check stripe-signature header + 5 min timestamp window
function verify_stripe_webhook_signature(string $payload, string $sigHeader, string $secret, int $tolerance = 300): bool
{
    if ($payload === '' || $sigHeader === '' || $secret === '') {
        return false;
    }

    $timestamp = 0;
    $signatures = [];
    foreach (explode(',', $sigHeader) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') {
            $timestamp = (int) $v;
        } elseif ($k === 'v1' && $v !== '') {
            $signatures[] = $v;
        }
    }

    if ($timestamp <= 0 || empty($signatures)) {
        return false;
    }

    if (abs(time() - $timestamp) > $tolerance) {
        return false;
    }

    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    foreach ($signatures as $candidate) {
        if (hash_equals($expected, $candidate)) {
            return true;
        }
    }

    return false;
}
