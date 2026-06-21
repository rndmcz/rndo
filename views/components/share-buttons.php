<?php
/**
 * views/components/share-buttons.php
 * Expects $post array to be set
 */
$shareUrl   = e(articleUrl($post['slug'], $post['category'] ?? ''));
$shareTitle = urlencode($post['title']);
$shareLink  = urlencode(articleUrl($post['slug'], $post['category'] ?? ''));
$rawUrl     = articleUrl($post['slug'], $post['category'] ?? '');

$platforms = [
    ['icon' => 'bi-twitter-x',  'color' => 'hover:text-black',       'label' => 'Share on X (Twitter)',   'url' => "https://twitter.com/intent/tweet?url={$shareLink}&text={$shareTitle}"],
    ['icon' => 'bi-facebook',   'color' => 'hover:text-blue-600',    'label' => 'Share on Facebook',      'url' => "https://www.facebook.com/sharer/sharer.php?u={$shareLink}"],
    ['icon' => 'bi-whatsapp',   'color' => 'hover:text-green-500',   'label' => 'Share on WhatsApp',      'url' => "https://api.whatsapp.com/send?text={$shareTitle}%20{$shareLink}"],
    ['icon' => 'bi-reddit',     'color' => 'hover:text-orange-600',  'label' => 'Share on Reddit',        'url' => "https://www.reddit.com/submit?url={$shareLink}&title={$shareTitle}"],
];
?>

<!-- Desktop sidebar share -->
<div class="hidden md:flex sticky top-32 flex-col items-center space-y-5" role="complementary" aria-label="Share article">
    <span class="text-[10px] font-black tracking-[0.3em] text-gray-300 uppercase -rotate-90 mb-10 whitespace-nowrap" aria-hidden="true">Share</span>
    <?php foreach ($platforms as $p): ?>
    <a href="<?= e($p['url']) ?>" target="_blank" rel="noopener noreferrer"
       aria-label="<?= e($p['label']) ?>"
       class="text-gray-400 <?= $p['color'] ?> transition-all">
        <i class="bi <?= e($p['icon']) ?> text-[24px]" aria-hidden="true"></i>
    </a>
    <?php endforeach; ?>
    <button onclick="copyLink(this)"
            data-url="<?= $shareUrl ?>"
            aria-label="Copy article link"
            class="text-gray-400 hover:text-youtube-red transition-all">
        <i class="bi bi-link-45deg text-[20px]" aria-hidden="true"></i>
    </button>
</div>

<!-- Mobile inline share -->
<div class="md:hidden pt-10 pb-10" role="complementary" aria-label="Share article">
    <div class="flex flex-col items-center">
        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-300 mb-6">Share this Article</span>
        <div class="flex items-center justify-center gap-4">
            <?php foreach ($platforms as $p): ?>
            <a href="<?= e($p['url']) ?>" target="_blank" rel="noopener noreferrer"
               aria-label="<?= e($p['label']) ?>"
               class="w-12 h-12 flex items-center justify-center text-gray-400 <?= $p['color'] ?> transition-all">
                <i class="bi <?= e($p['icon']) ?> text-[24px]" aria-hidden="true"></i>
            </a>
            <?php endforeach; ?>
            <button onclick="copyLink(this)"
                    data-url="<?= $shareUrl ?>"
                    aria-label="Copy article link"
                    class="flex items-center justify-center text-gray-400 hover:text-youtube-red transition-all">
                <i class="bi bi-link-45deg text-[24px]" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<script>
function copyLink(btn) {
    const url = btn.getAttribute('data-url');
    navigator.clipboard?.writeText(url).then(() => {
        const icon = btn.querySelector('i');
        if (!icon) return;
        const orig = icon.className;
        icon.className = 'bi bi-check2 text-green-500';
        setTimeout(() => { icon.className = orig; }, 2000);
    }).catch(() => {});
}
</script>
