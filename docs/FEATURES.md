# randomous CMS Feature Overview

A complete feature reference for the randomous CMS website.

## 1. Core Website Features

### 1.1 SEO-First Content Platform
- SEO-optimized article publishing workflow
- Meta title and description generation
- Schema markup generation for articles and FAQ content
- Sitemap generation via `sitemap.php`
- Robots configuration via `robots.php`
- LLM crawler-friendly listing via `llms.php`

### 1.2 Content Routing and Pages
- Single front controller at `index.php`
- Direct route handling without relying on Apache rewrite rules
- Home page, search page, category index, article pages
- Search available via query parameter `route=search`
- Category pages via `route=category&slug={category}`

### 1.3 Article Page Enhancements
- Article view tracking and analytics
- Breadcrumb navigation and rich snippet-ready layout
- Related content suggestions
- Floating AI chat interface on article pages
- Mobile-responsive layout

### 1.4 Responsive Front-End Design
- Modular Tailwind-style UI components
- Mobile-first layout and responsive navigation
- Search, category filters, and sidebar widgets
- Fast-loading article cards and homepage sections

## 2. Admin Panel Features

### 2.1 Post Management
- Create, edit, and publish articles
- Title, slug, category, excerpt, and content fields
- Focus keyword and SEO score controls
- Schema markup editing and previewing
- Save chatbot context for article indexing

### 2.2 AI-Assisted Content Workflows
- AutoPilot AI with full article generation
- Rewrite and expand modes for existing content
- Live SSE content streaming in the editor
- Content generation prompts and system instructions
- Automatic fill of title, slug, content, metadata, and schema

### 2.3 AI Configuration
- Multi-provider support: OpenRouter, Google Gemini, NVIDIA
- Provider API key management
- Model registration and selection
- Temperature and max token configuration
- AI settings persistence in the admin panel

### 2.4 SEO and Content Validation
- Live SEO score ring and score breakdown
- Meta description length guidance
- Keyword density monitoring
- Page schema validation hints
- AI chatbot context quality warnings

### 2.5 Admin Utilities
- Dashboard analytics overview
- Post listing with search and pagination
- Category management UI
- Settings management for site-wide configuration
- Login/logout authentication flow

## 3. AI and Chat Features

### 3.1 AI Generation Output
- Structured JSON output from AI generator
- Required fields: title, slug, keyword, desc, content, schema, chatbot_context
- SEO score and breakdown generated alongside content
- LSI keyword suggestions included in results

### 3.2 Chatbot Context Support
- `chatbot_context` field stores dense AI-ready article summary
- Public article pages pass context to `api/ai-chat.php`
- Enables follow-up questions and context-aware chat
- Admin editor displays and syncs chatbot context

### 3.3 AI Chat Interface
- Floating chat widget for article pages
- User question submission and conversation history
- AI chat response streaming from provider-backed endpoint
- Supports follow-up questions with previous context

## 4. Data and Performance Features

### 4.1 Database Support
- MySQL/MariaDB support for production deployments
- PDO connection helpers and driver detection

### 4.2 Analytics and Tracking
- Page visit tracking in local analytics table
- Dashboard metrics for most-viewed content
- Basic event logging and usage tracking

### 4.3 Performance Optimizations
- MySQL deployment optimized for shared hosting
- Static file protection via `.htaccess`
- Minimal PHP template rendering for speed

## 5. Security and Deployment

### 5.1 Basic Security
- Admin authentication with session protection
- Protected configuration and storage directories
- Hidden form field CSRF and session validation patterns
- No public direct DB access

### 5.2 Deployment Best Practices
- Use HTTPS in production
- Disable debug/Xdebug on public servers
- Use strong admin credentials
- Keep API keys secret and outside version control

## 6. Extension and Customization Points

### 6.1 Themes and Layouts
- `views/layouts/head.php` controls metadata and head output
- Reusable component structure under `views/components/`
- Page templates in `views/pages/`

### 6.2 Custom API and AJAX
- `api/load-more-*.php` endpoints support infinite scroll
- `api/ai-chat.php` handles chatbot questions
- Add new JSON endpoints by mirroring existing patterns

### 6.3 Custom AI Prompts
- Modify the AI master prompt in `admin/ai/proxy.php`
- Add new result fields or change output format
- Tune provider payloads for additional AI providers

## 7. Useful Pages and Files

- `index.php` — public front controller
- `admin/index.php` — admin dashboard
- `config/app.php` — global app settings loader
- `config/database.php` — PDO database configuration
- `admin/ai/proxy.php` — SSE AI generation proxy
- `docs/AI_PROVIDERS_SETUP.md` — in-depth AI provider setup guide

---

For full setup instructions, review `docs/SETUP.md`.
