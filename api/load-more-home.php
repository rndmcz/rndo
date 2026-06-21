<?php
/**
 * api/load-more-home.php
 * Returns HTML fragments for home feed pagination
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/security.php';

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit  = 15;

try {
    $stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $feeds = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    exit;
}

if (empty($feeds)) {
    echo 'END';
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
foreach ($feeds as $feed):
    $item = $feed;
    $size = 'large';
    require __DIR__ . '/../views/components/article-card.php';
endforeach;
