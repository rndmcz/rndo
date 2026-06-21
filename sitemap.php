<?php
/**
 * sitemap.php → direct endpoint without rewrite rules
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/helpers/security.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$posts = [];
$categories = [];

// Try to fetch posts (with fallback if updated_at column is missing)
try {
    $posts = db()->query("SELECT slug, created_at, updated_at FROM posts ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    try {
        // Fallback query if 'updated_at' does not exist in the database schema
        $posts = db()->query("SELECT slug, created_at FROM posts ORDER BY created_at DESC")->fetchAll();
    } catch (PDOException $ex) {
        error_log("[Sitemap Error] " . $ex->getMessage());
    }
}

// Try to fetch categories
try {
    $categories = db()->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL AND category != '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("[Sitemap Categories Error] " . $e->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Home -->
    <url>
        <loc><?= e(SITE_URL) ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
        <lastmod><?= date('Y-m-d') ?></lastmod>
    </url>

    <!-- Category index -->
    <url>
        <loc><?= e(categoriesUrl()) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <lastmod><?= date('Y-m-d') ?></lastmod>
    </url>

    <!-- Categories -->
    <?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= e(categoryUrl($cat)) ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>

    <!-- Articles -->
    <?php foreach ($posts as $post):
        if (empty($post['slug'])) continue;
        
        // Safely determine the last modified date
        $lastmod = !empty($post['updated_at']) ? $post['updated_at'] : (!empty($post['created_at']) ? $post['created_at'] : date('Y-m-d'));
        $dateObj = strtotime($lastmod);
        $formattedDate = $dateObj ? date('Y-m-d', $dateObj) : date('Y-m-d');
    ?>
    <url>
        <loc><?= e(articleUrl($post['slug'], $post['category'] ?? '')) ?></loc>
        <lastmod><?= e($formattedDate) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

</urlset>