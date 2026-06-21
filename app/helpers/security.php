<?php
/**
 * Security Helpers
 */

/**
 * Escape output for HTML context
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Render the brand name with the last "o" in red text.
 */
function brandLogoName(string $value): string {
    $position = mb_strrpos(mb_strtolower($value), 'o');
    if ($position === false) {
        return e($value);
    }

    $prefix = e(mb_substr($value, 0, $position));
    $lastO = e(mb_substr($value, $position, 1));
    $suffix = e(mb_substr($value, $position + 1));

    return $prefix
        . '<span class="text-[#FF0033] font-black" aria-label="' . e($lastO) . '">' . $lastO . '</span>'
        . $suffix;
}

/**
 * Sanitize a slug (alphanumeric + hyphens only)
 */
function sanitizeSlug(string $input): string {
    return preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($input)));
}

/**
 * Sanitize search query
 */
function sanitizeSearch(string $input): string {
    $input = strip_tags(trim($input));
    return mb_substr($input, 0, 100);
}

/**
 * Sanitize category name
 */
function sanitizeCategory(string $input): string {
    $input = strip_tags(trim($input));
    return mb_substr($input, 0, 80);
}

/**
 * Generate CSRF token
 */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrf(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF input field HTML
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Calculate reading time in minutes
 */
function readingTime(string $content): int {
    $wordCount = str_word_count(strip_tags($content));
    return max(1, (int) ceil($wordCount / 200));
}

/**
 * Format views number
 */
function formatViews(int $views): string {
    if ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M';
    }
    if ($views >= 1000) {
        return round($views / 1000, 1) . 'K';
    }
    return number_format($views);
}

/**
 * Build an article URL using clean path-based routing.
 */
function articleUrl(string $slug, string $category): string {
    $category = trim($category);
    return SITE_URL . '/' . rawurlencode($category) . '/' . rawurlencode($slug);
}

/**
 * Build a category URL using clean path-based routing.
 */
function categoryUrl(string $category): string {
    return SITE_URL . '/category/' . rawurlencode($category);
}

/**
 * Build a search URL using clean path-based routing.
 */
function searchUrl(string $query = ''): string {
    $query = trim($query);
    if ($query === '') {
        return SITE_URL . '/search';
    }
    return SITE_URL . '/search/' . rawurlencode($query);
}

/**
 * Build the category listing URL.
 */
function categoriesUrl(): string {
    return SITE_URL . '/categories';
}

/**
 * Get current full URL
 */
function currentUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
}

/**
 * Render the shared error page for any HTTP error status.
 */
function renderErrorPage(int $statusCode = 500): void {
    http_response_code($statusCode);
    require __DIR__ . '/../../views/pages/error.php';
    exit;
}
