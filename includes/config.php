<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Environment configuration
$environment = 'development'; // Change to 'production' for live site

// Database configuration
$db_host = 'localhost';
$db_name = 'flitz_events';
$db_user = 'root';
$db_pass = '';

// Application configuration
$app_config = [
    'upload_max_size' => 5 * 1024 * 1024, // 5MB
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'session_timeout' => 3600, // 1 hour
    'chat_refresh_interval' => 10000, // 10 seconds
    'timezone' => 'Europe/Amsterdam',
    'date_format' => 'd-m-Y',
    'datetime_format' => 'd-m-Y H:i',
];

// Set timezone
date_default_timezone_set($app_config['timezone']);

// Error reporting based on environment
if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');
}

// Create logs directory if it doesn't exist
$log_dir = dirname(__DIR__) . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    if ($environment === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Er is een technische storing. Probeer het later opnieuw.");
    }
}

// Include database update utility
require_once __DIR__ . '/database_update.php';

// Check and update database structure BEFORE using the database
try {
    updateDatabaseStructure($pdo);
} catch (Exception $e) {
    error_log("Database structure update failed: " . $e->getMessage());
    if ($environment === 'development') {
        echo "Warning: Database structure update failed: " . $e->getMessage() . "<br>";
    }
}

// Add missing columns if they don't exist
try {
    // Check and add aangemaakt_door column to projecten table
    $stmt = $pdo->query("SHOW COLUMNS FROM projecten LIKE 'aangemaakt_door'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE projecten ADD COLUMN aangemaakt_door int(11) DEFAULT NULL");
        error_log("Added aangemaakt_door column to projecten table");
    }
    
    // Check and add aangemaakt_door column to taken table
    $stmt = $pdo->query("SHOW COLUMNS FROM taken LIKE 'aangemaakt_door'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE taken ADD COLUMN aangemaakt_door int(11) DEFAULT NULL");
        error_log("Added aangemaakt_door column to taken table");
    }
    
    // Add foreign key constraints if they don't exist (ignore errors if they already exist)
    try {
        $pdo->exec("ALTER TABLE projecten ADD CONSTRAINT fk_projecten_aangemaakt_door FOREIGN KEY (aangemaakt_door) REFERENCES gebruikers(id) ON DELETE SET NULL");
    } catch (PDOException $e) {
        // Constraint might already exist
    }
    
    try {
        $pdo->exec("ALTER TABLE taken ADD CONSTRAINT fk_taken_aangemaakt_door FOREIGN KEY (aangemaakt_door) REFERENCES gebruikers(id) ON DELETE SET NULL");
    } catch (PDOException $e) {
        // Constraint might already exist
    }
    
} catch (PDOException $e) {
    error_log("Database structure update error: " . $e->getMessage());
}

// NOW it's safe to update last login time
if (isset($_SESSION['user_id']) && !isset($_SESSION['login_time_updated'])) {
    try {
        $stmt = $pdo->prepare("UPDATE gebruikers SET laatst_ingelogd = NOW() WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $_SESSION['login_time_updated'] = true;
    } catch (PDOException $e) {
        error_log("Failed to update last login time: " . $e->getMessage());
    }
}

// Session security and timeout handling
if (isset($_SESSION['user_id'])) {
    // Check session timeout
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > $app_config['session_timeout'])) {
        session_unset();
        session_destroy();
        header("Location: " . (basename($_SERVER['PHP_SELF']) === 'index.php' ? 'index.php' : '../index.php') . "?timeout=1");
        exit;
    }
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Helper functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logActivity($user_id, $action, $details = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                              VALUES (:user_id, :action, :details, :ip, :user_agent, NOW())");
        $stmt->execute([
            'user_id' => $user_id,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Create activity log table if it doesn't exist
try {
    $activity_log_sql = "CREATE TABLE IF NOT EXISTS `activity_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(100) NOT NULL,
        `details` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_action` (`action`),
        INDEX `idx_created_at` (`created_at`),
        FOREIGN KEY (`user_id`) REFERENCES `gebruikers`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($activity_log_sql);
} catch (PDOException $e) {
    error_log("Failed to create activity_log table: " . $e->getMessage());
}

// Global error handler for uncaught exceptions
set_exception_handler(function($exception) use ($environment) {
    error_log("Uncaught exception: " . $exception->getMessage());
    
    if ($environment === 'development') {
        echo "Uncaught exception: " . $exception->getMessage();
    } else {
        echo "Er is een fout opgetreden. Probeer het later opnieuw.";
    }
});
?>
