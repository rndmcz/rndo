<?php
/**
 * robots.php → direct endpoint without rewrite rules
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

header('Content-Type: text/plain; charset=UTF-8');
?>
User-agent: *
Allow: /
Disallow: /api/
Disallow: /config/
Disallow: /storage/
Disallow: /app/
Disallow: /search

# AI crawlers (allow for AI search optimization)
User-agent: GPTBot
Allow: /

User-agent: Claude-Web
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Googlebot
Allow: /
Crawl-delay: 1

User-agent: Bingbot
Allow: /
Crawl-delay: 2

Sitemap: <?= SITE_URL ?>/sitemap.php
Sitemap: <?= SITE_URL ?>/llms.php
