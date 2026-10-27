<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
} elseif (is_file(__DIR__ . '/.env')) {
    // fallback if vendor folder isnt installed locally
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
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
