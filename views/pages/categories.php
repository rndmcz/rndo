<?php
/**
 * views/pages/categories.php
 */
$rawSlug = $_GET['slug'] ?? '';
$catName = sanitizeCategory(rawurldecode($rawSlug));

$iconMap = [
    'News and Events'  => 'bi-megaphone',
    'Creator Stories'  => 'bi-person-badge',
    'Culture & Trends' => 'bi-graph-up-arrow',
    'Inside YouTube'   => 'bi-youtube',
    'Business'         => 'bi-briefcase',
    'Tutorials'        => 'bi-mortarboard',
    'NEET'             => 'bi-heart-pulse',
    'JEE'              => 'bi-calculator',
    'UPSC'             => 'bi-building',
    'Banking'          => 'bi-bank',
    'Current Affairs'  => 'bi-newspaper',
    'default'          => 'bi-hash',
];

$limit = 15;

if (!empty($catName)) {
    // Single category
    $countStmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE category = ?");
    $countStmt->execute([$catName]);
    $totalCount = (int) $countStmt->fetchColumn();

    $stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE category = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$catName, $limit]);
    $results = $stmt->fetchAll();

    $meta_title  = e($catName) . ' Articles';
    $meta_desc   = "Browse all articles about " . $catName . " on " . setting('site_title', 'randomous');
    $canonical   = categoryUrl($catName);

    // Category schema
    $schema_json = json_encode([
        "@context"        => "https://schema.org",
        "@type"           => "CollectionPage",
        "name"            => $catName,
        "description"     => $meta_desc,
        "url"             => $canonical,
        "numberOfItems"   => $totalCount,
    ]);
} else {
    // All categories
    $categories  = db()->query("SELECT category, COUNT(*) as cnt FROM posts GROUP BY category ORDER BY category ASC")->fetchAll();
    $meta_title  = 'All Topics';
    $meta_desc   = 'Browse all topics on ' . setting('site_title', 'randomous');
    $canonical   = categoriesUrl();
    $schema_json = '';
}

$og_type = 'website';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-white">
<head>
    <?php require __DIR__ . '/../layouts/head.php'; ?>
</head>
<body class="text-gray-900 antialiased selection:bg-[#FF0033] selection:text-white">
<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="flex min-h-screen relative">

    <?php require __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Right sidebar -->
    <aside class="hidden lg:flex fixed inset-y-0 right-0 w-[320px] bg-white border-l border-gray-50 flex-col overflow-y-auto z-20 p-10"
           aria-label="Content discovery">
        <?php require __DIR__ . '/../components/search-sidebar.php'; ?>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 md:ml-[280px] lg:mr-[320px]">
        <?php require __DIR__ . '/../components/mobile-header.php'; ?>

        <main id="main-content" class="flex-1 w-full bg-white">

            <?php if (!empty($catName)): ?>
            <!-- Single Category View -->
            <header class="px-6 md:px-12 pt-4 md:pt-8 pb-8 text-center md:text-left">
                <nav aria-label="Breadcrumb" class="mb-4">
                    <ol class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <li><a href="<?= SITE_URL ?>/" class="hover:text-black transition-colors">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="<?= categoriesUrl() ?>" class="hover:text-black transition-colors">Topics</a></li>
                        <li aria-hidden="true">/</li>
                        <li class="text-black"><?= e($catName) ?></li>
                    </ol>
                </nav>
                <h1 class="text-3xl md:text-5xl font-[900] text-black uppercase tracking-tighter mb-1">
                    <?= e($catName) ?>
                </h1>
                <p class="text-gray-400 text-sm font-bold uppercase tracking-widest">
                    <?= $totalCount ?> Articles
                </p>
            </header>

            <div class="flex justify-center px-6 md:px-12">
                <div class="w-full max-w-3xl">
                    <div id="category-posts-container" class="space-y-12">
                        <?php foreach ($results as $post):
                            $item = $post;
                            $size = 'compact';
                        ?>
                            <?php require __DIR__ . '/../components/article-card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalCount > $limit): ?>
                    <div class="mt-16 flex justify-center" id="load-more-wrapper">
                        <button id="load-more-btn"
                                data-category="<?= e($catName) ?>"
                                data-offset="<?= $limit ?>"
                                class="w-full py-4 bg-white border-2 border-black text-black text-[11px] font-black uppercase tracking-[0.2em] hover:bg-black hover:text-white transition-all">
                            Load More
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- All Categories View -->
            <div class="px-6 md:px-12 pt-12 md:pt-20 pb-20 flex justify-center">
                <div class="w-full max-w-3xl">
                    <div class="mb-16">
                        <h1 class="text-5xl md:text-8xl font-[900] text-black uppercase tracking-tighter leading-[0.8] mb-4">TOPICS</h1>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-10">
                        <?php foreach ($categories as $c):
                            $iconClass = $iconMap[$c['category']] ?? $iconMap['default'];
                        ?>
                        <a href="<?= e(categoryUrl($c['category'])) ?>"
                           class="flex gap-5 group cursor-pointer items-start">
                            <div class="w-14 h-14 shrink-0 rounded-2xl bg-gray-50 flex items-center justify-center group-hover:bg-[#FF0033]/5 transition-colors duration-300" aria-hidden="true">
                                <i class="bi <?= e($iconClass) ?> text-2xl text-gray-300 group-hover:text-[#FF0033] transition-colors duration-300"></i>
                            </div>
                            <div class="flex flex-col gap-1 py-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-[#FF0033]">Topic</span>
                                    <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= (int)$c['cnt'] ?> Articles</span>
                                </div>
                                <h2 class="text-[14px] md:text-[18px] font-[900] leading-tight text-gray-900 group-hover:text-black uppercase transition-all">
                                    <?= e($c['category']) ?>
                                </h2>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mobile sidebar widgets -->
            <div class="lg:hidden mt-8 px-6 pt-8 pb-10">
                <h2 class="text-xs font-black uppercase tracking-[0.5em] text-gray-300 mb-12 text-center">Discover More</h2>
                <?php require __DIR__ . '/../components/search-sidebar.php'; ?>
            </div>
        </main>

        <?php require __DIR__ . '/../components/footer.php'; ?>
    </div>
</div>

<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm" aria-hidden="true"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-more-btn');
    if (!btn) return;
    btn.addEventListener('click', async () => {
        const category = btn.dataset.category;
        const offset   = parseInt(btn.dataset.offset, 10);
        btn.textContent = 'Loading…';
        btn.disabled = true;
        try {
            const res  = await fetch(`/api/load-more-category.php?category=${encodeURIComponent(category)}&offset=${offset}`);
            const html = await res.text();
            if (html.trim() === 'END') {
                document.getElementById('load-more-wrapper').innerHTML =
                    '<p class="text-center text-[10px] font-bold text-gray-200 uppercase tracking-widest mt-10">— End of Feed —</p>';
            } else {
                document.getElementById('category-posts-container').insertAdjacentHTML('beforeend', html);
                btn.dataset.offset = offset + 15;
                btn.textContent = 'Load More';
                btn.disabled = false;
            }
        } catch { btn.textContent = 'Error – try again'; btn.disabled = false; }
    });
});
</script>
</body>
</html>
