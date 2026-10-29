<?php
// local dev router for php -S localhost:8000 router.php
if (PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    exit('Forbidden');
}

$uriPath = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if (
    preg_match('#^/(config\.php|router\.php|includes(/|$)|partials(/|$)|database(/|$)|vendor(/|$)|storage(/|$)|\.|composer\.|LICENSE)#i', $uriPath)
    || preg_match('#\.(scss|map|sql|sqlite)$#i', $uriPath)
) {
    http_response_code(403);
    exit('403 Forbidden');
}

$fullAssetPath = __DIR__ . $uriPath;
if ($uriPath !== '/' && is_file($fullAssetPath) && !str_ends_with(strtolower($uriPath), '.php')) {
    return false;
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (in_array($method, ['GET', 'HEAD'], true)) {
    if (preg_match('#^/(pages/)?index(\.php)?$#i', $uriPath)) {
        header('Location: /', true, 301);
        exit();
    }
    if (preg_match('#^/pages/([a-zA-Z0-9_-]+)\.php$#', $uriPath, $m)) {
        header('Location: /' . $m[1], true, 301);
        exit();
    }
}

if ($uriPath === '/') {
    require __DIR__ . '/pages/index.php';
    return true;
}

if (preg_match('#^/(cart-action|process-waitlist|webhook-stripe)\.php$#', $uriPath) && is_file($fullAssetPath)) {
    require $fullAssetPath;
    return true;
}

if (preg_match('#^/([a-zA-Z0-9_-]+)$#', $uriPath, $m)) {
    $pageFile = __DIR__ . '/pages/' . $m[1] . '.php';
    if (is_file($pageFile)) {
        require $pageFile;
        return true;
    }
}

require __DIR__ . '/pages/404.php';
return true;
