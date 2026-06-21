<?php
/**
 * api/load-more-search.php
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }

$query  = sanitizeSearch($_GET['q'] ?? '');
$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit  = 6;

if (empty($query)) { http_response_code(400); exit; }

$term = '%' . $query . '%';
try {
    $stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE title LIKE ? OR excerpt LIKE ? OR content LIKE ? ORDER BY (CASE WHEN title LIKE ? THEN 1 WHEN excerpt LIKE ? THEN 2 ELSE 3 END), created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$term, $term, $term, $term, $term, $limit, $offset]);
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
