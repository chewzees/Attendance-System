<?php
/**
 * Database Configuration
 * College Face Recognition Attendance System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance_system');
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Auto-detect application base path from install folder (works for any folder name)
 */
function getBasePath() {
    static $basePath = null;
    if ($basePath !== null) {
        return $basePath;
    }

    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(dirname(__DIR__));

    if ($docRoot && $projectRoot) {
        $docRoot = str_replace('\\', '/', $docRoot);
        $projectRoot = str_replace('\\', '/', $projectRoot);
        if (strpos($projectRoot, $docRoot) === 0) {
            $basePath = substr($projectRoot, strlen($docRoot));
            return $basePath = rtrim($basePath, '/') ?: '';
        }
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (preg_match('#^(/[^/]+)/#', $script, $matches)) {
        return $basePath = $matches[1];
    }

    return $basePath = '';
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', getBasePath());
}

function baseUrl($path = '') {
    $path = ltrim((string) $path, '/');
    return BASE_PATH . ($path !== '' ? '/' . $path : '');
}

// Timezone configuration
date_default_timezone_set('Asia/Kolkata');

// Error reporting — log to file, never expose to browser
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>

