<?php
/**
 * views/components/mobile-header.php
 */
$currentRoute  = $_GET['route'] ?? 'home';
$isSearchPage  = ($currentRoute === 'search');
$siteName      = setting('site_title', 'randomous');
?>
<!-- Mobile Header -->
<header class="md:hidden sticky top-0 z-30 bg-white flex flex-col border-b border-gray-100" role="banner">
    <div class="px-5 py-4 flex items-center justify-between relative">

        <!-- Menu toggle -->
        <button id="mobile-menu-btn" aria-label="Open navigation menu" aria-expanded="false" aria-controls="sidebar"
                class="text-gray-800 transition-transform active:scale-90">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Logo -->
        <a href="<?= SITE_URL ?>/" class="flex items-center gap-0.5 group cursor-pointer" aria-label="<?= e($siteName) ?> – Home">
            <span class="text-[18px] font-[900] tracking-tighter text-black uppercase"><?= brandLogoName($siteName) ?></span>
        </a>

        <!-- Search toggle (hidden on search page) -->
        <?php if (!$isSearchPage): ?>
        <button id="mobile-search-toggle" aria-label="Toggle search" aria-expanded="false"
                class="text-gray-800 transition-transform active:scale-90">
            <i id="search-icon-state" class="bi bi-search text-xl" aria-hidden="true"></i>
        </button>
        <?php else: ?>
        <div class="w-6" aria-hidden="true"></div>
        <?php endif; ?>
    </div>

    <?php if (!$isSearchPage): ?>
    <!-- Expandable search bar -->
    <div id="mobile-search-bar" class="hidden px-5 pb-4">
        <form action="<?= SITE_URL ?>/search" method="GET" role="search" class="js-clean-search flex items-center">
            <label for="mobile-search-input" class="sr-only">Search articles</label>
            <div class="flex flex-1 items-center bg-white border border-gray-200 rounded-lg overflow-hidden focus-within:border-black focus-within:ring-1 focus-within:ring-black transition-all">
                <input type="text" id="mobile-search-input" name="q"
                       value="<?= e($_GET['q'] ?? '') ?>"
                       placeholder="Search articles..."
                       maxlength="100"
                       class="flex-1 bg-transparent px-4 py-2 text-sm font-bold outline-none placeholder-gray-300">
                <button type="submit" aria-label="Submit search" class="px-5 py-2 hover:bg-gray-100 transition-colors group">
                    <i class="bi bi-search text-gray-400 group-hover:text-black transition-colors" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menuBtn   = document.getElementById('mobile-menu-btn');
    const closeBtn  = document.getElementById('close-sidebar-btn');
    const overlay   = document.getElementById('sidebar-overlay');
    const sidebar   = document.getElementById('sidebar');

    function openSidebar() {
        if (!overlay || !sidebar) return;
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.add('opacity-100'), 10);
        sidebar.classList.remove('-translate-x-full');
        document.body.style.overflow = 'hidden';
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
    }
    function closeSidebar() {
        if (!overlay || !sidebar) return;
        overlay.classList.remove('opacity-100');
        sidebar.classList.add('-translate-x-full');
        setTimeout(() => { overlay.classList.add('hidden'); document.body.style.overflow = ''; }, 300);
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    }

    if (menuBtn)  menuBtn.addEventListener('click', openSidebar);
    if (overlay)  overlay.addEventListener('click', closeSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

    // Search toggle
    const searchToggle = document.getElementById('mobile-search-toggle');
    const searchBar    = document.getElementById('mobile-search-bar');
    const searchIcon   = document.getElementById('search-icon-state');
    const searchInput  = document.getElementById('mobile-search-input');

    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function () {
            const hidden = searchBar.classList.contains('hidden');
            searchBar.classList.toggle('hidden', !hidden);
            if (searchIcon) {
                searchIcon.classList.toggle('bi-search', !hidden);
                searchIcon.classList.toggle('bi-x-lg', hidden);
            }
            searchToggle.setAttribute('aria-expanded', hidden ? 'true' : 'false');
            if (hidden && searchInput) searchInput.focus();
        });
    }
});
</script>
