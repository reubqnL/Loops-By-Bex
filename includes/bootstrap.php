<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/security.php';
if (is_file(__DIR__ . '/catalog.php')) {
    require_once __DIR__ . '/catalog.php';
}
if (is_file(__DIR__ . '/cart.php')) {
    require_once __DIR__ . '/cart.php';
}

function env_val(string $key, string $default = ''): string
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    $val = getenv($key);
    return ($val !== false && $val !== '') ? (string) $val : $default;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset_url(string $path): string
{
    if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
        return $path;
    }
    $cleanPath = (string) parse_url($path, PHP_URL_PATH);
    $diskFile = __DIR__ . '/..' . $cleanPath;
    if (is_file($diskFile)) {
        $mtime = @filemtime($diskFile);
        if ($mtime !== false) {
            return $cleanPath . '?v=' . $mtime;
        }
    }
    return $path;
}

function format_gbp(int $pence): string
{
    $negative = $pence < 0;
    $abs = abs($pence);
    return ($negative ? '-' : '') . '£' . number_format($abs / 100, 2, '.', ',');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): ?array
{
    if (!isset($_SESSION['flash_message']) || !is_array($_SESSION['flash_message'])) {
        return null;
    }
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    return $flash;
}

// controls whether / shows waitlist or full shop based on .env
function enforce_site_mode(string $routeType = 'storefront'): void
{
    $mode = strtolower(env_val('APP_LAUNCHING_MODE', 'preview'));

    if ($mode === 'launching' && $routeType === 'storefront') {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($uri === '/' || $uri === '/index') {
            require __DIR__ . '/../pages/launching.php';
            exit();
        }
        header('Location: /', true, 302);
        exit();
    }

    if ($mode === 'live' && $routeType === 'launching') {
        header('Location: /', true, 301);
        exit();
    }
}

$appEnv = env_val('APP_ENV', 'production');
configure_runtime_security($appEnv);
start_secure_session();
send_security_headers($appEnv);
csrf_token();
