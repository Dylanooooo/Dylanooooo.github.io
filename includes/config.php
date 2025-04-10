<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = 'localhost';
$db_name = 'flitz_events';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include database update utility
require_once __DIR__ . '/database_update.php';

// Check and update database structure
updateDatabaseStructure($pdo);

// Other configuration settings can be added here
?>
