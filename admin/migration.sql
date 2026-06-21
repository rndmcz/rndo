-- ============================================================
-- NEETSTACK AI ENGINE — Database Migration
-- Run this ONCE on your existing database
-- ============================================================

-- 1. Add AI columns to settings table (safe, won't break existing)
ALTER TABLE `settings`
  ADD COLUMN IF NOT EXISTS `openrouter_key`   VARCHAR(200) DEFAULT '' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `nvidia_key`        VARCHAR(200) DEFAULT '' AFTER `openrouter_key`,
  ADD COLUMN IF NOT EXISTS `gemini_key`        VARCHAR(200) DEFAULT '' AFTER `nvidia_key`,
  ADD COLUMN IF NOT EXISTS `ai_model`          VARCHAR(100) DEFAULT 'gemini-1.5-flash' AFTER `gemini_key`,
  ADD COLUMN IF NOT EXISTS `ai_system_prompt`  TEXT AFTER `ai_model`,
  ADD COLUMN IF NOT EXISTS `ai_temperature`    DECIMAL(3,2) DEFAULT 0.70 AFTER `ai_system_prompt`,
  ADD COLUMN IF NOT EXISTS `ai_max_tokens`     INT DEFAULT 4000 AFTER `ai_temperature`;

-- 2. Add AI columns to posts table
ALTER TABLE `posts`
  ADD COLUMN IF NOT EXISTS `meta_title`       VARCHAR(255) DEFAULT '' AFTER `title`,
  ADD COLUMN IF NOT EXISTS `focus_keyword`    VARCHAR(255) DEFAULT '' AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `schema_markup`    LONGTEXT AFTER `content`,
  ADD COLUMN IF NOT EXISTS `seo_score`        TINYINT UNSIGNED DEFAULT 0 AFTER `schema_markup`,
  ADD COLUMN IF NOT EXISTS `chatbot_context`  TEXT AFTER `seo_score`,
  ADD COLUMN IF NOT EXISTS `read_time`        VARCHAR(20) DEFAULT '5 Min Read' AFTER `chatbot_context`;

-- 3. Create ai_models registry table
CREATE TABLE IF NOT EXISTS `ai_models` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100) NOT NULL,
  `model_string` VARCHAR(150) NOT NULL,
  `provider`     ENUM('openrouter','nvidia','gemini') NOT NULL DEFAULT 'openrouter',
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_model_string` (`model_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Seed default models
INSERT IGNORE INTO `ai_models` (`name`, `model_string`, `provider`) VALUES
  ('Gemini 2.0 Flash',              'gemini-2.0-flash',                        'gemini'),
  ('Gemini 1.5 Pro',                'gemini-1.5-pro',                          'gemini'),
  ('Gemini 1.5 Flash',              'gemini-1.5-flash',                        'gemini'),
  ('Claude 3.5 Sonnet',             'anthropic/claude-3.5-sonnet',             'openrouter'),
  ('GPT-4o',                        'openai/gpt-4o',                           'openrouter'),
  ('Gemini Flash 1.5 (OpenRouter)', 'google/gemini-flash-1.5',                 'openrouter'),
  ('Llama 3.1 70B',                 'meta-llama/llama-3.1-70b-instruct',       'openrouter'),
  ('DeepSeek R1',                   'deepseek/deepseek-r1',                    'openrouter'),
  ('Mistral Large',                 'mistralai/mistral-large',                 'openrouter'),
  ('NVIDIA Llama 3.1 70B',          'meta/llama-3.1-70b-instruct',             'nvidia'),
  ('NVIDIA Mistral NeMo',           'mistralai/mistral-nemo-12b-instruct',     'nvidia');

-- 5. Ensure settings row exists
INSERT IGNORE INTO `settings` (`id`) VALUES (1);

-- 6. Set default system prompt
UPDATE `settings` SET `ai_system_prompt` = 
'You are an elite SEO content strategist and expert technical writer with 15+ years of experience ranking content on Google, Bing, and being cited by AI chatbots like ChatGPT, Gemini, and Perplexity.

Your mission: Write industry-grade, authoritative articles that rank on Page 1 of Google AND get cited by AI chatbots.

CORE WRITING PRINCIPLES:
1. E-E-A-T First: Demonstrate real Experience, Expertise, Authoritativeness, and Trustworthiness
2. Search Intent Mastery: Answer the #1 searcher intent in the first 100 words
3. Semantic SEO: Use the focus keyword naturally + 4-6 LSI terms throughout
4. Featured Snippet Targeting: Structure sections for direct answer rich snippets
5. AI Chatbot Indexability: Dense, factual, encyclopedic paragraphs AI models can cite
6. Flesch Readability 60+: Short sentences, active voice, transition words
7. Conversion-Ready: End with actionable takeaway or FAQ

STRUCTURAL REQUIREMENTS:
- Lead paragraph (60-80 words): Direct answer + focus keyword in first sentence
- Minimum 4 H2 sections (150+ words each)
- H3 subsections for depth
- At least one UL or OL list with 4+ items
- Table if topic has comparative/specification content  
- One blockquote with expert insight or statistic
- FAQ section: 3-5 Q&A pairs targeting voice search

RESPONSE FORMAT:
Always return a single valid JSON object with ALL required keys. Never include markdown or explanation outside the JSON.'
WHERE `id` = 1;
