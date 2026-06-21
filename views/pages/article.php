<?php
/**
 * views/pages/article.php
 * Single article view with full SEO, schema, AI chat, TOC
 */

$slug = sanitizeSlug($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

try {
    $stmt = db()->prepare("SELECT * FROM posts WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} catch (Throwable $e) {
    $post = null;
}

if (!$post) {
    renderErrorPage(404);
}

// Increment view count
try {
    db()->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);
} catch (Throwable $e) {}

// SEO
$meta_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'];
$meta_desc  = $post['excerpt'] ?? '';
$canonical  = articleUrl($post['slug'], $post['category'] ?? '');
$og_type    = 'article';
$og_image   = $post['og_image'] ?? '';

// Breadcrumb schema
$breadcrumbSchema = [
    "@context" => "https://schema.org",
    "@type"    => "BreadcrumbList",
    "itemListElement" => [
        ["@type" => "ListItem", "position" => 1, "name" => "Home",              "item" => SITE_URL . '/'],
        ["@type" => "ListItem", "position" => 2, "name" => $post['category'],   "item" => categoryUrl($post['category'])],
        ["@type" => "ListItem", "position" => 3, "name" => $post['title'],      "item" => $canonical],
    ],
];

// Article schema
$articleSchema = [
    "@context"        => "https://schema.org",
    "@type"           => "Article",
    "headline"        => $post['title'],
    "description"     => $post['excerpt'] ?? '',
    "url"             => $canonical,
    "datePublished"   => date('c', strtotime($post['created_at'])),
    "dateModified"    => date('c', strtotime($post['updated_at'] ?? $post['created_at'])),
    "author"          => ["@type" => "Person", "name" => $post['author_name'] ?? 'Editorial Team'],
    "publisher"       => ["@type" => "Organization", "name" => setting('site_title', 'randomous'), "url" => SITE_URL],
    "articleSection"  => $post['category'],
    "wordCount"       => str_word_count(strip_tags($post['content'] ?? '')),
];

// Use provided schema or build from article
$schema_json = !empty($post['schema_markup']) ? $post['schema_markup'] : json_encode([$breadcrumbSchema, $articleSchema]);

$tagColor    = $post['tag_color'] ?? '#FF0033';
$readMinutes = readingTime($post['content'] ?? '');

// Current URL for sharing
$current_url = $canonical;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-white">
<head>
    <?php require __DIR__ . '/../layouts/head.php'; ?>

    <!-- Extra breadcrumb schema -->
    <script type="application/ld+json"><?= json_encode($breadcrumbSchema) ?></script>
</head>
<body class="text-gray-900 antialiased overflow-x-hidden selection:bg-youtube-red selection:text-white">
<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="flex flex-col md:flex-row min-h-screen w-full relative">

    <?php require __DIR__ . '/../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col w-full md:ml-[280px]">

        <?php require __DIR__ . '/../components/sticky-header.php'; ?>
        <?php require __DIR__ . '/../components/mobile-header.php'; ?>

        <main id="main-content" class="flex-1 relative flex flex-col w-full bg-white">

            <!-- Article Hero -->
            <section id="hero-trigger" class="w-full font-sans multi-mesh-hero pt-6 md:pt-14 pb-10 md:pb-16 px-6 md:px-12 border-b border-pink-100 relative overflow-hidden" aria-label="Article header">
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image:url('https://grainy-gradients.vercel.app/noise.svg');" aria-hidden="true"></div>
                <div class="max-w-6xl mx-auto relative z-10">
                    <div class="w-full max-w-3xl mx-auto">

                        <!-- Breadcrumb nav (SEO + UX) -->
                        <nav aria-label="Breadcrumb" class="mb-4">
                            <ol class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                <li><a href="<?= SITE_URL ?>/" class="hover:text-black transition-colors">Home</a></li>
                                <li aria-hidden="true">/</li>
                                <li><a href="<?= e(categoryUrl($post['category'])) ?>" class="hover:text-black transition-colors"><?= e($post['category']) ?></a></li>
                                <li aria-hidden="true">/</li>
                                <li class="text-black line-clamp-1"><?= e(mb_substr($post['title'], 0, 40)) ?>…</li>
                            </ol>
                        </nav>

                        <!-- Category tag -->
                        <div class="mb-4 md:mb-6">
                            <a href="<?= e(categoryUrl($post['category'])) ?>"
                               class="inline-block text-white text-[9px] font-black uppercase tracking-[0.2em] px-5 py-1.5 rounded-full hover:opacity-90 transition-opacity"
                               style="background-color:<?= e($tagColor) ?>">
                                <?= e($post['category']) ?>
                            </a>
                        </div>

                        <!-- Title -->
                        <h1 class="text-[28px] md:text-[36px] lg:text-[44px] font-black leading-[1.2] uppercase tracking-tight text-youtube-dark mb-5 md:mb-7">
                            <?= e($post['title']) ?>
                        </h1>

                        <!-- Author -->
                        <div class="flex items-center gap-2 mb-5 md:mb-7">
                            <div class="w-4 h-4 bg-[#FF0000] rounded-full" aria-hidden="true"></div>
                            <span class="text-[12px] font-black uppercase tracking-[0.15em] text-youtube-dark">
                                <?= e($post['author_name'] ?? 'Editorial Team') ?>
                            </span>
                        </div>

                        <!-- Excerpt -->
                        <p class="text-[14px] md:text-[16px] leading-relaxed text-gray-800 font-medium tracking-tight mb-5">
                            <?= e($post['excerpt'] ?? '') ?>
                        </p>

                        <!-- Meta row -->
                        <div class="flex flex-row justify-between text-[10px] font-black uppercase tracking-[0.15em] text-gray-500 w-full">
                            <time datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>" class="text-youtube-dark">
                                <?= date('M d, Y', strtotime($post['created_at'])) ?>
                            </time>
                            <span><?= formatViews((int)$post['views']) ?> Reads · <?= $readMinutes ?> min read</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Article Body -->
            <section class="max-w-5xl mx-auto px-4 md:px-8 pt-12 pb-0 flex flex-col md:flex-row relative">

                <!-- Desktop share sidebar -->
                <aside class="hidden md:block w-24 flex-shrink-0 relative" aria-label="Share options">
                    <?php require __DIR__ . '/../components/share-buttons.php'; ?>
                </aside>

                <article class="flex-1 max-w-4xl w-full pb-0">

                    <!-- Mobile TOC -->
                    <div class="md:hidden overflow-hidden px-3 transition-all duration-300" id="mobile-toc-container">
                        <div class="flex items-center justify-between cursor-pointer py-4 transition-colors active:bg-gray-50 rounded-xl px-2 -mx-2"
                             onclick="toggleMobileToc()"
                             role="button"
                             tabindex="0"
                             aria-expanded="false"
                             aria-controls="mobile-toc-list"
                             onkeydown="if(event.key==='Enter'||event.key===' ')toggleMobileToc()">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-list-nested text-2xl" aria-hidden="true"></i>
                                <div class="flex flex-col">
                                    <span class="text-[6px] font-black uppercase tracking-[0.3em] text-gray-400 leading-none mb-1">Article Index</span>
                                    <span class="text-[12px] font-black uppercase tracking-tight text-black leading-none">Table of Contents</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50">
                                <i id="toc-chevron" class="bi bi-chevron-down text-lg text-black transition-transform duration-500" aria-hidden="true"></i>
                            </div>
                        </div>
                        <nav id="mobile-toc-list"
                             class="mt-2 space-y-1 overflow-hidden transition-all duration-500 max-h-0 opacity-0 toc-collapsed"
                             aria-label="Article sections">
                        </nav>
                    </div>

                    <!-- Article content -->
                    <div id="article-text-body" class="prose prose-l md:prose-l max-w-none text-gray-800 prose-headings:font-black prose-headings:tracking-tight prose-a:font-semibold">
                        <div class="content-body">
                            <?= $post['content'] ?>
                        </div>
                    </div>

                    <!-- AI Chat section -->
                    <div class="mt-12 w-full max-w-3xl mx-auto pb-0">
                        <h2 id="chat-title" class="hidden text-xl font-black mb-6 border-t border-gray-200 pt-10 text-youtube-dark flex items-center gap-2">
                            <i class="bi bi-stars text-indigo-500" aria-hidden="true"></i> AI Explainer
                        </h2>
                        <div id="chat-container" class="flex pb-4 flex-col space-y-4" aria-live="polite" aria-label="AI conversation"></div>
                    </div>

                    <?php require __DIR__ . '/../components/floating-chat.php'; ?>

                    <!-- Mobile share -->
                    <div class="md:hidden">
                        <?php require __DIR__ . '/../components/share-buttons.php'; ?>
                    </div>

                    <?php require __DIR__ . '/../components/related-articles.php'; ?>

                    <!-- Mobile sidebar widgets -->
                    <div class="pb-10 block md:hidden">
                        <?php require __DIR__ . '/../components/search-sidebar.php'; ?>
                    </div>
                </article>
            </section>
        </main>

        <?php require __DIR__ . '/../components/footer.php'; ?>
    </div>
</div>

<!-- Sidebar overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm" aria-hidden="true"></div>

<script>
function toggleMobileToc() {
    const list    = document.getElementById('mobile-toc-list');
    const chevron = document.getElementById('toc-chevron');
    const btn     = list?.previousElementSibling;
    if (!list) return;
    const collapsed = list.classList.contains('toc-collapsed');
    list.style.maxHeight = collapsed ? '2000px' : '0px';
    list.style.opacity   = collapsed ? '1' : '0';
    list.classList.toggle('toc-collapsed', !collapsed);
    if (chevron) chevron.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
    if (btn) btn.setAttribute('aria-expanded', collapsed ? 'true' : 'false');
}

document.addEventListener('DOMContentLoaded', () => {

    // --- TOC GENERATOR ---
    const desktopList  = document.getElementById('toc-list');
    const mobileList   = document.getElementById('mobile-toc-list');
    const articleBody  = document.querySelector('.content-body');

    if (articleBody) {
        if (desktopList) desktopList.innerHTML = '';
        if (mobileList) { mobileList.innerHTML = ''; mobileList.classList.add('toc-collapsed'); }

        const headings = articleBody.querySelectorAll('h2, h3');
        let currentH2Wrapper = null;

        headings.forEach((heading, i) => {
            const id = 'section-nav-' + i;
            heading.id = id;
            const isH3 = heading.tagName.toLowerCase() === 'h3';

            const scrollTo = () => {
                const offset = window.innerWidth < 768 ? 90 : 110;
                window.scrollTo({ top: heading.getBoundingClientRect().top + window.pageYOffset - offset, behavior: 'smooth' });
            };

            // Desktop – flat list
            if (desktopList) {
                const a = document.createElement('a');
                a.textContent = heading.textContent;
                a.className = `cursor-pointer block py-2 text-[13px] font-bold transition-all hover:text-youtube-red ${isH3 ? 'pl-4 text-gray-400 font-medium' : 'text-gray-800'}`;
                a.onclick = e => {
                    e.preventDefault();
                    scrollTo();
                    document.getElementById('toc-drawer')?.classList.add('opacity-0','pointer-events-none','-translate-y-4');
                };
                desktopList.appendChild(a);
            }

            // Mobile – nested
            if (mobileList) {
                if (!isH3) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'mb-2';
                    const a = document.createElement('a');
                    a.className = 'flex items-center justify-between py-3 px-4 text-[12px] font-black uppercase tracking-tight text-black';
                    a.innerHTML = `<div class="flex items-center gap-3"><span class="w-1.5 h-1.5 bg-youtube-red rounded-full"></span>${heading.textContent}</div><i class="bi bi-chevron-right text-[10px] h3-toggle-icon transition-transform hidden"></i>`;
                    a.onclick = e => { scrollTo(); };
                    const sub = document.createElement('div');
                    sub.className = 'h3-group max-h-0 opacity-0 overflow-hidden transition-all duration-500 pl-6 ml-4';
                    wrapper.append(a, sub);
                    mobileList.appendChild(wrapper);
                    currentH2Wrapper = { group: sub, icon: a.querySelector('.h3-toggle-icon') };
                } else if (currentH2Wrapper) {
                    currentH2Wrapper.icon.classList.remove('hidden');
                    const a = document.createElement('a');
                    a.className = 'flex items-center gap-3 py-2 text-[12px] font-bold text-gray-400 hover:text-black';
                    a.innerHTML = `<span class="w-1 h-1 bg-gray-200 rounded-full"></span> ${heading.textContent}`;
                    a.onclick = e => { e.preventDefault(); scrollTo(); };
                    currentH2Wrapper.group.appendChild(a);
                }
            }
        });
    }

    // --- STICKY HEADER TOC DRAWER ---
    const tocToggle = document.getElementById('toc-toggle');
    const tocDrawer = document.getElementById('toc-drawer');
    if (tocToggle && tocDrawer) {
        tocToggle.onclick = e => {
            e.stopPropagation();
            const open = tocDrawer.classList.toggle('opacity-0') ? false : true;
            tocDrawer.classList.toggle('pointer-events-none', !open);
            tocDrawer.classList.toggle('-translate-y-4', !open);
            tocToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        document.addEventListener('click', e => {
            if (!tocDrawer.contains(e.target) && e.target !== tocToggle) {
                tocDrawer.classList.add('opacity-0','pointer-events-none','-translate-y-4');
                tocToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // --- SCROLL OBSERVER ---
    window.addEventListener('scroll', () => {
        const header = document.getElementById('sticky-header');
        const chat   = document.getElementById('floating-chat-widget');
        const show   = window.scrollY > 600;
        if (header) { header.classList.toggle('-translate-y-full', !show); header.classList.toggle('opacity-0', !show); }
        if (chat)   { chat.classList.toggle('opacity-0', !show); chat.classList.toggle('translate-y-4', !show); chat.classList.toggle('pointer-events-none', !show); }
    }, { passive: true });
});
</script>
</body>
</html>
