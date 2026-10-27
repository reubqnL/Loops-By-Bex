<?php
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

    // rotate session id every 30 mins
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
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=()');
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
        error_log('csrf failed on ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        http_response_code(403);
        set_flash('error', 'Your form session expired. Please try again.');
        header('Location: ' . $fallbackPath, true, 303);
        exit();
    }
}

// hidden input to catch spam bots
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
    // hash ip so we dont store raw visitor ips in mysql
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $pepper = env_val('APP_KEY', 'loops-by-bex-rate-limit-v1');
    return hash_hmac('sha256', $ip, $pepper);
}

function check_rate_limit(?PDO $pdo, string $action, int $maxAttempts, int $windowSeconds): bool
{
    $now = time();

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
            error_log('rate limit db fallback: ' . $e->getMessage());
        }
    }

    return true;
}
