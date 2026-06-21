<?php
/**
 * NEETSTACK NEURAL CORE — ADVANCED STREAMING PROXY v2.0
 * Supports: Google Gemini, NVIDIA NIM, OpenRouter
 * Features: Real word-by-word SSE streaming, structured JSON output,
 *           per-provider format normalization, robust error handling.
 */

// ── 1. SSE / Output Buffering Setup ──────────────────────────────────────────
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
header('Access-Control-Allow-Origin: *');

error_reporting(0);
ini_set('display_errors', 0);
ini_set('zlib.output_compression', 0);
while (ob_get_level()) ob_end_flush();
ob_implicit_flush(true);

// ── 2. Helper: Send SSE event ─────────────────────────────────────────────────
function sseEvent(string $type, $data): void {
    echo "event: {$type}\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
}

function sseError(string $msg): void {
    sseEvent('error', ['message' => $msg]);
    exit;
}

function sseDone(): void {
    sseEvent('done', ['status' => 'complete']);
    exit;
}

function cleanJsonString(string $jsonStr): string {
    $jsonStr = preg_replace('/```(?:json)?/m', '', $jsonStr);
    $jsonStr = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonStr);
    $jsonStr = preg_replace('/,\s*([\}\]])/', '$1', $jsonStr);
    return $jsonStr;
}

function balanceJsonString(string $jsonStr): string {
    $openBraces   = substr_count($jsonStr, '{');
    $closeBraces  = substr_count($jsonStr, '}');
    $openBrackets = substr_count($jsonStr, '[');
    $closeBrackets= substr_count($jsonStr, ']');
    return $jsonStr
        . str_repeat('}', max(0, $openBraces - $closeBraces))
        . str_repeat(']', max(0, $openBrackets - $closeBrackets));
}

// ── 3. Bootstrap ──────────────────────────────────────────────────────────────
try {
    $authFile = __DIR__ . '/../auth.php';
    $dbFile   = dirname(dirname(__DIR__)) . '/config/database.php';

    if (!file_exists($authFile) || !file_exists($dbFile)) {
        sseError('System bootstrap files missing. Check proxy.php path resolution.');
    }
    require_once $authFile;
    require_once $dbFile;
} catch (Throwable $e) {
    sseError('Bootstrap failed: ' . $e->getMessage());
}

// ── 4. Parse & Validate Input ─────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input) sseError('Invalid JSON input received.');

$command  = trim($input['command']  ?? '');
$context  = trim($input['content']  ?? '');
$model    = trim($input['model']    ?? '');
$provider = strtolower(trim($input['provider'] ?? ''));
$keyword  = trim($input['keyword']  ?? '');
$category = trim($input['category'] ?? '');
$action   = trim($input['action']   ?? 'generate'); // generate | seo_check | rewrite | expand

if (!$model || !$provider) sseError('Model and provider are required.');

// Normalize provider names from UI or database
$provider = match($provider) {
    'gemini'         => 'gemini',
    'google'         => 'gemini',
    'nvidia'         => 'nvidia',
    'nv'             => 'nvidia',
    'openrouter'     => 'openrouter',
    'anthropic'      => 'openrouter',
    default          => $provider,
};

if (!in_array($provider, ['gemini','nvidia','openrouter'], true)) {
    sseError("Unsupported provider: {$provider}. Use gemini, nvidia, or openrouter.");
}

// ── 5. Fetch Settings & API Keys ──────────────────────────────────────────────
try {
    $s = db()->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
    if (!$s) sseError('Could not load settings from database.');
} catch (Throwable $e) {
    sseError('Database error: ' . $e->getMessage());
}

$apiKey = match($provider) {
    'gemini'     => $s['gemini_key']     ?? '',
    'nvidia'     => $s['nvidia_key']     ?? '',
    'openrouter' => $s['openrouter_key'] ?? '',
};
if (empty($apiKey)) sseError("API key for provider '" . strtoupper($provider) . "' is not configured in AI Settings.");

$sysPrompt = $s['ai_system_prompt'] ?? '';
$temp      = (float)($s['ai_temperature'] ?? 0.7);
$maxTokens = (int)($s['ai_max_tokens']    ?? 4000);

