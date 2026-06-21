<?php
/**
 * llms.php → direct endpoint without rewrite rules
 * Helps AI models understand the site structure
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/helpers/security.php';

header('Content-Type: text/plain; charset=UTF-8');

$siteName = setting('site_title', 'randomous');
$siteDesc = setting('site_description', 'Educational platform for competitive exam preparation in India.');
$posts    = db()->query("SELECT title, slug, excerpt, category FROM posts ORDER BY views DESC LIMIT 50")->fetchAll();
$cats     = db()->query("SELECT DISTINCT category FROM posts ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

echo "# {$siteName} – LLM Site Map\n\n";
echo "> {$siteDesc}\n\n";
echo "## About\n";
echo "{$siteName} is an educational content platform covering competitive exams, study materials, and career guidance for students in India.\n\n";
echo "## Topics Covered\n";
foreach ($cats as $cat) {
    echo "- {$cat}\n";
}
echo "\n## Featured Articles (by popularity)\n";
foreach ($posts as $post) {
    echo "- [{$post['title']}](" . articleUrl($post['slug'], $post['category']) . ") – {$post['category']}\n";
    if (!empty($post['excerpt'])) {
        echo "  {$post['excerpt']}\n";
    }
}
echo "\n## Key URLs\n";
echo "- Homepage: " . SITE_URL . "/\n";
echo "- All Topics: " . categoriesUrl() . "\n";
echo "- Sitemap: " . SITE_URL . "/sitemap.php\n";
