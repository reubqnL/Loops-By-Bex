<?php
// standard boilerplate

// checks if running locally or on IONOS
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0) {
    // --- LOCAL DATABASE SETTINGS ---
    $db_host = 'localhost';
    $db_name = 'your_local_db_name'; // Change this to your local database name
    $db_user = 'root';              // Default local username (e.g., root)
    $db_pass = '';                  // Default local password (often blank for XAMPP/MAMP)
} else {
    // --- IONOS LIVE DATABASE SETTINGS ---
    $db_host = 'dbxxxxxxxxx.db.1and1.com';
    $db_name = 'dbxxxxxxxxx';
    $db_user = 'dboXXXXXXXX';
    $db_pass = 'YourIonosDatabasePassword';
}

try {
    // connect to mysql securely using pdo
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed. Please check your configuration settings.");
}
?>