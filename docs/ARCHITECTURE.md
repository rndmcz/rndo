# randomous CMS Architecture Guide

This document explains the website architecture, file structure, routing, data flow, and component responsibilities.

## 1. Application Architecture

randomous is built as a lightweight PHP CMS with the following architectural patterns:

- **Front controller** pattern for public pages via `index.php`
- **Modular view templates** for consistent UI rendering
- **Admin panel** separated under the `admin/` directory
- **API endpoints** for asynchronous loading and chatbot interaction
- **Configuration-based database loading** for MySQL deployments
- **AI proxy** layer for secure streaming AI generation

## 2. Directory Structure

```
/ (project root)
├── .htaccess               # Root web server and security rules
├── index.php               # Public front controller
├── sitemap.php             # XML sitemap generator
├── robots.php              # robots.txt output
├── llms.php                # AI crawler/LLM site map text
├── admin/                  # Admin dashboard and content editor
│   ├── index.php
│   ├── login.php
│   ├── editor.php
│   ├── ai-config.php
│   ├── ai/                 # AI-related backend routes
│   └── includes/           # reusable admin templates
├── api/                    # AJAX and chatbot endpoints
├── app/                    # application helpers and utilities
├── config/                 # database and app configuration
├── database/               # schema files and migrations
├── docs/                   # documentation files
├── storage/                # runtime data and logs
└── views/                  # public page templates and components
```

## 3. Routing and Public Pages

### 3.1 Public Front Controller

- Managed by `index.php`
- Uses query parameter `route` to choose which page to render
- Example routes:
  - `?route=home`
  - `?route=search`
  - `?route=category`
  - `?route=article`

### 3.2 Route Parameters

- `route=search` uses query `q` for search terms
- `route=category` may use `slug` to determine page context
- Article pages are loaded by slug and category mapping

### 3.3 Auxiliary Endpoints

- `sitemap.php` generates XML sitemap output
- `robots.php` writes robots rules dynamically
- `llms.php` provides a text-based AI crawlers guide

## 4. Data Layer and Configuration

### 4.1 Database Configuration

- All database logic is in `config/database.php`
- The app is configured for MySQL via `DB_DRIVER`, with `mysql` as the default driver
- Connection helper function `db()` returns a PDO instance

### 4.2 Data Models

No ORM is used. The app uses simple SQL queries and prepared statements.

### 4.3 Key Tables

- `settings` — stores site metadata, AI keys, API configuration, and defaults
- `posts` — stores articles, SEO fields, chatbot context, and display metadata
- `analytics` — stores visit and page access events

## 5. Admin CMS Architecture

### 5.1 Authentication

- `admin/login.php` authenticates admin users
- `admin/auth.php` protects admin-only pages
- Sessions are used to maintain logged-in state

### 5.2 Admin UI Flow

- `admin/index.php` shows dashboard metrics and recent posts
- `admin/posts.php` lists posts with edit/delete actions
- `admin/editor.php` is the page editor with AI assistance
- `admin/settings.php` controls site-wide configuration

### 5.3 Editor Data Flow

1. Admin enters title, content, and SEO metadata
2. The editor may call `admin/ai/proxy.php` for AI generation
3. AI proxy returns JSON data in SSE stream
4. Client-side JS updates editor fields
5. The post is saved to the `posts` table on submit

## 6. AI Proxy and Streaming

### 6.1 Purpose of `admin/ai/proxy.php`

- Acts as a secure server-side proxy for AI requests
- Sends configured prompts to external AI providers
- Normalizes response streaming into SSE events
- Protects API keys from client-side exposure

### 6.2 Supported providers

- Google Gemini
- NVIDIA
- OpenRouter

### 6.3 AI output format

The proxy expects a strict JSON object containing:

- `title`
- `meta_title`
- `slug`
- `keyword`
- `desc`
- `content`
- `schema`
- `chatbot_context`
- `color`
- `seo_score`
- `seo_breakdown`
- `lsi_keywords`
- `word_count`

### 6.4 SSE client handling

- `admin/js/main.js` opens the proxy stream
- It reads `event: status`, `event: token`, `event: result`, and `event: done`
- `result` updates editor fields in real time

## 7. Public Chat & AI Context

### 7.1 Chatbot context storage

- `chatbot_context` is stored with each article
- It is a dense summary designed for follow-up chat queries

### 7.2 Article chatbot flow

- Public article pages load the stored context
- `api/ai-chat.php` uses it to answer reader questions
- Chat history is preserved for context-aware follow-up

## 8. Page Components

### 8.1 Views and templates

- `views/layouts/head.php` sets metadata, Open Graph tags, and CSS/JS includes
- `views/components/` contains reusable UI blocks like headers, cards, and sidebars
- `views/pages/` contains page-specific content templates

### 8.2 Component responsibilities

- `views/components/article-card.php` renders post previews
- `views/components/floating-chat.php` renders the chat widget
- `views/components/sidebar.php` renders navigation and filters

## 9. Security and Protection

### 9.1 `.htaccess` rules

- Root `.htaccess` controls rewrite and security for the public site
- `admin/.htaccess` protects admin routes and upload access
- `storage/.htaccess` denies direct access to storage files

### 9.2 PHP-level protection

- Database access is only available through the app
- Sensitive config values are loaded from `config/database.php`
- Admin-only pages require a valid session login

## 10. Extending the Application

### 10.1 Adding new pages

- Add a route in `index.php`
- Create a corresponding view template in `views/pages/`
- Update navigation or helpers as needed

### 10.2 Adding new API endpoints

- Create a new file in `api/`
- Return JSON or HTML fragment depending on the endpoint
- Use `fetch()` from the front end to call the endpoint

### 10.3 Adding new AI features

- Update `admin/ai/proxy.php` to support additional model providers
- Extend the master prompt and JSON output schema
- Update `admin/js/main.js` to handle new response fields

---

For setup instructions, see `docs/SETUP.md`.
