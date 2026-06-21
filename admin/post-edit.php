<?php 
require_once 'auth.php'; 
require_once '../config/database.php';

/**
 * Redirect legacy post-edit link to the new admin editor route.
 */
$id = $_GET['id'] ?? null;
if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header("Location: /admin/edit.php?id=" . urlencode($id));
    exit;
}

/**
 * Note: This assumes db() is defined in your database.php 
 * and returns the PDO instance.
 */

// Fetch settings using db()
$settings = db()->query("SELECT * FROM settings WHERE id = 1")->fetch();

// 1. Fetch the existing post
if (!$id) { 
    header("Location: /admin/posts.php"); 
    exit; 
}

// Prepare and execute using db()
$stmt = db()->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) { 
    die("Article not found."); 
}

// 2. Handle the Update Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $title     = $_POST['title'];
        $m_title   = $_POST['meta_title'] ?: $title;
        $slug      = $_POST['slug'];
        $content   = $_POST['content'];
        $excerpt   = $_POST['excerpt'];
        $category  = $_POST['category'];
        $tag_color = $_POST['tag_color'];
        $keyword   = $_POST['focus_keyword'];
        $schema    = $_POST['schema_markup'];
        $score     = (int)$_POST['seo_score'];

        $sql = "UPDATE posts SET 
                title = ?, meta_title = ?, slug = ?, content = ?, excerpt = ?, 
                category = ?, tag_color = ?, focus_keyword = ?, schema_markup = ?, seo_score = ? 
                WHERE id = ?";
        
        // Execute update using db()
        db()->prepare($sql)->execute([
            $title, $m_title, $slug, $content, $excerpt, 
            $category, $tag_color, $keyword, $schema, $score, $id
        ]);
        
        header("Location: /admin/posts.php?msg=updated"); 
        exit;
    } catch (PDOException $e) { 
        die("Error: " . $e->getMessage()); 
    }
}
?>

<?php include 'includes/head.php'; ?>

