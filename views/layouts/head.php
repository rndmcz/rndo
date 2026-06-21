<?php
/**
 * views/layouts/head.php
 * Dynamic <head> with full SEO, OG, Twitter Cards, Schema
 *
 * Expected variables (set before including):
 * $meta_title   - Page title
 * $meta_desc    - Meta description
 * $canonical    - Canonical URL
 * $schema_json  - JSON-LD schema string (optional)
 * $og_type      - 'article' | 'website'
 * $og_image     - OG image URL (optional)
 */

$siteName    = setting('site_title', 'randomous');
$siteDesc    = setting('site_description', 'India\'s leading educational platform for competitive exam preparation.');
$gaId        = setting('google_analytics_id', '');
$favicon     = setting('site_favicon', '');
$canonical   = $canonical ?? SITE_URL . '/';
$ogType      = $og_type  ?? 'website';
$ogImage     = $og_image ?? '';
$pageTitle   = isset($meta_title) ? e($meta_title) . ' – ' . e($siteName) : e($siteName);
$pageDesc    = isset($meta_desc)  ? e($meta_desc)  : e($siteDesc);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Primary SEO -->
<title><?= $pageTitle ?></title>
<meta name="description" content="<?= $pageDesc ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Robots -->
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

<!-- Open Graph -->
<meta property="og:type"        content="<?= e($ogType) ?>">
<meta property="og:title"       content="<?= $pageTitle ?>">
<meta property="og:description" content="<?= $pageDesc ?>">
<meta property="og:url"         content="<?= e($canonical) ?>">
<meta property="og:site_name"   content="<?= e($siteName) ?>">
<meta property="og:locale"      content="en_IN">
<?php if ($ogImage): ?>
<meta property="og:image"       content="<?= e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= $pageTitle ?>">
<meta name="twitter:description" content="<?= $pageDesc ?>">
<?php if ($ogImage): ?>
<meta name="twitter:image"       content="<?= e($ogImage) ?>">
<?php endif; ?>

<!-- Favicon -->
<?php if ($favicon): ?>
<link rel="icon" type="image/x-icon" href="<?= e($favicon) ?>">
<?php else: ?>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='https://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='50' fill='%23FF0033'/></svg>">
<?php endif; ?>

<!-- Sitemap reference -->
<link rel="sitemap" type="application/xml" href="<?= SITE_URL ?>/sitemap.php">

<!-- Schema.org JSON-LD -->
<?php if (!empty($schema_json)): ?>
<script type="application/ld+json"><?= $schema_json ?></script>
<?php endif; ?>

<!-- Website + SearchAction Schema (always) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "<?= e($siteName) ?>",
  "url": "<?= SITE_URL ?>",
  "description": "<?= e($siteDesc) ?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "<?= SITE_URL ?>/search/{search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>

<script>
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form.js-clean-search').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var input = form.querySelector('input[name="q"]');
                if (!input) {
                    return;
                }
                var query = input.value.trim();
                if (query === '') {
                    event.preventDefault();
                    window.location.href = '<?= SITE_URL ?>/search';
                    return;
                }
                event.preventDefault();
                window.location.href = '<?= SITE_URL ?>/search/' + encodeURIComponent(query);
            });
        });
    });
}
</script>

<!-- Google Analytics -->
<?php if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($gaId) ?>', { anonymize_ip: true });
</script>
<?php endif; ?>

<!-- Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Roboto+Mono:wght@500&family=Montserrat:wght@900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Tailwind + Typography -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
<!-- Marked.js for AI chat markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js" defer></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
                mono: ['Roboto Mono', 'monospace'],
                logo: ['Montserrat', 'sans-serif'],
            },
            colors: {
                youtube: { red: '#FF0000', dark: '#1A1A1A', bg: '#FFF5F5' }
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        color: theme('colors.gray.800'),
                        a: { color: theme('colors.blue.600'), '&:hover': { color: theme('colors.blue.800') } },
                        h2: { color: theme('colors.black'), fontWeight: '800', marginTop: '2.5rem', marginBottom: '1rem', letterSpacing: '-0.02em' },
                        h3: { color: theme('colors.black'), fontWeight: '700', marginTop: '2rem', marginBottom: '0.8rem' },
                    },
                },
            }),
        }
    }
}
</script>

<style>
    html { scroll-behavior: smooth; }
    :root { --yt-red: #FF0000; }
    input:focus { outline: none !important; }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    h2[id], h3[id], h1[id] { scroll-margin-top: 100px; }
    .toc-collapsed { max-height: 0 !important; opacity: 0 !important; overflow: hidden; }

    .logo-group:hover .bar-1 { width: 100% !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #f3f4f6; border-radius: 10px; }

    .bar { transition: height 0.2s ease; height: 4px; }
    .playing .bar:nth-child(1) { animation: equalize 1s infinite alternate; }
    .playing .bar:nth-child(2) { animation: equalize 0.8s infinite alternate-reverse; }
    .playing .bar:nth-child(3) { animation: equalize 1.2s infinite alternate; }
    .playing .bar:nth-child(4) { animation: equalize 0.9s infinite alternate-reverse; }
    @keyframes equalize { 0% { height: 4px; } 100% { height: 16px; } }

    @keyframes liquid-flow {
        0%   { background-position: 0% 0%; }
        50%  { background-position: 100% 100%; }
        100% { background-position: 0% 0%; }
    }
    .multi-mesh-hero {
        background-color: #fffdf7;
        background-image:
            radial-gradient(at 0% 0%, #ffe8c0 0px, transparent 50%),
            radial-gradient(at 50% 0%, #fff0ba 0px, transparent 50%),
            radial-gradient(at 100% 0%, #d0f5e8 0px, transparent 50%),
            radial-gradient(at 0% 50%, #d8eeff 0px, transparent 50%),
            radial-gradient(at 50% 50%, #fff8e7 0px, transparent 50%),
            radial-gradient(at 100% 50%, #ffd6ec 0px, transparent 50%),
            radial-gradient(at 0% 100%, #c8f0e0 0px, transparent 50%);
        background-size: 400% 400%;
        animation: liquid-flow 30s ease infinite;
    }

    /* AI chat styles */
    .ai-response-content { color: #1f2937; line-height: 1.6; }
    .ai-response-content strong { font-weight: 800; color: #000; }
    .ai-response-content ul { list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
    .ai-response-content p { margin-bottom: 0.8rem; }
    .typing-active::after { content: '▊'; animation: blink 0.8s infinite; margin-left: 2px; color: #6366f1; font-size: 12px; vertical-align: middle; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    .shimmer-line { height: 10px; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite linear; border-radius: 4px; margin-bottom: 8px; }
    @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    .ai-pulse { animation: pulse-indigo 2s infinite; color: #6366f1; }
    @keyframes pulse-indigo { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.7; } }

    /* Skip link for accessibility */
    .skip-link { position: absolute; top: -40px; left: 0; background: #FF0000; color: white; padding: 8px; z-index: 9999; font-weight: bold; }
    .skip-link:focus { top: 0; }
</style>
