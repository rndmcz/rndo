<?php
/**
 * views/components/search-sidebar.php
 * Popular, Recent, Top Topics widgets
 */
try {
    $sidebarPopular    = db()->query("SELECT title, slug, views, category FROM posts ORDER BY views DESC LIMIT 5")->fetchAll();
    $sidebarRecent     = db()->query("SELECT title, slug, created_at, views, category FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $sidebarCategories = db()->query("SELECT category, COUNT(*) as cnt FROM posts GROUP BY category ORDER BY cnt DESC LIMIT 8")->fetchAll();
} catch (Throwable $e) {
    $sidebarPopular = $sidebarRecent = $sidebarCategories = [];
}
?>
<div class="space-y-12">

    <!-- Popular Articles -->
    <section aria-labelledby="sidebar-popular-label">
        <h3 id="sidebar-popular-label" class="text-[10px] font-black uppercase tracking-[0.3em] text-black mb-8 flex items-center gap-2">
            <span class="w-1 h-2 bg-youtube-red rounded-full" aria-hidden="true"></span> Popular Articles
        </h3>
        <div class="space-y-6">
            <?php foreach ($sidebarPopular as $i => $p): ?>
            <div class="flex gap-4 group">
                <span class="text-2xl font-black text-gray-100 group-hover:text-black transition-colors shrink-0 min-w-[40px]" aria-hidden="true">
                    0<?= $i + 1 ?>
                </span>
                <div class="flex flex-col gap-0.5 justify-center">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-youtube-red"><?= e($p['category']) ?></span>
                        <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= formatViews((int)$p['views']) ?> Reads</span>
                    </div>
                    <h4 class="text-[12px] font-bold leading-tight text-gray-900 group-hover:text-youtube-red transition-all">
                        <a href="<?= e(articleUrl($p['slug'], $p['category'] ?? '')) ?>"><?= e($p['title']) ?></a>
                    </h4>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Recent Articles -->
    <section aria-labelledby="sidebar-recent-label">
        <h3 id="sidebar-recent-label" class="text-[10px] font-black uppercase tracking-[0.3em] text-black mb-8 flex items-center gap-2">
            <span class="w-1 h-2 bg-youtube-red rounded-full" aria-hidden="true"></span> Recent Articles
        </h3>
        <div class="space-y-6">
            <?php foreach ($sidebarRecent as $r): ?>
            <div class="flex gap-4 group cursor-pointer">
                <div class="flex flex-col items-center justify-start shrink-0 min-w-[40px] leading-none">
                    <span class="text-2xl font-black text-gray-100 group-hover:text-black transition-colors">
                        <?= date('d', strtotime($r['created_at'])) ?>
                    </span>
                    <span class="text-[8px] font-black uppercase text-gray-200 group-hover:text-youtube-red transition-colors mt-1">
                        <?= date('M', strtotime($r['created_at'])) ?>
                    </span>
                </div>
                <div class="flex flex-col gap-0.5 justify-center">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-youtube-red"><?= e($r['category']) ?></span>
                        <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= formatViews((int)$r['views']) ?> Reads</span>
                    </div>
                    <h4 class="text-[12px] font-bold leading-tight text-gray-900 group-hover:text-youtube-red transition-all">
                        <a href="<?= e(articleUrl($r['slug'], $r['category'] ?? '')) ?>"><?= e($r['title']) ?></a>
                    </h4>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Top Topics -->
    <section aria-labelledby="sidebar-topics-label">
        <h3 id="sidebar-topics-label" class="text-[10px] font-black uppercase tracking-[0.3em] text-black mb-8 flex items-center gap-2">
            <span class="w-1 h-2 bg-youtube-red rounded-full" aria-hidden="true"></span> Top Topics
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-1 gap-x-5 gap-y-10">
            <?php foreach ($sidebarCategories as $idx => $c): ?>
            <a href="<?= e(categoryUrl($c['category'])) ?>" class="flex gap-4 group cursor-pointer">
                <span class="text-3xl font-black text-gray-100 group-hover:text-black transition-colors duration-300 tabular-nums shrink-0 min-w-[40px]" aria-hidden="true">
                    <?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?>
                </span>
                <div class="flex flex-col gap-0.5 justify-center">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-youtube-red">Topic</span>
                        <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= (int)$c['cnt'] ?></span>
                    </div>
                    <h4 class="text-[12px] font-[900] leading-snug text-gray-900 group-hover:text-youtube-red uppercase transition-all">
                        <?= e($c['category']) ?>
                    </h4>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="<?= categoriesUrl() ?>" class="text-[9px] font-black uppercase tracking-widest text-black hover:text-youtube-red transition-colors">
                View all topics →
            </a>
        </div>
    </section>
</div>
