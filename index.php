<?php
/**
 * randomous - Front Controller
 * All requests route through here using direct PHP routing.
 */

// 1. Bootstrap
require_once __DIR__ . '/app/helpers/security.php';

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $exception): void {
    error_log('[Unhandled Exception] ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    renderErrorPage(500);
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR], true)) {
        error_log('[Fatal Error] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
        renderErrorPage(500);
    }
});

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// 2. Track visit
trackVisit();

// 3. Path-based routing
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . trim($path, '/');

$route = 'home';
$_GET['q'] = $_GET['q'] ?? '';
$_GET['slug'] = $_GET['slug'] ?? '';

if ($path === '/' || $path === '') {
    $route = 'home';
} elseif ($path === '/search') {
    $route = 'search';
    if (isset($_GET['q']) && trim((string)$_GET['q']) !== '') {
        $_GET['q'] = trim((string)$_GET['q']);
    } else {
        $_GET['q'] = '';
    }
} elseif (str_starts_with($path, '/search/')) {
    $route = 'search';
    $_GET['q'] = rawurldecode(substr($path, strlen('/search/')));
} elseif ($path === '/categories') {
    $route = 'category';
    $_GET['slug'] = '';
} elseif (str_starts_with($path, '/category/')) {
    $route = 'category';
    $_GET['slug'] = rawurldecode(substr($path, strlen('/category/')));
} elseif (preg_match('#^/([^/]+)/([^/]+)$#', $path, $matches)) {
    $route = 'article';
    $_GET['slug'] = rawurldecode($matches[2]);
    $_GET['category'] = rawurldecode($matches[1]);
} elseif ($path === '/error' || $path === '/404') {
    $route = 'error';
} elseif (!empty($_GET['route'])) {
    // Legacy fallback for query-string based navigation
    $route = $_GET['route'];
}

switch ($route) {
    case 'home':
        require __DIR__ . '/views/pages/homepage.php';
        break;

    case 'article':
        require __DIR__ . '/views/pages/article.php';
        break;

    case 'category':
        require __DIR__ . '/views/pages/categories.php';
        break;

    case 'search':
        require __DIR__ . '/views/pages/search.php';
        break;

    case 'error':
        renderErrorPage(404);
        break;

    default:
        renderErrorPage(404);
        break;
}
