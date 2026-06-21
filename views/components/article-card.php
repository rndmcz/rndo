<?php
/**
 * views/components/article-card.php
 * Reusable article card for home feed and search results.
 * Expects $item array with: title, slug, excerpt, category, content, created_at
 * Optional $size: 'large' (home feed) | 'compact' (search/category)
 */
$size    = $size    ?? 'large';
$reading = readingTime($item['content'] ?? '');
$large   = ($size === 'large');
?>
<article class="group relative flex flex-col items-start animate-in fade-in slide-in-from-bottom-4 duration-700">

    <!-- Meta row -->
    <div class="flex items-center gap-2 mb-<?= $large ? '2' : '1' ?>">
        <div class="w-3 h-3 bg-youtube-red rounded-full flex items-center justify-center" aria-hidden="true">
            <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
        </div>
        <span class="text-[10px] font-black uppercase tracking-widest text-black">
            <?= e($item['category']) ?>
        </span>
        <span class="text-gray-300 text-[10px]" aria-hidden="true">•</span>
        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
            <time datetime="<?= date('Y-m-d', strtotime($item['created_at'])) ?>"><?= $reading ?> min read</time>
        </span>
    </div>

    <!-- Title -->
    <h<?= $large ? '2' : '3' ?> class="<?= $large ? 'text-l md:text-2xl font-black font-[800]' : 'text-xl md:text-2xl font-black' ?> leading-tight tracking-tight text-gray-900 group-hover:text-youtube-red transition-colors mb-1">
        <a href="<?= e(articleUrl($item['slug'], $item['category'] ?? '')) ?>" class="after:absolute after:inset-0">
            <?= e($item['title']) ?>
        </a>
    </h<?= $large ? '2' : '3' ?>>

    <!-- Excerpt -->
    <p class="text-gray-500 text-xs md:text-sm leading-relaxed line-clamp-2 max-w-2xl">
        <?= e($item['excerpt'] ?? '') ?>
    </p>
</article>