<style>
    input, textarea, select { background: transparent !important; border: none !important; outline: none !important; border-radius: 0 !important; }
    .sep-line { border-bottom: 1px solid #f3f4f6; }
    .ai-gradient-text { background: linear-gradient(90deg, #6366f1, #ff0000); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
</style>

<div class="flex h-screen bg-white">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col ml-[280px]">
        
        <!-- TOP NAV -->
        <header class="h-24 px-12 flex items-center justify-between sep-line shrink-0">
            <div class="flex flex-col">
                <span class="text-[9px] font-black uppercase tracking-[0.3em] text-gray-300 mb-1">Editing Mode</span>
                <div class="flex items-center gap-4">
                   <h2 class="text-xs font-black uppercase tracking-widest text-black"><?php echo htmlspecialchars($post['title']); ?></h2>
                </div>
            </div>
            <div class="flex items-center gap-10">
                <button type="button" onclick="omniAutoPilot()" id="aiBtn" class="text-[10px] font-black uppercase tracking-[0.2em] ai-gradient-text hover:opacity-50 transition-all">
                   [ Re-Optimize with AI ]
                </button>
                <button type="submit" form="masterForm" class="text-[10px] font-black uppercase tracking-[0.2em] text-black hover:text-youtube-red transition-all">
                   Update Article
                </button>
            </div>
        </header>

        <form method="POST" id="masterForm" class="flex flex-1 overflow-hidden">
            <input type="hidden" name="seo_score" id="seoScoreHidden" value="<?php echo $post['seo_score']; ?>">

            <!-- LEFT SIDE: EDITOR -->
            <div class="flex-1 overflow-y-auto px-16 py-12">
                
                <!-- AI COMMAND BOX (For Updates) -->
                <div class="mb-20">
                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-4 block">AI Re-Write Instruction</span>
                    <textarea id="aiCommand" rows="1" class="w-full text-2xl font-bold text-black placeholder-gray-200" placeholder="e.g. Update this article with more recent 2025 statistics..."></textarea>
                    <div class="sep-line mt-4"></div>
                </div>

                <div class="space-y-12 pb-40">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-300 mb-4 block">Article Headline</span>
                        <textarea name="title" id="postTitle" rows="1" class="w-full text-6xl font-[900] tracking-tighter text-black outline-none" placeholder="Headline..."><?php echo htmlspecialchars($post['title']); ?></textarea>
                    </div>

                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-300 mb-4 block">Article Body</span>
                        <textarea name="content" id="postContent" rows="25" class="w-full font-mono text-[16px] leading-relaxed text-gray-800 outline-none" placeholder="Content..."><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE: TECHNICAL METADATA -->
            <div class="w-[450px] px-12 py-12 space-y-12 overflow-y-auto shrink-0 border-l border-gray-50">
                
                <div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-300 mb-6 block">Optimization Health</span>
                    <div class="flex items-end gap-2">
                        <span class="text-6xl font-[900] tracking-tighter" id="vScore"><?php echo str_pad($post['seo_score'], 2, "0", STR_PAD_LEFT); ?></span>
                        <span class="text-[10px] font-black uppercase mb-2 text-gray-400">Score / 100</span>
                    </div>
                </div>

                <div class="space-y-10">
                    <section>
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-300 mb-6 block">Search Metadata</span>
                        <div class="space-y-8">
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">Focus Keyword</label>
                                <input type="text" name="focus_keyword" id="fKeyword" value="<?php echo htmlspecialchars($post['focus_keyword']); ?>" class="w-full font-bold text-sm text-black">
                                <div class="sep-line mt-2"></div>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">SEO Meta Title</label>
                                <input type="text" name="meta_title" id="mTitle" value="<?php echo htmlspecialchars($post['meta_title']); ?>" class="w-full font-bold text-sm text-black">
                                <div class="sep-line mt-2"></div>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">URL Slug</label>
                                <input type="text" name="slug" id="pSlug" value="<?php echo htmlspecialchars($post['slug']); ?>" class="w-full font-mono text-xs text-gray-500">
                                <div class="sep-line mt-2"></div>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">Meta Description</label>
                                <textarea name="excerpt" id="mDesc" rows="3" class="w-full font-medium text-xs leading-relaxed text-gray-500"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                                <div class="sep-line mt-2"></div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-300 mb-6 block">Automation & Technical</span>
                        <div class="space-y-8">
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">JSON-LD Schema</label>
                                <textarea name="schema_markup" id="sMarkup" rows="6" class="w-full font-mono text-[10px] text-indigo-500 leading-relaxed"><?php echo htmlspecialchars($post['schema_markup']); ?></textarea>
                                <div class="sep-line mt-2"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-10">
                                <div>
                                    <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">Category</label>
                                    <input type="text" name="category" id="pCat" value="<?php echo htmlspecialchars($post['category']); ?>" class="w-full font-bold text-xs text-black">
                                    <div class="sep-line mt-2"></div>
                                </div>
                                <div>
                                    <label class="text-[9px] font-black uppercase text-gray-400 block mb-2">Accent Color</label>
                                    <input type="text" name="tag_color" id="pColor" value="<?php echo htmlspecialchars($post['tag_color']); ?>" class="w-full font-bold text-xs text-black">
                                    <div class="sep-line mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
    const KEY = "<?php echo $settings['openrouter_key'] ?? ''; ?>";

    async function omniAutoPilot() {
        const cmd = document.getElementById('aiCommand').value;
        const currentContent = document.getElementById('postContent').value;
        const btn = document.getElementById('aiBtn');
        
        btn.innerText = "[ AI RE-OPTIMIZING... ]";

        // Instruction for the AI to "Refine" existing work
        const prompt = `Act as an Elite SEO Strategist. 
        Current Article: ${currentContent}
        Update Instruction: ${cmd || 'Improve SEO and flow'}. 
        Return ONLY valid JSON: {
            "title": "Headline", "content": "HTML body", "m_title": "SEO title", "slug": "url-slug", 
            "keyword": "focus keyword", "desc": "160 char summary", "schema": "JSON-LD string", 
            "cat": "Category", "color": "Hex", "score": 98
        }`;

        try {
            const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
                method: "POST",
                headers: { "Authorization": `Bearer ${KEY}`, "Content-Type": "application/json" },
                body: JSON.stringify({ "model": "nex-agi/nex-n2-pro:free", "messages": [{"role": "user", "content": prompt}] })
            });

            const data = await response.json();
            const res = JSON.parse(data.choices[0].message.content.replace(/```json|```/g, '').trim());

            document.getElementById('postTitle').value = res.title;
            document.getElementById('postContent').value = res.content;
            document.getElementById('mTitle').value = res.m_title;
            document.getElementById('fKeyword').value = res.keyword;
            document.getElementById('mDesc').value = res.desc;
            document.getElementById('sMarkup').value = res.schema;
            document.getElementById('vScore').innerText = res.score;
            document.getElementById('seoScoreHidden').value = res.score;
            
            alert("Article Re-Optimized Successfully.");
        } catch (e) {
            alert("AI Error. Check your connection.");
        } finally {
            btn.innerText = "[ RE-OPTIMIZE WITH AI ]";
        }
    }

    // Auto-resize headline on load and input
    const h = document.getElementById('postTitle');
    function resize() { h.style.height = 'auto'; h.style.height = h.scrollHeight + 'px'; }
    h.addEventListener('input', resize);
    window.onload = resize;
</script>

<?php include 'includes/footer.php'; ?>