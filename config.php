<?php
if (basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === 'config.php') {
    http_response_code(403);
    exit('403 Forbidden');
}
if (!defined('LBB_BOOTSTRAPPED')) {
    define('LBB_BOOTSTRAPPED', true);
}

// optional php-based env config (cannot be read in plain text over http even without .htaccess)
$phpEnvPath = __DIR__ . '/includes/env.php';
if (is_file($phpEnvPath)) {
    $phpEnvVars = require $phpEnvPath;
    if (is_array($phpEnvVars)) {
        foreach ($phpEnvVars as $k => $v) {
            if (is_string($k) && $k !== '' && !array_key_exists($k, $_ENV)) {
                $_ENV[$k] = (string) $v;
            }
        }
    }
}

$envDir = is_file(dirname(__DIR__) . '/.env') ? dirname(__DIR__) : __DIR__;
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
    if (class_exists('Dotenv\\Dotenv') && is_file($envDir . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($envDir);
        $dotenv->safeLoad();
    }
} elseif (is_file($envDir . '/.env')) {
    // fallback if vendor folder isnt installed locally
    $lines = file($envDir . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $trimmed, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if ($k !== '' && !array_key_exists($k, $_ENV)) {
            $_ENV[$k] = $v;
        }
    }
}

$pdo = null;
$dbDriver = strtolower((string) ($_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysql'));
$dbHost = (string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '');
$dbPort = (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
$dbName = (string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '');
$dbUser = (string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: '');
$dbPass = (string) ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
$dbSslCa = (string) ($_ENV['DB_SSL_CA'] ?? getenv('DB_SSL_CA') ?: '');

try {
    if ($dbDriver === 'sqlite') {
        // local sqlite db for testing in phpstorm since ionos blocks external mysql connections
        $sqlitePath = $dbName !== '' && !str_contains($dbName, 'dbs')
            ? $dbName
            : (__DIR__ . '/storage/local.sqlite');
        $sqliteDir = dirname($sqlitePath);
        if (!is_dir($sqliteDir)) {
            @mkdir($sqliteDir, 0750, true);
        }
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS waitlist (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT NOT NULL UNIQUE,
                idempotency_key TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL,
                customer_email TEXT NOT NULL,
                shipping_line1 TEXT NOT NULL,
                shipping_line2 TEXT NOT NULL DEFAULT '',
                shipping_city TEXT NOT NULL,
                shipping_postcode TEXT NOT NULL,
                shipping_country TEXT NOT NULL DEFAULT 'GB',
                order_notes TEXT NOT NULL DEFAULT '',
                subtotal_pence INTEGER NOT NULL DEFAULT 0,
                shipping_pence INTEGER NOT NULL DEFAULT 0,
                total_pence INTEGER NOT NULL DEFAULT 0,
                currency TEXT NOT NULL DEFAULT 'GBP',
                payment_provider TEXT NOT NULL DEFAULT 'stripe_hosted',
                payment_reference TEXT NOT NULL DEFAULT '',
                payment_status TEXT NOT NULL DEFAULT 'pending',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_slug TEXT NOT NULL,
                product_name TEXT NOT NULL,
                unit_price_pence INTEGER NOT NULL DEFAULT 0,
                quantity INTEGER NOT NULL DEFAULT 1,
                line_total_pence INTEGER NOT NULL DEFAULT 0,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                action_key TEXT NOT NULL,
                ip_hash TEXT NOT NULL,
                attempts INTEGER NOT NULL DEFAULT 1,
                window_started_at INTEGER NOT NULL,
                UNIQUE (action_key, ip_hash)
            );"
        );
    } elseif ($dbHost !== '' && $dbName !== '' && $dbUser !== '') {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ];
        if ($dbSslCa !== '' && defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $dbSslCa;
        }
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    }
} catch (PDOException $e) {
    // log to server error log, never echo db details to page
    error_log('db connection failed: ' . $e->getMessage());
    $pdo = null;
}
