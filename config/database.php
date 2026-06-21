<?php
/**
 * Database Configuration
 * Uses environment variable pattern with fallback
 * Change credentials here or define DB_* constants before including this file
 */

defined('DB_DRIVER')  || define('DB_DRIVER',  'mysql');

defined('SITE_PATH') || define('SITE_PATH', dirname(__DIR__));
defined('DB_HOST')    || define('DB_HOST',    'sql310.infinityfree.com');
defined('DB_NAME')    || define('DB_NAME',    'if0_41370463_ns');
defined('DB_USER')    || define('DB_USER',    'if0_41370463');
defined('DB_PASS')    || define('DB_PASS',    'DG346SToSirb6d');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// Site URL - auto-detect, override if needed
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $protocol . '://' . $host);
}

/**
 * Current PDO driver name
 */
function dbDriver(): string {
    return strtolower(trim(DB_DRIVER));
}

/**
 * Maps common date expressions for MySQL.
 */
function dbDateExpression(string $purpose): string {
    return match ($purpose) {
        'now'           => 'NOW()',
        '30_days_ago'   => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
        '5_minutes_ago' => 'NOW() - INTERVAL 5 MINUTE',
        '7_days_ago'    => 'NOW() - INTERVAL 7 DAY',
        default         => 'NOW()',
    };
}

/**
 * Get PDO connection (singleton)
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DB ERROR] ' . $e->getMessage());
            http_response_code(503);
            exit('Service temporarily unavailable. Please try again later.');
        }
    }
    return $pdo;
}
