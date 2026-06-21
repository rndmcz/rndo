<?php
/**
 * views/pages/homepage.php
 */
$limit      = 15;
$meta_title = 'Latest Articles';
$canonical  = SITE_URL . '/';
$og_type    = 'website';

// Fetch initial feed
$stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts ORDER BY created_at DESC LIMIT :lim");
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$feeds = $stmt->fetchAll();

// Popular (last 30 days, fallback to all-time)
$popularSql = "SELECT title, slug, views, created_at, category FROM posts WHERE created_at >= " . dbDateExpression('30_days_ago') . " ORDER BY views DESC LIMIT 10";
$popStmt = db()->prepare($popularSql);
$popStmt->execute();
$popular = $popStmt->fetchAll();
if (count($popular) < 3) {
    $popular = db()->query("SELECT title, slug, views, created_at, category FROM posts ORDER BY views DESC LIMIT 10")->fetchAll();
}

// Top categories
$topCategories = db()->query("SELECT category, COUNT(*) as cnt FROM posts GROUP BY category ORDER BY cnt DESC LIMIT 10")->fetchAll();

// Total for load-more button
$totalPosts = (int) db()->query("SELECT COUNT(*) FROM posts")->fetchColumn();

// Site info for hero
$siteTitle = setting('site_title', 'randomous');
$siteDesc  = setting('site_description', 'India\'s leading educational platform for competitive exam preparation.');
$words     = explode(' ', $siteTitle, 2);

// Website + Organization schema
$schema_json = json_encode([
    "@context" => "https://schema.org",
    "@type"    => "Organization",
    "name"     => $siteTitle,
    "url"      => SITE_URL,
    "description" => $siteDesc,
]);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-white">
<head>
    <?php require __DIR__ . '/../layouts/head.php'; ?>
