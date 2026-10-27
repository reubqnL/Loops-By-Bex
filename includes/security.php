<?php
declare(strict_types=1);

/**
 * Core security primitives for Loops By Bex:
 * - Hardened PHP runtime & session cookie settings
 * - Defense-in-depth HTTP security headers (strict CSP without unsafe-inline scripts)
 * - Synchronizer CSRF tokens verified with constant-time hash_equals()
 * - Same-origin Origin/Referer verification on state-changing requests
 * - Anti-automation honeypot & privacy-preserving HMAC-hashed IP rate limiting
 * - PCI-DSS SAQ-A guardrails blocking raw card PAN/CVC submission to PHP
 * - Stripe Hosted Checkout session helper & webhook signature verification
 */

function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    return $forwardedProto === 'https';
}

function configure_runtime_security(string $appEnv): void
{
    if (function_exists('header_remove')) {
        @header_remove('X-Powered-By');
    }

    ini_set('log_errors', '1');
    if (strtolower($appEnv) !== 'development') {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
    }
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = is_https_request();

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($secure) {
        ini_set('session.cookie_secure', '1');
    }

    session_name('lbb_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    // Rotate session ID periodically (every 30 minutes) to limit session hijacking exposure
    $now = time();
    if (!isset($_SESSION['_session_created_at'])) {
        $_SESSION['_session_created_at'] = $now;
    } elseif (($now - (int) $_SESSION['_session_created_at']) > 1800) {
        regenerate_session_safely();
    }
}

function regenerate_session_safely(): void
{
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
        $_SESSION['_session_created_at'] = time();
    }
}

function send_security_headers(string $appEnv = 'production'): void
{
    if (headers_sent()) {
        return;
    }

    $isDev = strtolower($appEnv) === 'development';

    header('X-Content-Type-Options: nosniff');
    if (!$isDev) {
        header('X-Frame-Options: SAMEORIGIN');
    }
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=(), payment=(self "https://js.stripe.com" "https://checkout.stripe.com")');
    header(
        "Content-Security-Policy: "
        . "default-src 'self'; "
        . "script-src 'self' https://js.stripe.com; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "img-src 'self' data: https:; "
        . "connect-src 'self' https://api.stripe.com; "
        . "frame-src https://js.stripe.com https://hooks.stripe.com https://checkout.stripe.com; "
        . "base-uri 'self'; "
        . "form-action 'self' https://checkout.stripe.com;"
        . ($isDev ? '' : " frame-ancestors 'self';")
    );

    if (is_https_request() && !$isDev) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_same_origin(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return true;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerKey) {
        $raw = trim((string) ($_SERVER[$headerKey] ?? ''));
        if ($raw === '') {
            continue;
        }
        $parsedHost = strtolower((string) parse_url($raw, PHP_URL_HOST));
        $parsedPort = parse_url($raw, PHP_URL_PORT);
        $candidate = $parsedPort !== null ? ($parsedHost . ':' . $parsedPort) : $parsedHost;

        if ($candidate !== $host && $parsedHost !== explode(':', $host)[0]) {
            return false;
        }
        return true;
    }

    // Allow requests from privacy browsers that strip Origin/Referer only when CSRF token still matches
    return true;
}

function verify_csrf_request(): bool
{
    if (!verify_same_origin()) {
        return false;
    }

    $expected = $_SESSION['csrf_token'] ?? '';
    $provided = (string) ($_POST['csrf_token'] ?? '');

    if (!is_string($expected) || $expected === '' || $provided === '') {
        return false;
    }

    return hash_equals($expected, $provided);
}

function verify_csrf_or_redirect(string $fallbackPath = '/'): void
{
    if (!verify_csrf_request()) {
        error_log('[LoopsByBex Security] CSRF validation failed on ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        http_response_code(403);
        set_flash('error', 'Your form session expired for security reasons. Please try again.');
        header('Location: ' . $fallbackPath, true, 303);
        exit();
    }
}

function honeypot_field(): string
{
    return '<div class="hp-trap" aria-hidden="true">'
        . '<label for="website_url_hp">Leave this field blank</label>'
        . '<input type="text" id="website_url_hp" name="website_url" tabindex="-1" autocomplete="off" value="">'
        . '</div>';
}

function is_honeypot_triggered(): bool
{
    return trim((string) ($_POST['website_url'] ?? '')) !== '';
}

function get_client_ip_hash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $pepper = env_val('APP_KEY', 'loops-by-bex-rate-limit-v1');
    return hash_hmac('sha256', $ip, $pepper);
}

