# Performance Optimization Guide

## Overview

This guide focuses on production performance for the randomous CMS running on shared MySQL hosting such as InfinityFree.

The primary goals are:
- minimize PHP response overhead
- optimize MySQL query performance
- protect static assets and sensitive files
- avoid local-only tooling and development artifacts

## Key Performance Priorities

### 1. Disable Xdebug in production

Xdebug is a debugging extension that slows PHP significantly when enabled. On production hosting, ensure it is disabled entirely.

If your host allows custom PHP configuration, verify that `xdebug.mode` is `off` and no Xdebug extension is loaded.

### 2. Use MySQL with optimized queries

This application is configured for MySQL deployment via `config/database.php`.

- Use `query.sql` to create the required tables on InfinityFree.
- Keep the default connection settings in `config/database.php` updated to your host credentials.
- Avoid unnecessary full table scans by using indexed columns for search, category, and slug lookups.

### 3. Keep database schema lean

The schema includes the following production tables:
- `settings`
- `posts`
- `analytics`
- `categories`
- `ai_models`
- `users`

Important production defaults are already included in `query.sql`.

## Performance Checklist

### PHP and web server

- Deploy using Apache with `mod_rewrite` enabled and `AllowOverride All`.
- Disable display of PHP errors in production.
- Log errors to `storage/logs/error.log` if writable.
- Ensure `config/database.php` uses PDO with `ATTR_EMULATE_PREPARES = false`.

### Database

- Import `query.sql` on your InfinityFree MySQL database.
- Ensure the MySQL user has proper privileges for the database.
- Validate that `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` are correct.
- Avoid using SQLite; this repository is optimized for MySQL.

### Security and file protection

- Use the included `.htaccess` rules to protect `storage/` and sensitive PHP files.
- Ensure `storage/` is not publicly accessible.
- Do not keep local development helpers or temporary PHP config files in production.

## Testing and validation

### Quick validation

Use the host or local browser to validate the following pages:
- homepage
- admin login page
- article pages
- search page

### Basic PHP check

Run the following command locally or on the host CLI if available:

```bash
php -v
php -m | grep pdo_mysql
```

Ensure `pdo_mysql` is loaded and there are no fatal configuration errors.

### Sample database test

If you have access to MySQL via phpMyAdmin or a CLI, confirm the schema is present:

```sql
SHOW TABLES;
SELECT COUNT(*) FROM settings;
```

## Recommended production settings

- Use HTTPS for all traffic.
- Set a strong admin password and remove default credentials after the first login.
- Keep `DB_PASS` and API keys secret.
- Monitor logs in `storage/logs/error.log` if the host allows it.

## Troubleshooting

### Common issues

- `500 Internal Server Error`
  - Check server error logs.
  - Confirm PHP extensions are loaded.
  - Confirm `DB_*` values in `config/database.php`.

- MySQL connection fails
  - Verify the InfinityFree database host and credentials.
  - Confirm the database exists and the user has access.

- AI generation fails
  - Verify API keys in the admin settings.
  - Ensure provider endpoints are reachable from the host.

## Notes

This guide is intentionally focused on production deployment and removing local-only tooling. The `dev-server.sh`, `php.ini`, and `test-performance.sh` files are not part of the deployed repository.
