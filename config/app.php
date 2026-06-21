<?php
/**
 * Application Bootstrap & Settings
 */

// Error reporting: Development mode for localhost, Production for others
// Set APP_ENV=production on live server
$env = getenv('APP_ENV');
if ($env === null) {
    // Auto-detect based on hostname
    $env = (strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false) ? 'development' : 'production';
}

if ($env === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', SITE_PATH . '/storage/logs/error.log');
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', SITE_PATH . '/storage/logs/error.log');
}

// Secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}

/**
 * Load global settings from DB (cached in static)
 */
function settings(): array {
    static $settings = null;
    if ($settings === null) {
        try {
            $row = db()->query("SELECT * FROM settings WHERE id = 1 LIMIT 1")->fetch();
            $settings = $row ?: [];
        } catch (Throwable $e) {
            $settings = [];
        }
    }
    return $settings;
}

/**
 * Get a single setting value with fallback
 */
function setting(string $key, string $default = ''): string {
    return settings()[$key] ?? $default;
}

/**
 * Track page visit (non-bot only)
 */
function trackVisit(): void {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/bot|crawl|slurp|spider|google|bing|yandex|baidu/i', $agent)) {
        return;
    }
    try {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
        $url = $_SERVER['REQUEST_URI'] ?? '';
        db()->prepare("INSERT INTO analytics (page_url, ip_address, user_agent) VALUES (?, ?, ?)")
             ->execute([$url, $ip, substr($agent, 0, 255)]);
    } catch (Throwable $e) {
        // Analytics failure should never break the site
    }
}
