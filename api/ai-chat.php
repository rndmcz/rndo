<?php
/**
 * api/ai-chat.php
 * Proxies questions to Anthropic API using article context
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$prompt  = isset($data['prompt'])  ? mb_substr(strip_tags((string)$data['prompt']),  0, 500) : '';
$context = isset($data['context']) ? mb_substr(strip_tags((string)$data['context']), 0, 4000) : '';
$history = is_array($data['history'] ?? null) ? array_slice($data['history'], -6) : [];

if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(['error' => 'No prompt provided']);
    exit;
}

// Build message history
$messages = [];
foreach ($history as $msg) {
    if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
        $messages[] = [
            'role'    => $msg['role'],
            'content' => mb_substr(strip_tags((string)$msg['content']), 0, 1000),
        ];
    }
}
$messages[] = ['role' => 'user', 'content' => $prompt];

$chatModel = trim(setting('ai_chat_model', '') ?: setting('ai_model', ''));
if (empty($chatModel)) {
    try {
        $chatModel = db()->query("SELECT model_string FROM ai_models ORDER BY provider,name LIMIT 1")->fetchColumn();
    } catch (Throwable $e) {
        $chatModel = '';
    }
}

$provider = '';
try {
    $stmt = db()->prepare("SELECT provider FROM ai_models WHERE model_string = ? LIMIT 1");
    $stmt->execute([$chatModel]);
    $provider = $stmt->fetchColumn() ?: '';
} catch (Throwable $e) {
    $provider = '';
}

if (!$provider) {
    if (str_contains($chatModel, 'gemini') || str_contains($chatModel, 'google/')) {
        $provider = 'gemini';
    } elseif (str_contains($chatModel, 'nvidia') || str_contains($chatModel, 'minimax') || str_contains($chatModel, 'llama')) {
        $provider = 'nvidia';
    } else {
        $provider = 'openrouter';
    }
}

$apiKey = match ($provider) {
    'gemini'     => setting('gemini_key', ''),
    'nvidia'     => setting('nvidia_key', ''),
    'openrouter' => setting('openrouter_key', ''),
    default      => ''
};

if (empty($apiKey)) {
    http_response_code(503);
    echo json_encode(['error' => 'AI chat service is not configured for provider: ' . strtoupper($provider)]);
    exit;
}

$systemPrompt = "You are an expert educational assistant for " . setting('site_title', 'randomous') . ". Answer follow-up questions about the article using the provided article chatbot context when relevant. If the answer is not in the article, still answer clearly and accurately.\n\nArticle context:\n{$context}";

try {
    if ($provider === 'openrouter') {
        $payload = json_encode([
            'model'    => $chatModel,
            'messages' => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $messages
            ),
            'temperature' => 0.3,
            'max_tokens'  => 800,
        ]);

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            throw new RuntimeException('API returned HTTP ' . $httpCode . ' - ' . substr($response ?: '', 0, 200));
        }

        $result = json_decode($response, true);
        $answer = $result['choices'][0]['message']['content'] ?? $result['choices'][0]['message']['content']['parts'][0] ?? $result['choices'][0]['delta']['content'] ?? '';
    } elseif ($provider === 'nvidia') {
        $payload = json_encode([
            'model'       => $chatModel,
            'temperature' => 0.3,
            'max_tokens'  => 800,
            'messages'    => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $messages
            ),
        ]);

        $ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            throw new RuntimeException('API returned HTTP ' . $httpCode . ' - ' . substr($response ?: '', 0, 200));
        }

        $result = json_decode($response, true);
        $answer = $result['choices'][0]['message']['content'] ?? $result['choices'][0]['message']['content']['text'] ?? '';
    } else {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($chatModel) . ':generate?key=' . urlencode($apiKey);
        $payload = json_encode([
            'prompt' => [
                'messages' => array_merge(
                    [['author' => 'system', 'content' => $systemPrompt]],
                    array_map(fn($msg) => ['author' => $msg['role'] === 'assistant' ? 'assistant' : 'user', 'content' => $msg['content']], $messages)
                )
            ],
            'temperature' => 0.3,
            'max_output_tokens' => 800,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            throw new RuntimeException('API returned HTTP ' . $httpCode . ' - ' . substr($response ?: '', 0, 200));
        }

        $result = json_decode($response, true);
        $answer = $result['candidates'][0]['output'][0]['content'] ?? $result['candidates'][0]['content'] ?? '';
    }

    if (empty($answer)) {
        throw new RuntimeException('AI returned an empty answer.');
    }

    echo json_encode(['answer' => trim($answer)]);

} catch (Throwable $e) {
    error_log('[AI Chat Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'AI service temporarily unavailable']);
}
