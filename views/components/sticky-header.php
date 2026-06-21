<?php
/**
 * views/components/sticky-header.php
 * Shown on article pages, slides in on scroll
 * Expects $post to be set
 */
?>
<header id="sticky-header"
        class="fixed top-0 left-0 md:left-[280px] w-full md:w-[calc(100%-280px)] bg-white/95 backdrop-blur-xl z-[100] border-b border-gray-100 shadow-sm transition-all duration-500 ease-in-out transform -translate-y-full opacity-0 hidden md:block"
        aria-label="Article navigation bar">
    <div class="max-w-5xl mx-auto px-8 h-16 flex items-center justify-between relative">

        <!-- TOC Toggle -->
        <button id="toc-toggle"
                aria-expanded="false"
                aria-controls="toc-drawer"
                aria-label="Table of contents"
                class="flex items-center gap-3 px-3 py-1.5 hover:bg-gray-50 rounded-xl transition-all group">
            <div class="flex flex-col gap-1 items-start" aria-hidden="true">
                <span class="w-4 h-0.5 bg-gray-400 group-hover:bg-black transition-all"></span>
                <span class="w-2.5 h-0.5 bg-black transition-all"></span>
                <span class="w-4 h-0.5 bg-gray-400 group-hover:bg-black transition-all"></span>
            </div>
            <span class="text-[11px] font-black uppercase tracking-[0.15em] text-gray-500 group-hover:text-black">Contents</span>
        </button>

        <!-- TOC Drawer -->
        <div id="toc-drawer"
             role="navigation"
             aria-label="Table of contents"
             class="absolute top-full left-8 w-72 bg-white border border-gray-100 rounded-2xl p-6 mt-2 transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-4 shadow-lg">
            <h2 class="text-[10px] font-black uppercase tracking-widest text-gray-300 mb-4">Table of Contents</h2>
            <nav id="toc-list" class="space-y-1 max-h-[60vh] overflow-y-auto custom-scrollbar" aria-label="Article sections">
                <!-- Populated by JS -->
            </nav>
        </div>

        <!-- Article title -->
        <div class="flex-1 px-10 text-center">
            <span class="text-[13px] font-extrabold text-youtube-dark tracking-tight line-clamp-1 uppercase">
                <?= e($post['title'] ?? '') ?>
            </span>
        </div>

        <!-- Back to top -->
        <button onclick="window.scrollTo({top:0,behavior:'smooth'})"
                aria-label="Scroll to top"
                class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-50 hover:bg-youtube-red hover:text-white transition-all shadow-sm">
            <i class="bi bi-arrow-up-short text-xl" aria-hidden="true"></i>
        </button>
    </div>
</header>
