<?php
/**
 * views/components/sidebar.php
 * Left sidebar navigation
 */
$currentRoute   = $_GET['route'] ?? 'home';
$activeCategory = isset($_GET['slug']) ? rawurldecode($_GET['slug']) : '';
$siteName       = setting('site_title', 'randomous');

// Dynamic categories from DB
try {
    $navCats = db()->query("SELECT category FROM posts GROUP BY category ORDER BY COUNT(*) DESC LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $navCats = [];
}

$iconMap = [
    'News and Events'     => 'bi-megaphone',
    'Creator Stories'     => 'bi-person-badge',
    'Culture & Trends'    => 'bi-graph-up-arrow',
    'Inside YouTube'      => 'bi-youtube',
    'Business'            => 'bi-briefcase',
    'Tutorials'           => 'bi-mortarboard',
    'NEET'                => 'bi-heart-pulse',
    'JEE'                 => 'bi-calculator',
    'UPSC'                => 'bi-building',
    'Banking'             => 'bi-bank',
    'Current Affairs'     => 'bi-newspaper',
    'default'             => 'bi-hash',
];
?>
<aside id="sidebar" role="navigation" aria-label="Main navigation"
       class="fixed inset-y-0 left-0 w-72 bg-white z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col border-r border-gray-50">

    <!-- Brand -->
    <div class="h-20 px-8 flex items-center shrink-0">
        <a href="<?= SITE_URL ?>/" class="flex items-center gap-0.5 group cursor-pointer" aria-label="<?= e($siteName) ?> – Home">
            <span class="text-[22px] font-[900] tracking-tighter text-black uppercase">
                <?= brandLogoName($siteName) ?>
            </span>
        </a>
        <button id="close-sidebar-btn" aria-label="Close navigation menu"
                class="md:hidden ml-auto p-2 text-gray-400 hover:bg-gray-50 rounded-full transition-colors">
            <i class="bi bi-x-lg text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Nav content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar pt-2 pb-6">

        <!-- Desktop search -->
        <div class="hidden md:block px-6 mb-8">
            <form action="<?= SITE_URL ?>/search" method="GET" role="search" class="js-clean-search relative group">
                <label for="sidebar-search" class="sr-only">Search articles</label>
                <input type="text" id="sidebar-search" name="q"
                       placeholder="Search articles..."
                       value="<?= e($_GET['q'] ?? '') ?>"
                       maxlength="100"
                       class="w-full bg-gray-50 border border-transparent rounded-xl py-2.5 pl-10 pr-4 text-sm font-medium outline-none focus:bg-white focus:border-gray-200 transition-all">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-[#FF0033] transition-colors" aria-hidden="true"></i>
            </form>
        </div>

        <div class="space-y-9">
            <!-- Categories -->
            <section aria-labelledby="nav-categories-label">
                <h2 id="nav-categories-label" class="px-8 text-[11px] font-bold uppercase tracking-[0.15em] text-gray-400 mb-4">
                    Explore Topics
                </h2>
                <nav class="flex flex-col px-3 space-y-1">
                    <?php foreach ($navCats as $cat):
                        $icon    = $iconMap[$cat] ?? $iconMap['default'];
                        $isActive = ($currentRoute === 'category' && $activeCategory === $cat);
                    ?>
                    <a href="<?= e(categoryUrl($cat)) ?>"
                       class="relative flex items-center gap-3.5 px-5 py-3 rounded-xl text-[14px] font-semibold transition-all duration-200 group <?= $isActive ? 'bg-gray-50 text-black' : 'text-gray-500 hover:bg-gray-50 hover:text-black' ?>"
                       <?= $isActive ? 'aria-current="page"' : '' ?>>
                        <?php if ($isActive): ?>
                        <span class="absolute left-0 top-3 bottom-3 w-1 bg-[#FF0033] rounded-r-full" aria-hidden="true"></span>
                        <?php endif; ?>
                        <i class="<?= e($icon) ?> text-[17px] <?= $isActive ? 'text-[#FF0033]' : 'text-gray-400 group-hover:text-[#FF0033] transition-colors' ?>" aria-hidden="true"></i>
                        <?= e($cat) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
            </section>

            <!-- System links -->
            <section aria-labelledby="nav-system-label">
                <h2 id="nav-system-label" class="px-8 text-[11px] font-bold uppercase tracking-[0.15em] text-gray-400 mb-4">
                    System
                </h2>
                <nav class="flex flex-col px-3 space-y-1">
                    <a href="<?= SITE_URL ?>/"
                       class="flex items-center gap-3.5 px-5 py-3 rounded-xl text-[14px] font-semibold text-gray-500 hover:bg-gray-50 hover:text-black transition-all group">
                        <i class="bi bi-archive text-[17px] text-gray-400 group-hover:text-black transition-colors" aria-hidden="true"></i>
                        Archive
                    </a>
                    <a href="<?= SITE_URL ?>/admin/login.php"
                       class="flex items-center gap-3.5 px-5 py-3 rounded-xl text-[14px] font-semibold text-gray-500 hover:bg-gray-50 hover:text-black transition-all group">
                        <i class="bi bi-shield-lock text-[17px] text-gray-400 group-hover:text-black transition-colors" aria-hidden="true"></i>
                        Admin
                    </a>
                </nav>
            </section>
        </div>
    </div>
</aside>
