-- MySQL schema for rndo on InfinityFree
-- Run this SQL in your InfinityFree MySQL database

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `site_title`           VARCHAR(100)  NOT NULL DEFAULT 'randmonous',
  `site_description`     TEXT,
  `site_favicon`         VARCHAR(255),
  `google_analytics_id`  VARCHAR(50),
  `anthropic_api_key`    VARCHAR(100)  COMMENT 'Store your Claude API key here',
  `openrouter_key`       VARCHAR(200) DEFAULT '',
  `nvidia_key`           VARCHAR(200) DEFAULT '',
  `gemini_key`           VARCHAR(200) DEFAULT '',
  `ai_chat_model`        VARCHAR(150) DEFAULT '',
  `ai_system_prompt`     TEXT,
  `ai_temperature`       DECIMAL(3,2) DEFAULT 0.70,
  `ai_max_tokens`        INT DEFAULT 4000,
  `site_url`             VARCHAR(255),
  `author_name`          VARCHAR(100) DEFAULT 'randomous Team',
  `created_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table
CREATE TABLE IF NOT EXISTS `posts` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`            VARCHAR(500) NOT NULL,
  `slug`             VARCHAR(500) NOT NULL UNIQUE,
  `excerpt`          TEXT,
  `content`          LONGTEXT,
  `chatbot_context`  TEXT,
  `author_name`      VARCHAR(100) DEFAULT 'Editorial Team',
  `category`         VARCHAR(100) NOT NULL,
  `tag_color`        VARCHAR(20)  DEFAULT '#FF0033',
  `meta_title`       VARCHAR(500),
  `meta_desc`        TEXT,
  `og_image`         VARCHAR(500),
  `schema_markup`    LONGTEXT     COMMENT 'Custom JSON-LD, overrides auto-generated',
  `focus_keyword`    VARCHAR(255) DEFAULT '',
  `seo_score`        TINYINT UNSIGNED DEFAULT 0,
  `read_time`        VARCHAR(50) DEFAULT '5 Min Read',
  `views`            INT UNSIGNED NOT NULL DEFAULT 0,
  `featured`         TINYINT(1)   NOT NULL DEFAULT 0,
  `status`           ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug`     (`slug`(255)),
  INDEX `idx_category` (`category`),
  INDEX `idx_views`    (`views`),
  INDEX `idx_created`  (`created_at`),
  FULLTEXT INDEX `ft_search` (`title`, `excerpt`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics table
CREATE TABLE IF NOT EXISTS `analytics` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `page_url`    VARCHAR(500),
  `ip_address`  VARCHAR(45),
  `user_agent`  VARCHAR(255),
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_url`        (`page_url`(255)),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI models table
CREATE TABLE IF NOT EXISTS `ai_models` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(100) NOT NULL,
  `model_string` VARCHAR(255) NOT NULL UNIQUE,
  `provider`     VARCHAR(100) NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username`    VARCHAR(100) NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default data
INSERT IGNORE INTO `settings` (`id`, `site_title`, `site_description`, `site_url`, `author_name`, `openrouter_key`, `nvidia_key`, `gemini_key`, `ai_system_prompt`, `ai_temperature`, `ai_max_tokens`, `anthropic_api_key`) VALUES
(1, 'randomous', 'India\'s leading educational platform for competitive exam preparation including NEET, JEE, UPSC and more.', 'http://your-live-domain.com', 'randomous Team', '', '', '', '', 0.7, 4000, '');

INSERT IGNORE INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$12$.8/s8nPOGtBb15MBHGED2Of8WXNHhsZyOP/LRNeo2TzYZzWZe8B3C');
