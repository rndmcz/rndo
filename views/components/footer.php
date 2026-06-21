<?php
/**
 * views/components/footer.php
 */
$siteName = setting('site_title', 'randomous');
try {
    $footerCats = db()->query("SELECT category FROM posts GROUP BY category ORDER BY COUNT(*) DESC LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $footerCats = [];
}
?>
<footer class="bg-zinc-900 text-white pt-8 pb-8 text-xs w-full" role="contentinfo">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="grid grid-cols-3 gap-8 mb-4">

            <!-- Categories -->
            <nav aria-labelledby="footer-categories">
                <h3 id="footer-categories" class="font-bold mb-4 text-gray-300 uppercase tracking-widest text-[10px]">Categories</h3>
                <ul class="space-y-3 text-gray-400">
                    <?php foreach (array_slice($footerCats, 0, 4) as $cat): ?>
                    <li>
                        <a href="<?= e(categoryUrl($cat)) ?>" class="hover:text-white transition-colors">
                            <?= e($cat) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Links -->
            <nav aria-labelledby="footer-links">
                <h3 id="footer-links" class="font-bold mb-4 text-gray-300 uppercase tracking-widest text-[10px]">Links</h3>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="<?= SITE_URL ?>/" class="hover:text-white transition-colors">Latest Posts</a></li>
                    <li><a href="<?= SITE_URL ?>/sitemap.php" class="hover:text-white transition-colors">Sitemap</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/login.php" class="hover:text-white transition-colors">Admin</a></li>
                </ul>
            </nav>

            <!-- Platform -->
            <nav aria-labelledby="footer-platform">
                <h3 id="footer-platform" class="font-bold mb-4 text-gray-300 uppercase tracking-widest text-[10px]">Platform</h3>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Privacy</a></li>
                </ul>
            </nav>
        </div>

        <div class="border-t border-zinc-800 pt-4 flex flex-col md:flex-row justify-between items-center text-gray-500">
            <p class="mb-4 md:mb-0 text-[11px] font-bold uppercase tracking-widest">
                &copy; <?= date('Y') ?> <?= e($siteName) ?>
            </p>
            <div class="flex space-x-6 text-[11px] font-bold uppercase tracking-widest">
                <a href="#" class="hover:text-white transition-colors">Help</a>
                <a href="#" class="hover:text-white transition-colors">Privacy</a>
                <a href="#" class="hover:text-white transition-colors">Terms</a>
            </div>
        </div>
    </div>
</footer>