/**
 * Enforces sliding-window rate limits per action + HMAC-hashed IP (plus session counter).
 * Returns true if the request is allowed, false if rate-limited.
 */
function check_rate_limit(?PDO $pdo, string $action, int $maxAttempts, int $windowSeconds): bool
{
    $now = time();

    // Layer 1: Session-scoped rate limit (always available even without DB)
    if (!isset($_SESSION['_rate_limits']) || !is_array($_SESSION['_rate_limits'])) {
        $_SESSION['_rate_limits'] = [];
    }
    $bucket = $_SESSION['_rate_limits'][$action] ?? ['count' => 0, 'start' => $now];
    if (($now - (int) $bucket['start']) >= $windowSeconds) {
        $bucket = ['count' => 1, 'start' => $now];
    } else {
        $bucket['count'] = (int) $bucket['count'] + 1;
    }
    $_SESSION['_rate_limits'][$action] = $bucket;

    if ((int) $bucket['count'] > $maxAttempts) {
        return false;
    }

    // Layer 2: Database-backed IP rate limit (catches cookieless bots across sessions)
    if ($pdo instanceof PDO) {
        $ipHash = get_client_ip_hash();
        try {
            $select = $pdo->prepare(
                'SELECT attempts, window_started_at FROM rate_limits WHERE action_key = :action AND ip_hash = :ip LIMIT 1'
            );
            $select->execute(['action' => $action, 'ip' => $ipHash]);
            $row = $select->fetch();

            if (!is_array($row) || ($now - (int) $row['window_started_at']) >= $windowSeconds) {
                $upsert = $pdo->prepare(
                    'INSERT INTO rate_limits (action_key, ip_hash, attempts, window_started_at)
                     VALUES (:action, :ip, 1, :now_insert)
                     ON DUPLICATE KEY UPDATE attempts = 1, window_started_at = :now_update'
                );
                $upsert->execute([
                    'action' => $action,
                    'ip' => $ipHash,
                    'now_insert' => $now,
                    'now_update' => $now,
                ]);
                return true;
            }

            $attempts = (int) $row['attempts'] + 1;
            $update = $pdo->prepare(
                'UPDATE rate_limits SET attempts = :attempts WHERE action_key = :action AND ip_hash = :ip'
            );
            $update->execute([
                'attempts' => $attempts,
                'action' => $action,
                'ip' => $ipHash,
            ]);

            return $attempts <= $maxAttempts;
        } catch (PDOException $e) {
            // Fall back safely to the session limiter if rate_limits table is not yet migrated or SQLite is in use
            error_log('[LoopsByBex RateLimit] DB limiter fallback: ' . $e->getMessage());
        }
    }

    return true;
}

/**
 * PCI-DSS SAQ-A hard guardrail: raw payment card numbers / security codes must NEVER touch PHP.
 */
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
            error_log('[LoopsByBex PCI ALERT] Blocked forbidden card field submission: ' . $normalized);
            http_response_code(400);
            exit('Invalid payment request. Raw card data is never accepted by this server.');
        }
    }
}

/**
 * Creates a Stripe Hosted Checkout Session (PCI-DSS SAQ-A) using authoritative server-side GBP pence amounts.
 * Returns ['id' => string, 'url' => string] on success or null on failure.
 */
function create_stripe_checkout_session(
    string $stripeSecretKey,
    string $orderNumber,
    string $customerEmail,
    array $cartSummary,
    string $idempotencyKey
): ?array {
    if (!function_exists('curl_init')) {
        error_log('[LoopsByBex Stripe] ext-curl is required to initiate Stripe Hosted Checkout.');
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
    curl_close($ch);

    if (!is_string($rawResponse) || $httpStatus < 200 || $httpStatus >= 300) {
        error_log('[LoopsByBex Stripe] Checkout session creation failed with HTTP ' . $httpStatus);
        return null;
    }

    $decoded = json_decode($rawResponse, true);
    if (!is_array($decoded) || empty($decoded['id']) || empty($decoded['url'])) {
        return null;
    }

    $checkoutUrl = (string) $decoded['url'];
    $checkoutHost = strtolower((string) parse_url($checkoutUrl, PHP_URL_HOST));
    if ($checkoutHost !== 'checkout.stripe.com') {
        error_log('[LoopsByBex Stripe] Unexpected checkout redirect host: ' . $checkoutHost);
        return null;
    }

    return [
        'id' => (string) $decoded['id'],
        'url' => $checkoutUrl,
    ];
}

/**
 * Verifies a Stripe-Signature header against raw webhook payload using HMAC-SHA256 + 300s replay tolerance.
 */
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