</head>
<body class="text-gray-900 antialiased overflow-x-hidden selection:bg-youtube-red selection:text-white">
<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="flex flex-col md:flex-row min-h-screen w-full relative">

    <?php require __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col w-full md:ml-[280px]">
        <?php require __DIR__ . '/../components/mobile-header.php'; ?>

        <main id="main-content" class="flex-1 relative flex flex-col w-full bg-white">

            <!-- Hero -->
            <section class="w-full font-sans multi-mesh-hero pt-12 md:pt-24 pb-12 md:pb-24 px-6 md:px-12 border-b border-gray-100 relative overflow-hidden" aria-label="Site introduction">
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image:url('https://grainy-gradients.vercel.app/noise.svg');" aria-hidden="true"></div>
                <div class="max-w-6xl mx-auto relative z-10">
                    <div class="w-full max-w-3xl">
                        <div class="mb-6">
                            <span class="inline-block bg-black text-white text-[10px] font-black uppercase tracking-[0.3em] px-5 py-2 rounded-full">Welcome</span>
                        </div>
                        <h1 class="text-[48px] md:text-[64px] lg:text-[84px] font-black leading-[0.95] uppercase tracking-tighter text-black mb-6">
                            <?= count($words) > 1 ? e($words[0]) . '<br>' . e($words[1]) : e($siteTitle) ?>
                        </h1>
                        <p class="text-[18px] md:text-[22px] leading-relaxed text-black/80 font-medium tracking-tight max-w-xl">
                            <?= e($siteDesc) ?>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Latest Articles Feed -->
            <section class="max-w-4xl mx-auto px-6 md:px-12 py-8 md:py-10" aria-labelledby="latest-heading">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-1.5 h-4 bg-youtube-red rounded-full" aria-hidden="true"></div>
                    <h2 id="latest-heading" class="text-[11px] font-black uppercase tracking-[0.4em] text-black">Latest Articles</h2>
                </div>

                <div id="home-feed-container" class="space-y-8 md:space-y-12">
                    <?php foreach ($feeds as $feed):
                        $item = $feed;
                        $size = 'large';
                    ?>
                        <?php require __DIR__ . '/../components/article-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPosts > $limit): ?>
                <div class="mt-10 flex justify-center" id="load-more-wrapper">
                    <button id="load-more-btn"
                            data-offset="<?= $limit ?>"
                            aria-label="Load more articles"
                            class="px-10 py-4 border border-black text-[10px] font-black uppercase tracking-[0.3em] hover:bg-black hover:text-white transition-all active:scale-95 shadow-xl">
                        Load More
                    </button>
                </div>
                <?php endif; ?>
            </section>

            <!-- Popular Articles -->
            <section class="max-w-4xl mx-auto px-6 md:px-10 py-5 md:py-6" aria-labelledby="popular-heading">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-1.5 h-4 bg-youtube-red rounded-full" aria-hidden="true"></div>
                    <h2 id="popular-heading" class="text-[11px] font-black uppercase tracking-[0.4em] text-black">Popular Articles</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                    <?php foreach ($popular as $idx => $p): ?>
                    <div class="flex gap-5 group cursor-pointer">
                        <span class="text-3xl font-black text-gray-100 group-hover:text-black transition-colors duration-300 tabular-nums" aria-hidden="true">
                            <?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?>
                        </span>
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-youtube-red"><?= e($p['category']) ?></span>
                                <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= formatViews((int)$p['views']) ?> Reads</span>
                            </div>
                            <h3 class="text-[14px] font-bold leading-snug text-gray-900 group-hover:text-youtube-red transition-all">
                                <a href="<?= e(articleUrl($p['slug'], $p['category'] ?? '')) ?>"><?= e($p['title']) ?></a>
                            </h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Top Categories -->
                <div class="flex items-center gap-3 mb-12 mt-12">
                    <div class="w-1.5 h-4 bg-youtube-red rounded-full" aria-hidden="true"></div>
                    <h2 class="text-[11px] font-black uppercase tracking-[0.4em] text-black">Top Categories</h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-x-5 gap-y-10">
                    <?php foreach ($topCategories as $idx => $c): ?>
                    <a href="<?= e(categoryUrl($c['category'])) ?>" class="flex gap-3 group cursor-pointer">
                        <span class="text-3xl font-black text-gray-100 group-hover:text-black transition-colors duration-300 tabular-nums" aria-hidden="true">
                            <?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?>
                        </span>
                        <div class="flex flex-col gap-0.5 justify-center">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-youtube-red">Topic</span>
                                <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= (int)$c['cnt'] ?></span>
                            </div>
                            <h3 class="text-[14px] font-[900] leading-snug text-gray-900 group-hover:text-youtube-red uppercase transition-all">
                                <?= e($c['category']) ?>
                            </h3>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 pt-4 pb-8 flex justify-between items-center">
                    <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest">Browse content by topic</p>
                    <a href="<?= categoriesUrl() ?>" class="text-[9px] font-black uppercase tracking-widest text-black hover:text-youtube-red transition-colors">View all categories →</a>
                </div>
            </section>
        </main>

        <?php require __DIR__ . '/../components/footer.php'; ?>
    </div>
</div>

<!-- Sidebar overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm" aria-hidden="true"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loadMoreBtn   = document.getElementById('load-more-btn');
    const feedContainer = document.getElementById('home-feed-container');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async () => {
            const offset = parseInt(loadMoreBtn.dataset.offset, 10);
            loadMoreBtn.textContent = 'Loading…';
            loadMoreBtn.disabled = true;
            try {
                const res  = await fetch(`/api/load-more-home.php?offset=${offset}`);
                const html = await res.text();
                if (html.trim() === 'END') {
                    document.getElementById('load-more-wrapper').innerHTML =
                        '<span class="text-[10px] font-black uppercase tracking-widest text-gray-300">End of Feed</span>';
                } else {
                    feedContainer.insertAdjacentHTML('beforeend', html);
                    loadMoreBtn.dataset.offset = offset + 15;
                    loadMoreBtn.textContent = 'Load More';
                    loadMoreBtn.disabled = false;
                }
            } catch {
                loadMoreBtn.textContent = 'Error – try again';
                loadMoreBtn.disabled = false;
            }
        });
    }
});
</script>
</body>
</html>
