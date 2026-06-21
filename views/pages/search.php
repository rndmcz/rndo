<?php
/**
 * views/pages/search.php
 */
$query      = sanitizeSearch($_GET['q'] ?? '');
$limit      = 6;
$results    = [];
$totalCount = 0;

if (!empty($query)) {
    $term      = '%' . $query . '%';
    $countStmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE ? OR excerpt LIKE ? OR content LIKE ?");
    $countStmt->execute([$term, $term, $term]);
    $totalCount = (int) $countStmt->fetchColumn();

    $stmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE title LIKE ? OR excerpt LIKE ? OR content LIKE ? ORDER BY (CASE WHEN title LIKE ? THEN 1 WHEN excerpt LIKE ? THEN 2 ELSE 3 END), created_at DESC LIMIT ?");
    $stmt->execute([$term, $term, $term, $term, $term, $limit]);
    $results = $stmt->fetchAll();
}

$meta_title = $query ? 'Search: ' . e($query) : 'Search';
$meta_desc  = $query ? "Found {$totalCount} results for \"" . e($query) . "\" on " . setting('site_title', 'randomous') : 'Search all articles on ' . setting('site_title', 'randomous');
$canonical  = searchUrl($query);
$og_type    = 'website';
$schema_json = '';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-white">
<head>
    <?php require __DIR__ . '/../layouts/head.php'; ?>
    <!-- No-index search result pages to avoid duplicate content -->
    <?php if ($query): ?><meta name="robots" content="noindex, follow"><?php endif; ?>
</head>
<body class="text-gray-900 antialiased selection:bg-youtube-red selection:text-white overflow-x-hidden">
<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="flex min-h-screen relative">

    <?php require __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Right sidebar -->
    <aside class="hidden lg:flex fixed inset-y-0 right-0 w-[320px] bg-white border-l border-gray-50 flex-col overflow-y-auto z-20 p-10"
           aria-label="Content discovery">
        <?php require __DIR__ . '/../components/search-sidebar.php'; ?>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 md:ml-[280px] lg:mr-[320px]">
        <?php require __DIR__ . '/../components/mobile-header.php'; ?>

        <main id="main-content" class="flex-1 w-full bg-white">

            <!-- Sticky search bar -->
            <div class="sticky top-[56px] md:top-0 z-30 bg-white/95 backdrop-blur-md flex justify-center px-4 md:px-6 pt-0 pb-1 md:pt-6 md:pb-2">
                <div class="w-full max-w-2xl flex items-center gap-2 md:gap-4">
                    <form action="<?= SITE_URL ?>/search" method="GET" role="search" class="js-clean-search flex flex-1 items-center">
                        <label for="main-search-input" class="sr-only">Search articles</label>
                        <div class="flex flex-1 items-center bg-white border border-gray-200 rounded-lg overflow-hidden focus-within:border-black focus-within:ring-1 focus-within:ring-black transition-all">
                            <input type="text" id="main-search-input" name="q"
                                   value="<?= e($query) ?>"
                                   placeholder="Search articles…"
                                   maxlength="100"
                                   autofocus
                                   class="flex-1 bg-transparent px-4 md:px-6 py-2 md:py-3 text-sm md:text-base font-bold outline-none placeholder-gray-300">
                            <button type="submit" aria-label="Submit search" class="px-5 md:px-8 py-2 md:py-3 hover:bg-gray-100 transition-colors group">
                                <i class="bi bi-search text-gray-400 group-hover:text-black transition-colors" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="flex justify-center px-4 md:px-6 py-4 md:py-8">
                <div class="w-full max-w-3xl">

                    <?php if ($query): ?>
                    <div class="flex items-center justify-between pb-2 mb-4">
                        <h1 class="text-[10px] font-black uppercase tracking-widest text-black">
                            Results for "<?= e($query) ?>"
                        </h1>
                        <span class="text-[9px] font-bold text-gray-400 uppercase"><?= $totalCount ?> Results</span>
                    </div>

                    <div id="search-results-container" class="space-y-6">
                        <?php if (!empty($results)):
                            foreach ($results as $post):
                                $item = $post;
                                $size = 'compact';
                        ?>
                            <?php require __DIR__ . '/../components/article-card.php'; ?>
                        <?php
                            endforeach;
                        else: ?>
                            <div class="py-20 text-center text-gray-400 italic" role="status">
                                No results found for "<?= e($query) ?>". Try different keywords.
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($totalCount > $limit): ?>
                    <div class="mt-12 flex justify-center" id="load-more-wrapper">
                        <button id="load-more-btn"
                                data-query="<?= e($query) ?>"
                                data-offset="<?= $limit ?>"
                                class="w-full md:w-auto px-12 py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-youtube-red transition-all shadow-xl">
                            Load More Results
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="py-16 md:py-20">
                        <p class="text-3xl md:text-7xl font-black text-gray-100 uppercase tracking-tighter leading-[0.9]" aria-label="Start typing to search">
                            Type to search.
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Mobile sidebar widgets -->
                    <div class="lg:hidden mt-8 pt-8 pb-10">
                        <h2 class="text-xs font-black uppercase tracking-[0.5em] text-gray-300 mb-12 text-center">Discover More</h2>
                        <?php require __DIR__ . '/../components/search-sidebar.php'; ?>
                    </div>
                </div>
            </div>
        </main>

        <?php require __DIR__ . '/../components/footer.php'; ?>
    </div>
</div>

<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm" aria-hidden="true"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn       = document.getElementById('load-more-btn');
    const container = document.getElementById('search-results-container');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const q      = btn.dataset.query;
        const offset = parseInt(btn.dataset.offset, 10);
        btn.textContent = 'Loading…';
        btn.disabled = true;
        try {
            const res  = await fetch(`/api/load-more-search.php?q=${encodeURIComponent(q)}&offset=${offset}`);
            const html = await res.text();
            if (html.trim() === 'END') {
                document.getElementById('load-more-wrapper').innerHTML =
                    '<p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest text-center">End of results</p>';
            } else {
                container.insertAdjacentHTML('beforeend', html);
                btn.dataset.offset = offset + 6;
                btn.textContent = 'Load More Results';
                btn.disabled = false;
            }
        } catch { btn.textContent = 'Error – try again'; btn.disabled = false; }
    });
});
</script>
</body>
</html>
