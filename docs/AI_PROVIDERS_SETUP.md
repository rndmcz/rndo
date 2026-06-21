# AI Content Generation Setup Guide

This guide helps you configure AI API providers for the **randomous CMS** content generation system. The platform supports three enterprise-grade AI providers for SEO-optimized content creation.

## Overview

The randomous CMS includes a powerful AI integration system that can generate:
- ✅ SEO-optimized articles (1000+ words)
- ✅ Meta titles, descriptions, and slugs
- ✅ JSON-LD schema markup for rich snippets
- ✅ Chatbot-indexable context summaries
- ✅ LSI keyword suggestions
- ✅ Content rewrites and expansions

Supported providers:
1. **OpenRouter** (recommended) - Access to 50+ models including Claude, Gemini, Llama
2. **Google Gemini** - Google's latest multimodal models
3. **NVIDIA** - Enterprise-grade language models via NVIDIA's API

---

## 1. OpenRouter (Recommended)

**Why choose OpenRouter?**
- Access to 100+ models (Claude, Gemini, Llama, Mistral, etc.)
- Best pricing through unified routing
- No vendor lock-in
- Free trial credits available

### Step 1: Create Account & Get API Key

1. Visit [https://openrouter.ai](https://openrouter.ai)
2. Click **Sign Up** and create an account
3. Go to **Settings** → **Keys**
4. Click **Create Key** to generate an API key
5. Copy the key starting with `sk-or-v1-`

### Step 2: Add Funds (Recommended Models Need Credit)

1. Go to **Billing** → **Credits**
2. Add payment method and deposit $5-10 for testing
3. Free tier ($5 credit) available for first-time users

### Step 3: Enter Key in Admin Panel

1. Log in to randomous admin at `/admin/`
2. Navigate to **AI Configuration** (Neural Core → AI Configuration)
3. Paste your key in the **OpenRouter Key** field
4. Click **Sync Core Settings**

### Step 4: Register Recommended Models

In the **Register AI Model** section, add these popular models:

| Display Name | Model String | Best For |
|---|---|---|
| Claude 3.5 Sonnet | `anthropic/claude-3.5-sonnet` | Best overall quality & reasoning |
| Gemini Flash 1.5 | `google/gemini-1.5-flash` | Fast & cost-effective |
| Llama 3.1 70B | `meta-llama/llama-3.1-70b-instruct` | Free tier eligible |
| GPT-4 Turbo | `openai/gpt-4-turbo` | Premium performance |

### Example OpenRouter Integration

The proxy automatically formats requests for OpenRouter:

```php
$url = 'https://openrouter.ai/api/v1/chat/completions';
$headers = [
    'Authorization: Bearer YOUR_KEY',
    'HTTP-Referer: https://randomous.com',
    'X-Title: randomous CMS'
];
$payload = [
    'model'       => 'anthropic/claude-3.5-sonnet',
    'stream'      => true,
    'temperature' => 0.7,
    'max_tokens'  => 4000,
    'messages'    => [
        ['role' => 'system', 'content' => 'You are an expert SEO writer...'],
        ['role' => 'user', 'content' => 'Generate an article about...'],
    ],
];
```

---

## 2. Google Gemini

**Why choose Gemini?**
- Free tier with generous limits (60 requests/minute)
- Multimodal capabilities (text, images, audio)
- Excellent for semantic understanding
- No credit card required for free tier

### Step 1: Get Free API Key

1. Visit [https://aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)
2. Click **Create API Key**
3. Select or create a Google Cloud project
4. Copy your API key (starts with `AIzaSy`)
5. Enable the Generative Language API for your project

### Step 2: Review Free Tier Limits

- **60 requests per minute** (free tier)
- **1 million tokens per day** across all methods
- No credit card required
- Upgrade to paid plan for higher limits

### Step 3: Enter Key in Admin Panel

1. Log in to randomous admin
2. Go to **AI Configuration**
3. Paste your Gemini API key in the **Google Gemini Key** field
4. Click **Sync Core Settings**

### Step 4: Add Gemini Models

| Display Name | Model String | Best For |
|---|---|---|
| Gemini 2.0 Flash | `gemini-2.0-flash` | Latest, fastest, free tier eligible |
| Gemini 1.5 Pro | `gemini-1.5-pro` | Deep reasoning & analysis |
| Gemini 1.5 Flash | `gemini-1.5-flash` | Quick responses, free tier eligible |

### Example Gemini Integration

```php
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse&key={$apiKey}";
$payload = [
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => 'Generate an SEO article...']]]
    ],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 4000,
        'responseMimeType'=> 'text/plain',
    ],
    'safetySettings' => [
        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
    ],
];
```

---

## 3. NVIDIA API

**Why choose NVIDIA?**
- Enterprise-grade infrastructure
- Access to curated model marketplace
- Long context window support
- Dedicated support for production workloads

### Step 1: Create Account & Get API Key

1. Visit [https://integrate.api.nvidia.com](https://integrate.api.nvidia.com)
2. Sign in with GitHub or create account
3. Go to **Authorize API** in the top-right
4. Copy your API key (format: `nvapi-...`)

### Step 2: Access Model Catalog

1. Browse available models at [https://build.nvidia.com/discover/models](https://build.nvidia.com/discover/models)
2. Popular text models:
   - `mistral-large-2` - Strong reasoning
   - `minimaxai/minimax-m3` - Long context
   - `meta-llama/llama-3.1-70b-instruct` - Open source

### Step 3: Enter Key in Admin Panel

1. Log in to randomous admin
2. Go to **AI Configuration**
3. Paste your NVIDIA API key in the **NVIDIA API Key** field
4. Click **Sync Core Settings**

### Step 4: Register NVIDIA Models

| Display Name | Model String | Best For |
|---|---|---|
| Mistral Large 2 | `mistral-large-2` | Best accuracy & reasoning |
| Minimax M3 | `minimaxai/minimax-m3` | Long document processing |
| Llama 3.1 70B | `meta-llama/llama-3.1-70b-instruct` | Open source option |

### Example NVIDIA Integration

```php
$url = 'https://integrate.api.nvidia.com/v1/chat/completions';
$headers = [
    'Authorization: Bearer YOUR_KEY',
    'Content-Type: application/json',
];
$payload = [
    'model'       => 'mistral-large-2',
    'stream'      => true,
    'temperature' => 0.7,
    'max_tokens'  => 4000,
    'messages'    => [
        ['role' => 'system', 'content' => 'You are an expert SEO writer...'],
        ['role' => 'user',   'content' => 'Generate an article...'],
    ],
];
```

---

## Configuration Parameters Explained

### Temperature (0.0 – 1.0)
- **0.0** = Deterministic, precise, best for structured content
- **0.5** = Balanced, recommended for general use
- **0.7** = Default, slightly creative
- **1.0** = Maximum creativity, varied outputs

**Recommendation:** Keep at **0.7** for SEO content (balanced accuracy + quality)

### Max Output Tokens
- **4000** = ~3,000 words (default)
- **3000** = ~2,250 words (faster, cheaper)
- **6000** = ~4,500 words (slower, more cost)
- **8000** = ~6,000 words (max recommended)

**Recommendation:** Use **4000** for typical articles

### System Prompt
The master prompt defines how AI models write. The default is:
- Expert SEO strategist persona
- E-E-A-T focused (Experience, Expertise, Authoritativeness, Trustworthiness)
- Optimized for Google ranking + AI chatbot indexing
- Structured output with JSON schema

---

## Testing Your Configuration

### Test from Admin Panel

1. Go to admin dashboard
2. Navigate to the content editor
3. Use the **AI Generate** button to create test content
4. Choose provider, model, and action (Generate/Rewrite/Expand)
5. Monitor the **[Generate...]** button for streaming output

### Test via API

Test your setup with curl:

```bash
# OpenRouter Example
curl -X POST https://openrouter.ai/api/v1/chat/completions \
  -H "Authorization: Bearer YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "anthropic/claude-3.5-sonnet",
    "messages": [{"role": "user", "content": "Say hello"}]
  }'

# Gemini Example
curl -X POST "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "contents": [{"parts": [{"text": "Say hello"}]}]
  }'

# NVIDIA Example
curl -X POST https://integrate.api.nvidia.com/v1/chat/completions \
  -H "Authorization: Bearer YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "mistral-large-2",
    "messages": [{"role": "user", "content": "Say hello"}]
  }'
```

---

## Cost Comparison (Estimated)

| Provider | Model | Input / Output | Est. Cost per 1K words |
|---|---|---|---|
| OpenRouter | Claude 3.5 Sonnet | $3 / $15 per 1M tokens | ~$0.05 |
| OpenRouter | Gemini Flash 1.5 | $0.075 / $0.30 per 1M tokens | ~$0.001 |
| OpenRouter | Llama 3.1 70B | $0.40 / $0.60 per 1M tokens | ~$0.005 |
| Gemini | Gemini 2.0 Flash | Free (tier), $0.075/1M input | Free or ~$0.0001 |
| Gemini | Gemini 1.5 Pro | $1.25 / $5.00 per 1M tokens | ~$0.008 |
| NVIDIA | Mistral Large 2 | $0.20 / $0.60 per 1M tokens | ~$0.0008 |

**Recommendation for budget-conscious setup:**
1. Start with **Gemini Free Tier** (no credit card, 60 req/min)
2. Add **OpenRouter with Llama 3.1** ($0.005/1K words)
3. Optional: **NVIDIA for specialty tasks** (long documents)

---

## Troubleshooting

### "API key for provider X is not configured"
- Verify the key is pasted correctly in AI Configuration
- Ensure no extra spaces at beginning/end
- Click **Sync Core Settings** button

### "API HTTP error 401"
- Check if API key is still valid (hasn't been revoked)
- Verify key belongs to correct provider
- For OpenRouter: Check if you have credits remaining

### "API HTTP error 429 (Rate Limited)"
- You've exceeded the provider's request rate
- Wait a few minutes before retrying
- Check your account's rate limits in provider dashboard

### "Invalid model string"
- Verify the model string exactly matches provider's catalog
- Model names are case-sensitive
- Don't include version numbers unless specified

### "Stream incomplete - JSON parse error"
- Try a different model or provider
- Reduce `max_tokens` to 3000
- Update the system prompt (may be causing format issues)

---

## Production Recommendations

1. **Always use paid tier** in production (higher reliability)
2. **Monitor API costs** - Set budget alerts in provider dashboards
3. **Use Claude or Gemini Pro** for consistent quality
4. **Implement caching** - Store generated articles to reduce API calls
5. **Set up alerting** - Monitor for failed generation jobs
6. **Rotate between providers** - Distribute load, reduce individual rate limits

---

## Support & Resources

- **OpenRouter Docs:** https://openrouter.ai/docs
- **Gemini Docs:** https://ai.google.dev/
- **NVIDIA API Docs:** https://docs.nvidia.com/ai/api/
- **randomous Issues:** Create an issue on GitHub

---

**Last Updated:** 2025  
**Supported Providers:** OpenRouter, Google Gemini, NVIDIA API
