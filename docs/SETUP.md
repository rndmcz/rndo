# randomous CMS Setup Guide

A complete setup guide for the randomous CMS website, including production deployment, database configuration, AI integration, and basic troubleshooting.

## 1. Project Overview

randomous is a self-hosted PHP content platform focused on educational publishing, SEO optimization, AI-assisted content generation, and contextual article chat. It runs on a PHP backend with reusable view templates, a lightweight admin CMS, and optional AI provider integration.

## 2. Prerequisites

- PHP 8.1+ installed
- PDO extension enabled
- MySQL or MariaDB for production
- Optional: Composer if you plan to extend packages
- Writable `storage/` directory
- A modern browser for admin access

## 3. Production Deployment Setup

### 3.1 Clone the repository

```bash
cd /path/to/projects
git clone https://github.com/cmsrndm/rndo.git
cd rndo
```

### 3.2 Verify files

Confirm the repository contains these key files:

- `index.php`
- `admin/index.php`
- `config/database.php`
- `config/app.php`
- `docs/AI_PROVIDERS_SETUP.md`

### 3.3 Upload to InfinityFree

Upload the repo contents to your InfinityFree account webroot using FTP or the control panel file manager.

### 3.4 Access the site

- Homepage: `https://your-infinityfree-domain/`
- Admin panel: `https://your-infinityfree-domain/admin`

### 3.5 Default admin credentials

- Username: `admin`
- Password: `admin123`

> If you change credentials in the database, use the `settings` or `users` table as appropriate.

## 4. Database Configuration

### 4.1 MySQL / MariaDB (default)

The project is configured for MySQL deployment by default via `config/database.php`.

- Default driver: `mysql`
- InfinityFree host example: `sql310.infinityfree.com`
- Use your assigned database name, user, and password

### 4.2 Database support

This repository is optimized for MySQL/MariaDB deployment. SQLite fallback is not included in the shipped project.

### 4.3 MySQL setup

To use MySQL, update `config/database.php` or set the following constants before loading:

```php
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'your-db-host');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

The MySQL credentials in the repository are placeholders and should be replaced in a production environment.

### 4.4 Database schema

- MySQL schema file: `query.sql`
- Default MySQL schema file: `database/schema.sql`

Import one of these files into your InfinityFree MySQL database.

### 4.5 Troubleshooting database access

- Confirm the MySQL database is accessible from InfinityFree.
- Confirm the MySQL user has privileges for the configured database.
- Check `phpinfo()` or `php -m` to verify the PDO MySQL extension is loaded.

## 5. AI Provider Setup

This project supports OpenRouter, Google Gemini, and NVIDIA.

Use the existing `docs/AI_PROVIDERS_SETUP.md` guide for full provider configuration.

### 5.1 Recommended process

1. Log into the admin panel.
2. Open `AI Configuration`.
3. Add API keys for the providers you want to use.
4. Register one or more models.
5. Save and sync settings.

### 5.2 Key pages

- `admin/ai-config.php` - AI settings page
- `admin/ai/proxy.php` - SSE proxy for AI content generation
- `admin/ai/seo-check.php` - SEO analyzer helper

## 6. Web Server / Deployment Setup

### 6.1 Apache with PHP

The project includes `.htaccess` files to protect storage and admin routes. For production, place the project in your webroot and ensure Apache has:

- `mod_rewrite` enabled
- `AllowOverride All` for the project directory
- PHP 8+ enabled

### 6.2 PHP built-in server

For simple local testing, use:

```bash
php -S 127.0.0.1:8001
```

Then visit `http://127.0.0.1:8001/` in your browser.

### 6.3 Recommended permissions

Set writable permissions for:

```bash
chmod -R 755 storage
chmod -R 755 admin
chmod -R 755 config
```

If you need writable logs or uploads:

```bash
chmod -R 775 storage
```

### 6.4 Production notes

- Disable Xdebug in production
- Use HTTPS for all traffic
- Use a strong admin password
- Secure API keys and remove default credentials
- Protect `storage/` and `config/` from direct access

## 7. Testing the Site

### 7.1 Basic connectivity

Open the homepage and the admin panel in your browser.

### 7.2 Admin editor test

In the admin panel:

- Create a new post
- Save it
- Confirm it appears on the homepage

### 7.3 AI content generation test

- Choose a model in the AI settings
- Use the editor `AutoPilot AI` button
- Verify generated title, content, schema, and chatbot context

### 7.4 Search and category test

- Search for a keyword using `/index.php?route=search&q=term`
- Visit a category page using `index.php?route=category&slug={category}`

## 8. Troubleshooting

### Common issues

- `500 Internal Server Error`
  - Check server error logs
  - Ensure PHP extensions are loaded

- `MySQL connection fails`
  - Check `config/database.php` credentials
  - Verify the InfinityFree host, database name, username, and password

- `AI generation fails`
  - Confirm API key is valid
  - Check admin AI settings and provider sync

- `Admin login issues`
  - Confirm session cookies are enabled
  - Reset the admin credentials in the database if needed

### Useful commands

```bash
php -m
php -v
```

---

## 9. Helpful references

- `README.md` — main repository documentation
- `docs/AI_PROVIDERS_SETUP.md` — AI provider setup
- `docs/SETUP.md` — this full setup guide
- `docs/FEATURES.md` — website feature list
- `docs/ARCHITECTURE.md` — architecture and file structure
