<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/cart.php';

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

function enforce_site_mode(string $routeType = 'storefront'): void
{
    $mode = strtolower(env_val('APP_LAUNCHING_MODE', 'preview'));

    // When APP_LAUNCHING_MODE=launching, visitors hitting storefront routes see the launching waitlist
    if ($mode === 'launching' && $routeType === 'storefront') {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($uri === '/' || $uri === '/index') {
            require __DIR__ . '/../pages/launching.php';
            exit();
        }
        header('Location: /', true, 302);
        exit();
    }

    // When APP_LAUNCHING_MODE=live, retire launching routes with permanent 301 redirect to /
    if ($mode === 'live' && $routeType === 'launching') {
        header('Location: /', true, 301);
        exit();
    }
}
