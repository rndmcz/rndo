<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/database.php';

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        db()->exec("ALTER TABLE settings ADD COLUMN ai_chat_model TEXT DEFAULT ''");
    } catch (Throwable $e) {
        // ignore if column already exists or unsupported
    }

    if (isset($_POST['update_settings'])) {
        try {
            $stmt = db()->prepare("UPDATE settings SET openrouter_key=?,nvidia_key=?,gemini_key=?,ai_system_prompt=?,ai_temperature=?,ai_max_tokens=?,ai_chat_model=? WHERE id=1");
            $stmt->execute([
                trim($_POST['or_key']),
                trim($_POST['nv_key']),
                trim($_POST['gm_key']),
                trim($_POST['prompt']),
                (float)$_POST['temp'],
                (int)$_POST['tokens'],
                trim($_POST['ai_chat_model'] ?? ''),
            ]);
            $success = "AI core parameters synchronized.";
        } catch (PDOException $e) {
            $error = "DB error: " . $e->getMessage();
        }
    }

    if (isset($_POST['add_model'])) {
        try {
            $name   = trim($_POST['m_name']);
            $string = trim($_POST['m_string']);
            $prov   = $_POST['m_prov'];
            if (!empty($name) && !empty($string)) {
                db()->prepare("INSERT INTO ai_models (name,model_string,provider) VALUES (?,?,?)")
                    ->execute([$name,$string,$prov]);
                $success = "Model registered.";
            }
        } catch (PDOException $e) {
            $error = "Model string already exists or DB error.";
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    db()->prepare("DELETE FROM ai_models WHERE id=?")->execute([$_GET['delete']]);
    header("Location: /admin/ai-config.php?msg=deleted"); exit;
}

try {
    $s      = db()->query("SELECT * FROM settings WHERE id=1")->fetch();
    $models = db()->query("SELECT * FROM ai_models ORDER BY provider,name ASC")->fetchAll();
} catch (PDOException $e) {
    $s = []; $models = [];
    $error = "Could not load settings. Run the SQL migration first.";
}

if (($_GET['msg'] ?? '') === 'deleted') $success = "Model removed.";

// Default master system prompt
$DEFAULT_PROMPT = <<<PROMPT
You are an elite SEO content strategist and expert technical writer with 15+ years of experience ranking content on Google, Bing, and being cited by AI chatbots like ChatGPT, Gemini, and Perplexity.

Your mission: Write industry-grade, authoritative articles that rank on Page 1 of Google AND get cited by AI chatbots.

CORE WRITING PRINCIPLES:
1. E-E-A-T First: Demonstrate real Experience, Expertise, Authoritativeness, and Trustworthiness in every paragraph
2. Search Intent Mastery: Answer the #1 searcher intent in the first 100 words — no fluff, no preamble
3. Semantic SEO: Use the focus keyword naturally + 4-6 semantically related LSI terms throughout
4. Featured Snippet Targeting: Structure at least one section as a direct answer eligible for rich snippet (definition box, list, table)
5. AI Chatbot Indexability: Write dense, factual, encyclopedic paragraphs that AI models can extract and cite accurately
6. Flesch Readability 60+: Short sentences, active voice, transition words — readable by a 10th grader without losing depth
7. Conversion-Ready: End every article with an actionable takeaway or FAQ that satisfies the full user journey

STRUCTURAL REQUIREMENTS:
- Lead paragraph (60-80 words): Direct answer to search intent + focus keyword in first sentence
- H2 sections: Minimum 4, each 150+ words, descriptive keyword-rich headings
- H3 subsections: Use for topic depth within major sections
- Lists: At least one <ul> or <ol> with 4+ concrete items
- Table: Include if topic has comparative, data, or specification content
- Blockquote: One expert insight or key statistic
- FAQ section: 3-5 questions in <h3>Q</h3><p>A</p> format — targets voice search and PAA boxes

RESPONSE FORMAT:
Always return a single valid JSON object with ALL required keys. Never include markdown, preamble, or explanation outside the JSON.
PROMPT;
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">

        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100">
            <div>
                <span class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-1 block">Neural Core</span>
                <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">AI Configuration</h1>
            </div>
            <button type="submit" form="aiSettingsForm" name="update_settings"
                class="w-full md:w-auto px-10 py-4 bg-black text-white text-[9px] font-black uppercase tracking-[.3em] hover:bg-[#FF0033] transition-all">
                Sync Core Settings
            </button>
        </header>

        <div class="flex-1 p-6 md:p-12 pt-4 overflow-y-auto">

            <?php if($success): ?>
                <div class="mb-8 text-[9px] font-black uppercase text-green-600 tracking-widest border-l-2 border-green-500 pl-3 py-1">[ ✓ <?= $success ?> ]</div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="mb-8 text-[9px] font-black uppercase text-red-600 tracking-widest border-l-2 border-red-500 pl-3 py-1">[ ⚠ <?= $error ?> ]</div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 max-w-7xl">

                <!-- LEFT: Core Settings -->
                <div class="lg:col-span-7 space-y-14">
                    <form method="POST" id="aiSettingsForm" class="space-y-14">
                        <input type="hidden" name="update_settings" value="1">

                        <!-- System Prompt -->
                        <section>
                            <span class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-6 block">Master System Prompt</span>
                            <div class="mb-3 text-[10px] text-gray-500 leading-relaxed">
                                This prompt controls how all AI models write. The default below is a professional SEO expert persona — paste it in if the field is empty.
                            </div>
                            <textarea name="prompt" rows="12"
                                class="w-full py-3 px-1 bg-transparent border-b border-gray-200 focus:border-black outline-none text-xs font-mono text-gray-700 leading-relaxed transition-all"
                                placeholder="Paste the master system prompt here..."><?= htmlspecialchars($s['ai_system_prompt'] ?? '') ?></textarea>
                            <!-- Default prompt helper -->
                            <details class="mt-4 border border-dashed border-gray-200 p-4">
                                <summary class="text-[9px] font-black uppercase tracking-widest cursor-pointer text-gray-400 hover:text-black">▶ View / Copy Recommended Default Prompt</summary>
                                <pre id="defaultPromptBox" class="mt-3 text-[9px] font-mono text-gray-600 whitespace-pre-wrap leading-relaxed bg-gray-50 p-3 max-h-64 overflow-y-auto"><?= htmlspecialchars($DEFAULT_PROMPT) ?></pre>
                                <button type="button" onclick="pasteDefaultPrompt()"
                                    class="mt-3 text-[9px] font-black uppercase tracking-widest text-black border border-black px-4 py-2 hover:bg-black hover:text-white transition-all">
                                    ↑ Use This Prompt
                                </button>
                            </details>
                        </section>

                        <!-- API Keys -->
                        <section>
                            <span class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-6 block">API Keys</span>
                            <div class="space-y-8">
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        OpenRouter Key
                                        <a href="https://openrouter.ai/keys" target="_blank" class="ml-2 text-blue-400 hover:underline normal-case font-normal">→ Get Key</a>
                                    </label>
                                    <input type="password" name="or_key" value="<?= htmlspecialchars($s['openrouter_key'] ?? '') ?>"
                                        class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-xs font-mono text-black transition-all"
                                        placeholder="sk-or-v1-...">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        NVIDIA API Key
                                        <a href="https://integrate.api.nvidia.com" target="_blank" class="ml-2 text-blue-400 hover:underline normal-case font-normal">→ Get Key</a>
                                    </label>
                                    <input type="password" name="nv_key" value="<?= htmlspecialchars($s['nvidia_key'] ?? '') ?>"
                                        class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-xs font-mono text-black transition-all"
                                        placeholder="nvapi-...">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        Google Gemini Key
                                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="ml-2 text-blue-400 hover:underline normal-case font-normal">→ Get Key</a>
                                    </label>
                                    <input type="password" name="gm_key" value="<?= htmlspecialchars($s['gemini_key'] ?? '') ?>"
                                        class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-xs font-mono text-black transition-all"
                                        placeholder="AIzaSy...">
                                </div>
                            </div>
                        </section>

                        <!-- Generation Tuning -->
                        <section>
                            <span class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-6 block">Generation Tuning</span>
                            <div class="grid grid-cols-2 gap-10">
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        Temperature (0.0 – 1.0)
                                        <span class="block font-normal text-gray-300 mt-0.5 normal-case">Lower = precise, Higher = creative</span>
                                    </label>
                                    <input type="number" step="0.05" min="0" max="1" name="temp"
                                        value="<?= htmlspecialchars($s['ai_temperature'] ?? '0.7') ?>"
                                        class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                        Max Output Tokens
                                        <span class="block font-normal text-gray-300 mt-0.5 normal-case">4000 = ~3000 words</span>
                                    </label>
                                    <input type="number" min="1000" max="8000" step="500" name="tokens"
                                        value="<?= htmlspecialchars($s['ai_max_tokens'] ?? '4000') ?>"
                                        class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                                </div>
                            </div>
                        </section>

                        <section>
                            <span class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-6 block">Article Follow-Up Chat Model</span>
                            <label class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                Choose the AI model that answers article follow-up questions
                            </label>
                            <select name="ai_chat_model"
                                class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black appearance-none cursor-pointer">
                                <option value="">— Use Default Model —</option>
                                <?php foreach ($models as $m): ?>
                                    <option value="<?= htmlspecialchars($m['model_string']) ?>"
                                        <?= (($s['ai_chat_model'] ?? '') === $m['model_string']) ? 'selected' : '' ?>>
                                        [<?= strtoupper($m['provider']) ?>] <?= htmlspecialchars($m['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-[9px] text-gray-400 leading-relaxed mt-3">
                                If blank, the default article generation model will be used for follow-up chat answers.
                            </p>
                        </section>

                    </form>

                    <!-- Model Reference Table -->
                    <section>
                        <span class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-6 block">Recommended Models Reference</span>
                        <div class="border border-gray-100 overflow-x-auto">
                            <table class="w-full text-[10px]">
                                <thead><tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="text-left p-3 font-black uppercase tracking-widest text-gray-500">Provider</th>
                                    <th class="text-left p-3 font-black uppercase tracking-widest text-gray-500">Model String</th>
                                    <th class="text-left p-3 font-black uppercase tracking-widest text-gray-500">Best For</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr><td class="p-3 font-bold text-[#FF0033]">OpenRouter</td><td class="p-3 font-mono">anthropic/claude-3.5-sonnet</td><td class="p-3 text-gray-500">Best overall quality</td></tr>
                                    <tr><td class="p-3 font-bold text-[#FF0033]">OpenRouter</td><td class="p-3 font-mono">google/gemini-flash-1.5</td><td class="p-3 text-gray-500">Fast + cheap</td></tr>
                                    <tr><td class="p-3 font-bold text-[#FF0033]">OpenRouter</td><td class="p-3 font-mono">meta-llama/llama-3.1-70b-instruct</td><td class="p-3 text-gray-500">Free tier available</td></tr>
                                    <tr><td class="p-3 font-bold text-blue-600">NVIDIA</td><td class="p-3 font-mono">minimaxai/minimax-m3</td><td class="p-3 text-gray-500">Long context</td></tr>
                                    <tr><td class="p-3 font-bold text-green-700">Gemini</td><td class="p-3 font-mono">gemini-1.5-pro</td><td class="p-3 text-gray-500">Deep reasoning</td></tr>
                                    <tr><td class="p-3 font-bold text-green-700">Gemini</td><td class="p-3 font-mono">gemini-1.5-flash</td><td class="p-3 text-gray-500">Fast generation</td></tr>
                                    <tr><td class="p-3 font-bold text-green-700">Gemini</td><td class="p-3 font-mono">gemini-2.0-flash</td><td class="p-3 text-gray-500">Latest + fast</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- RIGHT: Model Registry -->
                <div class="lg:col-span-5 space-y-14">

                    <!-- Add Model Form -->
                    <section>
                        <span class="text-[9px] font-black uppercase tracking-[.3em] text-[#FF0033] mb-6 block">Register AI Model</span>
                        <form method="POST" class="space-y-7">
                            <input type="hidden" name="add_model" value="1">
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 mb-2">Display Name</label>
                                <input type="text" name="m_name" required placeholder="e.g. Gemini 2.0 Flash"
                                    class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 mb-2">Model String (exact API identifier)</label>
                                <input type="text" name="m_string" required placeholder="e.g. gemini-2.0-flash"
                                    class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-mono text-gray-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-400 mb-2">Provider</label>
                                <select name="m_prov" required
                                    class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black appearance-none cursor-pointer">
                                    <option value="openrouter">OpenRouter</option>
                                    <option value="nvidia">NVIDIA</option>
                                    <option value="gemini">Google Gemini</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full py-4 border-2 border-black text-[9px] font-black uppercase tracking-[.3em] text-black hover:bg-black hover:text-white transition-all">
                                + Register Model
                            </button>
                        </form>
                    </section>

                    <!-- Model Inventory -->
                    <section>
                        <span class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-6 block">
                            Model Inventory <span class="text-black font-black">(<?= count($models) ?>)</span>
                        </span>
                        <div class="space-y-2">
                            <?php if(empty($models)): ?>
                                <p class="text-[9px] text-gray-300 italic font-black uppercase tracking-widest">No models registered.</p>
                            <?php endif; ?>
                            <?php foreach($models as $m): ?>
                                <?php
                                    $provColor = match($m['provider']) {
                                        'gemini'     => 'text-green-700',
                                        'nvidia'     => 'text-blue-600',
                                        default      => 'text-[#FF0033]'
                                    };
                                ?>
                                <div class="flex items-center justify-between py-4 border-b border-gray-50 group hover:border-black transition-all">
                                    <div class="min-w-0 pr-3">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="text-[8px] font-black uppercase tracking-widest <?= $provColor ?>"><?= htmlspecialchars($m['provider']) ?></span>
                                            <h4 class="text-[11px] font-black text-black uppercase truncate"><?= htmlspecialchars($m['name']) ?></h4>
                                        </div>
                                        <p class="text-[9px] font-mono text-gray-400 truncate"><?= htmlspecialchars($m['model_string']) ?></p>
                                    </div>
                                    <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('Remove this model?')"
                                        class="text-gray-200 hover:text-red-500 transition-colors p-1.5 shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Quick Test -->
                    <section>
                        <span class="text-[9px] font-black uppercase tracking-[.3em] text-gray-400 mb-4 block">Connection Tester</span>
                        <div class="border border-dashed border-gray-200 p-4 space-y-4">
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block">Select Provider to Test</label>
                                <select id="testProvider" class="w-full bg-transparent border-b border-gray-200 py-2 text-[10px] font-bold outline-none appearance-none">
                                    <option value="openrouter">OpenRouter</option>
                                    <option value="nvidia">NVIDIA</option>
                                    <option value="gemini">Gemini</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block">Model String</label>
                                <input type="text" id="testModel" placeholder="e.g. gemini-1.5-flash"
                                    class="w-full bg-transparent border-b border-gray-200 py-2 text-[10px] font-mono outline-none">
                            </div>
                            <button type="button" onclick="testConnection()"
                                class="w-full py-3 border border-black text-[9px] font-black uppercase tracking-widest hover:bg-black hover:text-white transition-all">
                                Ping Connection
                            </button>
                            <div id="testResult" class="text-[10px] font-mono text-gray-600 min-h-[20px]"></div>
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
function pasteDefaultPrompt() {
    const box = document.getElementById('defaultPromptBox');
    const ta = document.querySelector('textarea[name="prompt"]');
    if (ta && box) {
        ta.value = box.textContent;
        ta.focus();
        alert('Default prompt loaded into the field. Click "Sync Core Settings" to save.');
    }
}

async function testConnection() {
    const provider = document.getElementById('testProvider').value;
    const model    = document.getElementById('testModel').value.trim();
    const result   = document.getElementById('testResult');
    if (!model) { result.textContent = '⚠ Enter a model string first.'; return; }
    result.textContent = '[ Pinging... ]';
    try {
        const res = await fetch('/admin/ai/proxy.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                command: 'Say hello in JSON: {"title":"Hello","slug":"hello","keyword":"hello","desc":"test","content":"<p>Hello</p>","meta_title":"Hello"}',
                content: '', model, provider, keyword: 'test', category: 'Test', action: 'generate'
            })
        });
        result.style.color = '#16a34a';
        result.textContent = '[ ✓ Connection OK — HTTP ' + res.status + ' ]';
    } catch(e) {
        result.style.color = '#dc2626';
        result.textContent = '[ ✗ Failed: ' + e.message + ' ]';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