// ── 6. Build the Master Prompt ────────────────────────────────────────────────
$focusKw   = $keyword   ?: 'the article topic';
$catHint   = $category  ? "Category: {$category}." : '';
$contextBlock = $context ? "\n\n--- EXISTING CONTENT TO IMPROVE ---\n{$context}\n--- END EXISTING CONTENT ---" : '';

$masterPrompt = <<<PROMPT
{$sysPrompt}

=== TASK: {$command} ===
{$catHint}
Focus Keyword: {$focusKw}
{$contextBlock}

=== STRICT OUTPUT FORMAT ===
You MUST respond with ONLY a single valid JSON object. No markdown fences, no preamble, no explanation.
The JSON must have ALL of these exact keys:

{
  "title":           "SEO-optimized H1 headline (60 chars max, includes focus keyword)",
  "meta_title":      "Browser tab title (55-60 chars, includes focus keyword)",
  "slug":            "url-friendly-slug-from-title",
  "keyword":         "primary focus keyword phrase",
  "desc":            "Meta description (150-160 chars, includes keyword, call-to-action)",
  "content":         "FULL article HTML — minimum 1200 words — see requirements below",
  "schema":          "JSON-LD schema markup as a JSON string (Article + FAQPage)",
  "chatbot_context": "Dense plain-text summary of the entire article for AI chatbot indexing (300-500 words)",
  "color":           "#HEX color relevant to the category (e.g. #FF0033 tech, #2E7D32 biology)",
  "seo_score":       95,
  "seo_breakdown":   { "title": 10, "meta": 10, "keyword_density": 15, "headings": 10, "content_length": 15, "readability": 10, "schema": 10, "internal_links": 5, "images_alt": 5, "mobile": 5, "chatbot_context": 5 },
  "lsi_keywords":    ["related keyword 1", "related keyword 2", "related keyword 3", "related keyword 4", "related keyword 5"],
  "word_count":      1500
}

=== CONTENT HTML REQUIREMENTS ===
- Open with a <p class="lead"> paragraph (60-80 words) that answers the search intent directly
- Include exactly one <h2> "What is [topic]?" or introduction section
- Minimum 4 <h2> sections with detailed <p> paragraphs (150+ words each)
- Each <h2> may contain <h3> subsections for depth
- Include at least one <ul> or <ol> list with 4+ items
- Include one <blockquote> with an expert insight or key fact
- Include a <table> if the topic has comparative data
- End with an <h2>Frequently Asked Questions</h2> section with 3-5 <h3>Q</h3><p>A</p> pairs
- Use the focus keyword naturally 3-5 times in body text (not forced)
- Include 2-3 LSI/related keywords naturally
- All headings must be descriptive and contain relevant keywords
- Do NOT include <html>, <head>, <body>, or <script> tags — only article body HTML

=== SEO & AI RANKING REQUIREMENTS ===
- Article must satisfy E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness)
- Answer the searcher's primary intent in the first paragraph
- Structure content for featured snippet eligibility (direct answers, lists, tables)
- Include FAQ schema targets in the FAQ section
- Write for both human readers AND AI chatbot comprehension
- Use transition words for readability (Flesch score 60+)
- Avoid keyword stuffing — density 1-2% max

Now generate the complete JSON object:
PROMPT;

// ── 7. Provider Config ─────────────────────────────────────────────────────────
$url     = '';
$headers = ['Content-Type: application/json'];
$payload = [];

