<?php
/**
 * views/components/related-articles.php
 * Expects $post to be set
 */
try {
    $relStmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
    $relStmt->execute([$post['category'], $post['id']]);
    $relatedPosts = $relStmt->fetchAll();

    if (count($relatedPosts) < 1) {
        $relStmt = db()->prepare("SELECT id, title, slug, excerpt, category, content, created_at FROM posts WHERE id != ? ORDER BY created_at DESC LIMIT 3");
        $relStmt->execute([$post['id']]);
        $relatedPosts = $relStmt->fetchAll();
    }
} catch (Throwable $e) {
    $relatedPosts = [];
}

if (empty($relatedPosts)) return;
?>
<section class="max-w-5xl mx-auto px-0 py-4" aria-labelledby="related-heading">

    <div class="mb-8">
        <h2 id="related-heading" class="text-[11px] font-black uppercase tracking-[0.4em] text-black mb-2">Recommended Articles</h2>
        <div class="w-10 h-1 bg-youtube-red" aria-hidden="true"></div>
    </div>

    <div class="space-y-8">
        <?php foreach ($relatedPosts as $rel):
            $item = $rel;
            $size = 'compact';
        ?>
        <?php include __DIR__ . '/article-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div class="pb-10 pt-10 border-t border-gray-50 text-center">
        <a href="<?= SITE_URL ?>/"
           class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-black transition-colors inline-flex items-center gap-3">
            Back to main feed <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</section>
