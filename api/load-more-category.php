<?php
/**
 * api/load-more-category.php
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }

$category = sanitizeCategory($_GET['category'] ?? '');
$offset   = max(0, (int)($_GET['offset'] ?? 0));
$limit    = 15;

if (empty($category)) { http_response_code(400); exit; }

try {
    $stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE category = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$category, $limit, $offset]);
    $posts = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    exit;
}

if (empty($posts)) { echo 'END'; exit; }

header('Content-Type: text/html; charset=UTF-8');
foreach ($posts as $post):
    $item = $post;
    $size = 'compact';
    require __DIR__ . '/../views/components/article-card.php';
endforeach;
