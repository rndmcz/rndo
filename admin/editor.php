<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/database.php';

$success = "";
$error   = "";
$id      = $_GET['id'] ?? null;
$is_edit = (bool)$id;

$post = [
    'title'=>'','meta_title'=>'','slug'=>'','content'=>'',
    'excerpt'=>'','category'=>'','tag_color'=>'#FF0033',
    'focus_keyword'=>'','schema_markup'=>'','seo_score'=>0,'chatbot_context'=>''
];

try {
    $categories = db()->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
    $ai_models  = db()->query("SELECT * FROM ai_models ORDER BY provider, name ASC")->fetchAll();
    $s          = db()->query("SELECT * FROM settings WHERE id=1")->fetch();
    if (!$s) {
        $s = [];
    }

    // Fallback to the first registered model if settings do not include a default.
    if (empty($s['ai_model']) && !empty($ai_models[0]['model_string'])) {
        $s['ai_model'] = $ai_models[0]['model_string'];
    }

    $selectedAIModel = trim($_POST['selected_ai_model'] ?? $_GET['selected_ai_model'] ?? $s['ai_model'] ?? '');
    if ($selectedAIModel !== '') {
        $s['ai_model'] = $selectedAIModel;
    }

    // Ensure the selected AI model is a known registered model.
    $registeredModels = array_column($ai_models, 'model_string');
    if (!in_array($s['ai_model'], $registeredModels, true) && !empty($registeredModels)) {
        $s['ai_model'] = $registeredModels[0];
    }

    if ($is_edit) {
        $stmt = db()->prepare("SELECT * FROM posts WHERE id=?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) $post = $p; else die("Post not found.");
    }
} catch (PDOException $e) { $error = "DB: ".$e->getMessage(); }

$default_author = $s['author_name'] ?? 'randomous Team';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title   = trim($_POST['title']   ?? 'Untitled');
        $m_title = trim($_POST['meta_title'] ?? '') ?: $title;
        $raw_slug= trim($_POST['slug']    ?? '');
        $slug    = $raw_slug ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/','-',$title)));
        $content = $_POST['content']   ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $cat     = trim($_POST['category'] ?? '');
        $color   = trim($_POST['tag_color'] ?? '#FF0033');
        $kw      = trim($_POST['focus_keyword'] ?? '');
        $schema  = trim($_POST['schema_markup']  ?? '');
        $score   = (int)($_POST['seo_score'] ?? 0);
        $ctx     = $_POST['chatbot_context'] ?? '';
        // Auto read-time
        $wc       = str_word_count(strip_tags($content));
        $readtime = max(1, round($wc / 200)) . ' Min Read';

        if ($is_edit) {
            $sql = "UPDATE posts SET title=?,meta_title=?,slug=?,content=?,excerpt=?,category=?,tag_color=?,focus_keyword=?,schema_markup=?,seo_score=?,author_name=?,chatbot_context=?,read_time=? WHERE id=?";
            db()->prepare($sql)->execute([$title,$m_title,$slug,$content,$excerpt,$cat,$color,$kw,$schema,$score,$default_author,$ctx,$readtime,$id]);
            $success = "Node synchronized successfully.";
            // Refresh
            $stmt = db()->prepare("SELECT * FROM posts WHERE id=?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
        } else {
            $sql = "INSERT INTO posts (title,meta_title,slug,content,excerpt,category,tag_color,focus_keyword,schema_markup,seo_score,author_name,chatbot_context,read_time) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
                db()->prepare($sql)->execute([$title,$m_title,$slug,$content,$excerpt,$cat,$color,$kw,$schema,$score,$default_author,$ctx,$readtime]);
                header("Location: /admin/posts.php?msg=created"); exit;
        }
    } catch (PDOException $e) { $error = "Save failed: ".$e->getMessage(); }
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<style>
/* ── SEO Panel Styles ─────────────────── */
.seo-row{display:flex;align-items:center;gap:10px;padding:5px 0;border-bottom:1px solid #f3f4f6}
.seo-row-label{font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.1em;color:#6b7280;min-width:110px;flex-shrink:0}
.seo-row-bar-wrap{flex:1;background:#f3f4f6;height:4px;border-radius:2px;overflow:hidden}
.seo-row-bar{height:100%;border-radius:2px;transition:width .6s ease}
.seo-row-score{font-size:9px;font-weight:900;min-width:32px;text-align:right;flex-shrink:0}
.seo-tip{font-size:10px;padding:5px 0;border-bottom:1px solid #f9fafb;color:#374151;line-height:1.5}
.lsi-tag{display:inline-block;font-size:9px;font-weight:700;padding:2px 7px;background:#f3f4f6;margin:2px;text-transform:uppercase;letter-spacing:.05em}
/* Live stream preview */
#liveStreamPreview{font-family:monospace;font-size:10px;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 12px;max-height:80px;overflow:hidden;white-space:pre-wrap;word-break:break-all}
/* Tabs */
.sidebar-tab{cursor:pointer;padding:6px 12px;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.2em;border-bottom:2px solid transparent;transition:all .2s}
.sidebar-tab.active{border-color:#000;color:#000}
.sidebar-tab:not(.active){color:#9ca3af}
.tab-panel{display:none}.tab-panel.active{display:block}
</style>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 flex mt-16 md:mt-0 flex-col md:ml-[280px] w-full min-w-0">

        <!-- Header -->
        <header class="px-6 md:px-10 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100">
            <div>
                <span class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-1 block">
                    <?= $is_edit ? 'Modify Mode' : 'Compose Mode' ?>
                </span>
                <h1 class="text-lg md:text-xl font-black uppercase tracking-widest text-black">
                    <?= $is_edit ? 'Edit / #'.$post['id'] : 'New Article' ?>
                </h1>
            </div>
            <div class="flex items-center gap-4 flex-wrap">
                <!-- AI Action Buttons -->
                <button type="button" onclick="omniAutoPilot('generate')" id="aiBtn"
                    class="text-[9px] font-black uppercase tracking-[.25em] text-[#FF0033] hover:text-black border border-[#FF0033] hover:border-black px-4 py-2.5 transition-all">
                    ⚡ AutoPilot AI
                </button>
                <button type="button" onclick="omniAutoPilot('rewrite')"
                    class="text-[9px] font-black uppercase tracking-[.2em] text-gray-500 hover:text-black border border-gray-200 hover:border-black px-3 py-2.5 transition-all">
                    ↺ Rewrite
                </button>
                <button type="button" onclick="omniAutoPilot('expand')"
                    class="text-[9px] font-black uppercase tracking-[.2em] text-gray-500 hover:text-black border border-gray-200 hover:border-black px-3 py-2.5 transition-all">
                    ↕ Expand
                </button>
                <button type="submit" form="masterForm"
                    class="px-8 py-3 bg-black text-white text-[9px] font-black uppercase tracking-[.3em] hover:bg-[#FF0033] transition-all">
                    <?= $is_edit ? 'Sync Node' : 'Publish' ?>
                </button>
            </div>
        </header>

        <form method="POST" id="masterForm" class="flex-1 flex flex-col lg:flex-row relative">
            <!-- Hidden fields -->
            <input type="hidden" name="seo_score"      id="seoScoreHidden"  value="<?= $post['seo_score'] ?>">
            <input type="hidden" name="meta_title"      id="metaTitleHidden" value="<?= htmlspecialchars($post['meta_title']) ?>">
            <textarea name="chatbot_context_hidden" id="chatbotContextHidden" class="hidden"><?= htmlspecialchars($post['chatbot_context']) ?></textarea>

            <!-- ═══ LEFT: Editor Area ═══════════════════════════════════════ -->
            <div class="flex-1 p-6 md:p-10 pt-2 overflow-y-auto space-y-8">

                <!-- Alerts -->
                <?php if($success): ?>
                    <div class="text-[9px] font-black uppercase text-green-600 tracking-widest border-l-2 border-green-500 pl-3 py-1">[ ✓ <?= $success ?> ]</div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="text-[9px] font-black uppercase text-red-600 tracking-widest border-l-2 border-red-500 pl-3 py-1">[ ⚠ <?= $error ?> ]</div>
                <?php endif; ?>

                <!-- JS Error Box -->
                <div id="jsErrorBox" class="hidden border-l-4 border-[#FF0033] pl-4 py-3 text-red-700 text-[10px] font-mono leading-relaxed break-words bg-red-50"></div>

                <!-- AI Status Bar -->
                <div id="aiStatus" class="hidden items-center gap-3 bg-black text-white px-5 py-3">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-[#FF0033] animate-pulse rounded-full"></span>
                        <span class="w-1.5 h-1.5 bg-[#FF0033] animate-pulse delay-75 rounded-full"></span>
                        <span class="w-1.5 h-1.5 bg-[#FF0033] animate-pulse delay-150 rounded-full"></span>
                    </div>
                    <span id="aiStatusText" class="text-[9px] font-black uppercase tracking-widest">Neural Link Active...</span>
                </div>

                <!-- Live Stream Preview -->
                <div id="liveStreamPreview" class="hidden"></div>

                <!-- AI Instruction -->
                <div>
                    <label class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-2 block">
                        AI Instruction <span class="text-gray-300 font-normal normal-case tracking-normal">(topic, tone, audience — be specific)</span>
                    </label>
                    <textarea id="aiCommand" rows="2"
                        class="w-full text-base font-semibold text-black border-b border-gray-200 focus:border-black outline-none resize-none bg-transparent py-2 transition-all placeholder-gray-300"
                        placeholder="e.g. Write a comprehensive guide on photosynthesis for NEET students, include diagrams description, focus keyword: photosynthesis process"></textarea>
                </div>

                <!-- Headline -->
                <div>
                    <label class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-2 block">
                        Headline (H1)
                    </label>
                    <textarea name="title" id="postTitle" rows="2"
                        class="w-full text-2xl md:text-4xl font-black tracking-tight text-black border-b border-gray-200 focus:border-black outline-none resize-none bg-transparent py-2 leading-tight uppercase transition-all placeholder-gray-200"
                        placeholder="ARTICLE TITLE..."><?= htmlspecialchars($post['title']) ?></textarea>
                </div>

                <!-- Article Body -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400">
                            Article Body <span class="text-gray-300 font-normal normal-case">(HTML)</span>
                        </label>
                        <span id="wordCountLabel" class="text-[9px] font-black text-gray-400">0 words</span>
                    </div>
                    <textarea name="content" id="postContent" rows="30"
                        class="w-full font-mono text-sm leading-relaxed text-gray-700 border-b border-gray-200 focus:border-black outline-none bg-transparent py-2 transition-all custom-scrollbar"
                        placeholder="Article HTML content will appear here..."><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

            </div>

            <!-- ═══ RIGHT: Metadata Sidebar ════════════════════════════════ -->
            <aside class="w-full lg:w-[380px] border-t lg:border-t-0 lg:border-l border-gray-100 shrink-0 lg:overflow-y-auto">

                <!-- SEO Ring Header -->
                <div class="p-6 border-b border-gray-100 flex items-center gap-5">
                    <div class="relative w-[72px] h-[72px] shrink-0">
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                            <circle id="ringFill" cx="50" cy="50" r="45" fill="none" stroke="#000"
                                stroke-width="10" stroke-dasharray="283"
                                stroke-dashoffset="<?= 283 - (283 * $post['seo_score'] / 100) ?>"
                                style="transition:stroke-dashoffset 1s ease;"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-black" id="vScore"><?= $post['seo_score'] ?></span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-black uppercase tracking-widest text-black mb-1">SEO Health Score</div>
                        <div class="flex gap-3 text-[9px] text-gray-400 font-bold mb-2">
                            <span>Words: <b id="statWords" class="text-black">–</b></span>
                            <span>H2: <b id="statH2" class="text-black">–</b></span>
                        </div>
                        <button type="button" id="seoCheckBtn"
                            onclick="runSeoCheck()"
                            class="text-[9px] font-black uppercase tracking-widest text-gray-400 hover:text-black border border-gray-200 hover:border-black px-3 py-1.5 transition-all w-full">
                            ⟳ Analyze SEO
                        </button>
                    </div>
                </div>

                <!-- Sidebar Tabs -->
                <div class="flex border-b border-gray-100">
                    <button type="button" class="sidebar-tab active flex-1" onclick="switchTab('settings',this)">Settings</button>
                    <button type="button" class="sidebar-tab flex-1" onclick="switchTab('seo',this)">SEO Report</button>
                    <button type="button" class="sidebar-tab flex-1" onclick="switchTab('schema',this)">Schema</button>
                </div>

                <!-- TAB: Settings -->
                <div id="tab-settings" class="tab-panel active p-6 space-y-7">

                    <!-- Model Selector -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-2 block">AI Model</label>
                        <select id="activeModel" name="selected_ai_model"
                            class="w-full bg-transparent border-b border-gray-200 focus:border-black outline-none py-2 text-[10px] font-mono text-gray-500 cursor-pointer appearance-none">
                            <option value="" data-provider="">— SELECT MODEL —</option>
                            <?php foreach($ai_models as $m): ?>
                                <option value="<?= htmlspecialchars($m['model_string']) ?>"
                                    data-provider="<?= htmlspecialchars($m['provider']) ?>"
                                    <?= (($s['ai_model'] ?? '') === $m['model_string']) ? 'selected' : '' ?>
                                    >
                                    [<?= strtoupper($m['provider']) ?>] <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-2 block">Category</label>
                        <select name="category" id="pCat" required
                            class="w-full bg-transparent border-b border-gray-200 focus:border-black outline-none py-2 text-sm font-bold text-black cursor-pointer appearance-none">
                            <option value="">— SELECT —</option>
                            <?php foreach($categories as $cn): ?>
                                <option value="<?= htmlspecialchars($cn) ?>" <?= $post['category']==$cn?'selected':'' ?>>
                                    <?= htmlspecialchars($cn) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Focus Keyword -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400">Focus Keyword</label>
                            <span id="kwDensity" class="text-[9px] font-black text-gray-300">Density: –</span>
                        </div>
                        <input type="text" name="focus_keyword" id="fKeyword"
                            value="<?= htmlspecialchars($post['focus_keyword']) ?>"
                            class="w-full border-b border-gray-200 focus:border-black outline-none bg-transparent py-2 font-bold text-sm transition-all"
                            placeholder="primary target keyword">
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400 mb-2 block">URL Slug</label>
                        <input type="text" name="slug" id="pSlug"
                            value="<?= htmlspecialchars($post['slug']) ?>"
                            class="w-full border-b border-gray-200 focus:border-black outline-none bg-transparent py-2 font-mono text-[10px] text-gray-500 transition-all"
                            placeholder="url-friendly-slug">
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400">Meta Description</label>
                            <span id="metaCharCount" class="text-[9px] font-black text-gray-300">0/160</span>
                        </div>
                        <textarea name="excerpt" id="mDesc" rows="3"
                            class="w-full border-b border-gray-200 focus:border-black outline-none bg-transparent py-2 text-xs text-gray-600 leading-relaxed transition-all"
                            placeholder="Concise meta description (130–160 chars)..."><?= htmlspecialchars($post['excerpt']) ?></textarea>
                    </div>

                    <!-- Hex Accent Color -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400 mb-2 block">Accent Color</label>
                        <div class="flex items-center gap-3 border-b border-gray-200 py-2">
                            <div id="colorDot" class="w-4 h-4 rounded-sm shrink-0" style="background:<?= $post['tag_color'] ?: '#FF0033' ?>"></div>
                            <input type="text" name="tag_color" id="pColor"
                                value="<?= htmlspecialchars($post['tag_color']) ?>"
                                class="w-full outline-none bg-transparent font-bold text-xs uppercase transition-all"
                                placeholder="#FF0033">
                        </div>
                    </div>

                    <!-- LSI Keywords -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400 mb-2 block">LSI / Related Keywords</label>
                        <div id="lsiKeywords" class="flex flex-wrap gap-1 min-h-[28px] p-2 border border-dashed border-gray-200">
                            <span class="text-[9px] text-gray-300 italic">AI will populate these</span>
                        </div>
                    </div>

                </div><!-- /tab-settings -->

                <!-- TAB: SEO Report -->
                <div id="tab-seo" class="tab-panel p-6">
                    <div class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-4 block">SEO Breakdown</div>
                    <div id="seoBreakdown" class="space-y-1 mb-6">
                        <p class="text-[10px] text-gray-400 italic">Click "Analyze SEO" to generate a full report.</p>
                    </div>
                    <div class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-3">Improvement Tips</div>
                    <div id="seoTips" class="space-y-1"></div>
                </div>

                <!-- TAB: Schema -->
                <div id="tab-schema" class="tab-panel p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033]">JSON-LD Schema</label>
                        <button type="button" id="copySchemaBtn"
                            class="text-[9px] font-black uppercase tracking-widest text-gray-400 hover:text-black border border-gray-200 hover:border-black px-2 py-1 transition-all">
                            Copy
                        </button>
                    </div>
                    <textarea name="schema_markup" id="sMarkup" rows="12"
                        class="w-full border border-gray-100 focus:border-indigo-300 outline-none bg-gray-50 p-3 font-mono text-[9px] text-indigo-700 transition-all leading-relaxed"
                        placeholder='{"@context":"https://schema.org","@type":"Article",...}'><?= htmlspecialchars($post['schema_markup']) ?></textarea>
                    <!-- AI Chatbot Context -->
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-[.2em] text-gray-400 mb-2 block">AI Chatbot Context</label>
                        <textarea name="chatbot_context" id="chatbotContextVisible" rows="5"
                            oninput="document.getElementById('chatbotContextHidden').value=this.value"
                            class="w-full border border-gray-100 focus:border-gray-300 outline-none bg-gray-50 p-3 text-[10px] text-gray-600 transition-all leading-relaxed"
                            placeholder="Dense plain-text summary for AI chatbot indexing..."><?= htmlspecialchars($post['chatbot_context']) ?></textarea>
                    </div>
                </div>

            </aside><!-- /aside -->
        </form>
    </main>
</div>

<script>
window.NEETSTACK_CONFIG = {
    defaultModel: <?= json_encode($s['ai_model'] ?? '') ?>,
    proxyUrl: '/admin/ai/proxy.php',
    seoCheckUrl: '/admin/ai/seo-check.php'
};

// Ensure defaultModel is available in the form if not pre-selected
const selectedAIModel = document.querySelector('#activeModel');
if (selectedAIModel && !selectedAIModel.value) {
    const defaultModel = <?= json_encode($s['ai_model'] ?? '') ?>;
    if (defaultModel) selectedAIModel.value = defaultModel;
}

// Tab switcher
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// Sync visible chatbot context with hidden
document.addEventListener('DOMContentLoaded', () => {
    const vis = document.getElementById('chatbotContextVisible');
    const hid = document.getElementById('chatbotContextHidden');
    if (vis && hid) {
        vis.value = hid.value;
        vis.addEventListener('input', () => hid.value = vis.value);
    }
});
</script>
<script src="/admin/js/main.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
