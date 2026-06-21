# randomous CMS

A self-hosted PHP content platform built for educational content, SEO-optimized publishing, and AI-assisted article generation with support for multiple LLM providers.

**Brand:** random**o**us (where the final **o** is displayed in bold red #FF0033)

## Project Overview

randomous is a full-featured content management system that provides:
- ✅ Front controller routing via `index.php` and `.htaccess`
- ✅ Clean URLs for articles, categories, search, and AI metadata
- ✅ Modular view system with reusable, responsive Tailwind components
- ✅ MySQL database support for production deployment
- ✅ Admin panel for content management, multi-provider AI integration, and analytics
- ✅ **AI Content Generation** via OpenRouter, Google Gemini, and NVIDIA APIs
- ✅ Server-Sent Events (SSE) streaming for real-time content generation preview
- ✅ SEO-optimized metadata, JSON-LD schema, and sitemap generation
- ✅ AI Chatbot support with context-aware article indexing
- ✅ Analytics dashboard with trending insights

## Quick Start

### 1. Deploy to InfinityFree
Upload the project files to your InfinityFree PHP hosting account.

### 2. Configure MySQL
Update `config/database.php` with your InfinityFree MySQL credentials.

### 3. Import the database schema
Run the SQL in `query.sql` on your InfinityFree MySQL database.

### 4. Access the Site
Open your hosted domain in a browser and visit `/admin` to log in.

### 5. Admin credentials
- Username: `admin`
- Password: `admin123`

### 6. Configure AI (Optional)
- Get a free Gemini API key: https://aistudio.google.com/app/apikey
- Go to Admin → AI Configuration → Paste key → Sync Settings
- Use Admin → Editor → Generate Content button to create articles

### 4. Check Database
- **MySQL schema:** `query.sql`
- **Import SQL:** Run `query.sql` in your InfinityFree MySQL database or use phpMyAdmin

**Full Setup Guide:** See [docs/SETUP.md](docs/SETUP.md)
- **AI Provider Setup:** [docs/AI_PROVIDERS_SETUP.md](docs/AI_PROVIDERS_SETUP.md)
- **Feature Reference:** [docs/FEATURES.md](docs/FEATURES.md)
- **Architecture Guide:** [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## Repository Structure

```
/
├── .htaccess                 # URL rewrite, security headers, directory protection
├── index.php                 # Front controller for public pages
├── sitemap.php               # Dynamic XML sitemap at /sitemap.xml
├── robots.php                # robots.txt at /robots.txt
├── llms.php                  # AI crawler / LLM guide at /llms.txt
├── config/
│   ├── database.php          # PDO singleton and DB configuration
│   └── app.php               # Global settings loader, session config, analytics tracking
├── app/
│   └── helpers/security.php  # HTML escaping, sanitization, CSRF, URL helpers, formatters
├── views/
│   ├── layouts/head.php      # HTML head, SEO metadata, Open Graph, analytics, schema
│   ├── components/           # Reusable UI pieces and widgets
│   └── pages/                # Public page templates
├── api/                      # AJAX endpoints for infinite scrolling and AI chat
├── admin/                    # Admin dashboard and CMS editor files
├── database/schema.sql       # MySQL schema for tables and defaults
├── storage/.htaccess         # Storage protection for logs/cache
└── README.md                 # Project documentation
```

## Public Pages & Routes

The app uses direct PHP routing without Apache rewrite rules:
- `/index.php` → homepage (`index.php?route=home`)
- `/index.php?route=search` → search page
- `/index.php?route=category` → category index
- `/index.php?route=category&slug={name}` → category detail page or article page
- `/sitemap.php` → sitemap
- `/robots.php` → robots file
- `/llms.php` → AI site map document

## Core Public Functionality

### Homepage
- Loads latest articles
- Shows popular articles and top categories
- Uses JavaScript `fetch` to load more posts via `/api/load-more-home.php`
- Generates homepage schema markup and SEO metadata

### Article Page
- Loads article by `slug`
- Increments `views` count
- Renders article content with sharing buttons, related articles, and AI chat UI
- Generates BreadcrumbList and Article JSON-LD schema
- Builds mobile/desktop TOC from headings in article content

### Categories
- `/category` lists all categories
- `/categories/{name}` shows posts in the chosen category
- Uses `/api/load-more-category.php` for pagination

### Search
- `/search?q=term` searches title, excerpt, and content
- Displays results with compact article cards
- Supports incremental loading from `/api/load-more-search.php`
- Adds `noindex, follow` on query results

### AI & Bot Support
- `llms.php` generates a plain-text LLM site guide for AI crawlers
- `api/ai-chat.php` proxies user questions to Anthropic Claude
- Optional AI chat integration on article pages and in the admin editor

## Admin Panel

The `admin/` folder provides a CMS dashboard and AI settings interface.

Key admin files:
- `admin/login.php` / `admin/logout.php` → authentication flow
- `admin/auth.php` → session check for protected pages
- `admin/index.php` → analytics dashboard and stats
- `admin/posts.php` → post listing and management
- `admin/editor.php` → article editor and AI content integration
- `admin/categories.php` → category management
- `admin/settings.php` → site settings
- `admin/ai-config.php` → AI provider and prompt configuration
- `admin/ai/proxy.php` → SSE streaming proxy for AI generation
- `admin/ai/seo-check.php` → SEO analysis helper

### Admin Features
- Create/edit articles with title, excerpt, category, content, SEO metadata, and chatbot context
- AI-powered article generation, rewrite, and expansion
- AI provider configuration for OpenRouter, NVIDIA, and Google Gemini
- SEO score display and prompt template support
- Analytics dashboard with post counts and visitor metrics

## Database Schema

The `database/schema.sql` file creates the following tables:

### `settings`
- `site_title`
- `site_description`
- `site_favicon`
- `google_analytics_id`
- `anthropic_api_key`
- timestamps

### `posts`
- `title`, `slug`, `excerpt`, `content`
- `category`, `tag_color`, `author_name`
- `meta_title`, `meta_desc`, `og_image`, `schema_markup`
- `chatbot_context`, `seo_score`, `views`, `status`
- timestamps and indexes

### `analytics`
- `page_url`, `ip_address`, `user_agent`
- `created_at`

## Configuration

### Database
The project is configured for MySQL deployment by default. `config/database.php` now supports:
- `DB_DRIVER=mysql`
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`
- `query.sql` contains the MySQL schema for InfinityFree deployment

Default MySQL credentials in the current file:
- DB_HOST: `sql310.infinityfree.com`
- DB_NAME: `if0_41370463_ns`
- DB_USER: `if0_41370463`
- DB_PASS: `DG346SToSirb6d`

MySQL setup:
1. Import `query.sql` into your InfinityFree MySQL database.
2. Update `config/database.php` if your InfinityFree credentials differ.
3. Set `SITE_URL` if needed for canonical URLs.

Default admin credentials:
- Username: `admin`
- Password: `admin123`

### AI Provider Configuration

randomous supports three enterprise-grade AI providers for content generation:

#### Supported Providers
1. **OpenRouter** (recommended) - 50+ models including Claude, Gemini, Llama
2. **Google Gemini** - Free tier with 60 requests/minute
3. **NVIDIA API** - Enterprise language models

#### Setup Instructions

**Step 1: Get API Keys**
- OpenRouter: https://openrouter.ai/keys
- Gemini: https://aistudio.google.com/app/apikey
- NVIDIA: https://integrate.api.nvidia.com

**Step 2: Enter in Admin Panel**
1. Log in to admin at `/admin/` (default: admin/admin123)
2. Navigate to **AI Configuration** (Neural Core section)
3. Paste API keys for your chosen provider(s)
4. Click **Sync Core Settings**

**Step 3: Register AI Models**
Use the "Register AI Model" form to add models like:
- OpenRouter: `anthropic/claude-3.5-sonnet`, `google/gemini-1.5-flash`
- Gemini: `gemini-2.0-flash`, `gemini-1.5-pro`
- NVIDIA: `mistral-large-2`, `meta-llama/llama-3.1-70b-instruct`

**Step 4: Generate Content**
Use the admin editor's AI Generate button to create SEO-optimized articles with streaming real-time preview.

#### Cost Estimates
- **Gemini Free Tier**: Free (60 req/min limit)
- **OpenRouter with Llama**: ~$0.005 per 1,000 words
- **Claude 3.5 Sonnet**: ~$0.05 per 1,000 words
- **NVIDIA Mistral Large 2**: ~$0.0008 per 1,000 words

**Full Setup Guide:** See [docs/AI_PROVIDERS_SETUP.md](docs/AI_PROVIDERS_SETUP.md)


### App settings
`config/app.php` loads site settings from the `settings` row with `id = 1` and configures:
- error reporting
- session security
- analytics tracking

### Security helpers
`app/helpers/security.php` provides:
- `e()` for output escaping
- slug, category, and search sanitizers
- CSRF token generation/validation
- URL builders for article/category/search links
- reading time and view formatting

## API Endpoints

These endpoints return HTML fragments or JSON responses:
- `/api/load-more-home.php` → next homepage posts
- `/api/load-more-category.php` → next category posts
- `/api/load-more-search.php` → next search results
- `/api/ai-chat.php` → AI chatbot query proxy

## Security & Deployment Notes

- `.htaccess` protects `/config/`, `/storage/`, and `/app/`
- Uses security headers and CSP for browsers
- Enforces clean URL routing and custom error pages
- `storage/.htaccess` prevents direct file access
- `admin/auth.php` protects admin pages via session

## Setup Steps

1. Upload all project files to the webroot.
2. Create a MySQL database.
3. Import `database/schema.sql`.
4. Update `config/database.php` with your credentials.
5. Ensure Apache rewrite module is enabled.
6. Visit the site and confirm the homepage loads.
7. Optionally configure analytics and AI keys in the `settings` table or via admin.

## Notes

- The app assumes PHP with PDO and cURL support.
- The AI chat proxy uses Anthropic Claude if `anthropic_api_key` is configured.
- Article pages rely on HTML content stored in the database.
- Admin editor is session-based and requires a login mechanism via `admin/login.php`.

## Useful URLs

- Homepage: `/`
- Category list: `/category`
- Sitemap: `/sitemap.xml`
- Robots: `/robots.txt`
- LLM guide: `/llms.txt`
- Admin panel: `/admin/`

---

This README summarizes the full website architecture, public pages, admin capabilities, database setup, and deployment considerations for the extracted codebase.