if ($provider === 'gemini') {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse&key={$apiKey}";
    $payload = [
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $masterPrompt]]]
        ],
        'generationConfig' => [
            'temperature'     => $temp,
            'maxOutputTokens' => $maxTokens,
            'responseMimeType'=> 'text/plain',
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
        ],
    ];
} elseif ($provider === 'nvidia') {
    $url = 'https://integrate.api.nvidia.com/v1/chat/completions';
    $headers[] = "Authorization: Bearer {$apiKey}";
    $payload = [
        'model'       => $model,
        'stream'      => true,
        'temperature' => $temp,
        'max_tokens'  => $maxTokens,
        'messages'    => [
            ['role' => 'system', 'content' => 'You are an expert SEO content writer. Always respond with valid JSON only, no markdown.'],
            ['role' => 'user',   'content' => $masterPrompt],
        ],
    ];
} else {
    // OpenRouter
    $url = 'https://openrouter.ai/api/v1/chat/completions';
    $headers[] = "Authorization: Bearer {$apiKey}";
    $headers[] = 'HTTP-Referer: ' . (($_SERVER['HTTPS'] ?? '') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $headers[] = 'X-Title: NEETSTACK CMS';
    $payload = [
        'model'       => $model,
        'stream'      => true,
        'temperature' => $temp,
        'max_tokens'  => $maxTokens,
        'messages'    => [
            ['role' => 'system', 'content' => 'You are an expert SEO content writer. Always respond with valid JSON only, no markdown.'],
            ['role' => 'user',   'content' => $masterPrompt],
        ],
    ];
}

// ── 8. Stream Accumulator ─────────────────────────────────────────────────────
$accumulated = '';
$tokenCount  = 0;

$writeCallback = function($ch, $data) use (&$accumulated, &$tokenCount, $provider) {
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        $token = '';

        if ($provider === 'gemini') {
            // Gemini SSE: data: {"candidates":[{"content":{"parts":[{"text":"..."}]...
            if (str_starts_with($line, 'data: ')) {
                $json = json_decode(substr($line, 6), true);
                $token = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }
        } else {
            // OpenAI-compatible SSE: data: {"choices":[{"delta":{"content":"..."}}]}
            if (str_starts_with($line, 'data: ')) {
                $payload = substr($line, 6);
                if ($payload === '[DONE]') return strlen($data);
                $json = json_decode($payload, true);
                $token = $json['choices'][0]['delta']['content'] ?? '';
            }
        }

        if ($token !== '') {
            $accumulated .= $token;
            $tokenCount++;
            // Send live token to browser every chunk
            sseEvent('token', ['t' => $token, 'n' => $tokenCount]);
        }
    }
    return strlen($data);
};

// ── 9. Execute cURL ───────────────────────────────────────────────────────────
sseEvent('status', ['msg' => 'Neural link established. Streaming content...']);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST          => true,
    CURLOPT_POSTFIELDS    => json_encode($payload),
    CURLOPT_HTTPHEADER    => $headers,
    CURLOPT_RETURNTRANSFER=> false,
    CURLOPT_SSL_VERIFYPEER=> false,
    CURLOPT_TIMEOUT       => 120,
    CURLOPT_CONNECTTIMEOUT=> 15,
    CURLOPT_WRITEFUNCTION => $writeCallback,
]);

$curlResult = curl_exec($ch);
$curlError  = curl_error($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) sseError("cURL error: {$curlError}");
if ($httpCode >= 400) sseError("API HTTP error {$httpCode}. Check your API key and model string.");

// ── 10. Parse & Validate JSON Output ─────────────────────────────────────────
sseEvent('status', ['msg' => 'Stream complete. Parsing AI output...']);

// Strip potential markdown fences if AI was naughty
$clean = preg_replace('/^```(?:json)?\s*/m', '', $accumulated);
$clean = preg_replace('/\s*```$/m', '', $clean);

// Extract the JSON object
$start = strpos($clean, '{');
$end   = strrpos($clean, '}');

if ($start === false || $end === false) {
    $clean = cleanJsonString($accumulated);
    $start = strpos($clean, '{');
    $end   = strrpos($clean, '}');
    if ($start === false || $end === false) {
        sseError("AI did not return a valid JSON object. Raw output: " . substr($accumulated, 0, 300));
    }
}

$jsonStr = substr($clean, $start, $end - $start + 1);
$data    = json_decode($jsonStr, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sseEvent('status', ['msg' => 'Attempting JSON repair...']);
    $jsonStr = cleanJsonString($jsonStr);
    $data    = json_decode($jsonStr, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $jsonStr = balanceJsonString($jsonStr);
        $data    = json_decode($jsonStr, true);
    }
    if (json_last_error() !== JSON_ERROR_NONE) {
        sseError("JSON parse error: " . json_last_error_msg() . ". Raw: " . substr($jsonStr, 0, 200));
    }
}

// Ensure required fields exist
$required = ['title','slug','keyword','desc','content','meta_title','chatbot_context'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        sseError("AI response missing required field: '{$field}'. Please try again.");
    }
}

// ── 11. Send Final Parsed Data ────────────────────────────────────────────────
sseEvent('result', $data);
sseDone();
