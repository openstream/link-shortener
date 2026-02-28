# Openstream Link Shortener

## Project Overview

Self-hosted WordPress plugin that turns a dedicated WordPress installation into a link shortener (like bit.ly). This WordPress instance runs *only* this plugin — no blog posts, no regular content.

## Tech Stack

- WordPress 6.7+ (PHP 8.2+)
- DDEV for local development
- Custom database table for links

## Project Structure

```
wp-content/plugins/openstream-link-shortener/
├── openstream-link-shortener.php       # Main bootstrap
├── uninstall.php                       # Clean uninstall (drops table)
├── readme.txt                          # WordPress plugin directory readme
├── index.php                           # Silence is golden
├── includes/
│   ├── class-openstream-link-shortener.php          # Core: rewrites & redirects
│   ├── class-openstream-link-shortener-admin.php    # Admin: menu takeover, form handling, settings
│   ├── class-openstream-link-shortener-db.php       # Database CRUD
│   └── class-openstream-link-shortener-list-table.php # WP_List_Table for link list
├── views/
│   ├── admin-page.php                  # Main admin page template
│   └── settings-page.php              # Settings page template
└── assets/
    ├── css/admin.css                   # Admin styles
    └── js/admin.js                     # Copy-to-clipboard
```

## Coding Standards

- Follow WordPress Coding Standards (WPCS)
- Use tabs for indentation (not spaces)
- Use Yoda conditions (`if ( 'value' === $var )`)
- snake_case for function/variable names
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize all input: `sanitize_text_field()`, `esc_url_raw()`, `sanitize_title()`
- Use `$wpdb->prepare()` for all database queries
- Prefix everything with `openstream_link_shortener` or `Openstream_Link_Shortener`
- Required capability: `edit_others_posts` (Editors+)

## Dev Commands

```bash
# Start local environment
ddev start

# WP-CLI commands (run inside ddev)
ddev wp plugin activate openstream-link-shortener
ddev wp plugin deactivate openstream-link-shortener
ddev wp rewrite flush
ddev wp rewrite structure '/%postname%/'

# Plugin Check (coding standards & best practices)
ddev wp plugin install plugin-check --activate
ddev wp plugin check openstream-link-shortener
# With specific checks:
ddev wp plugin check openstream-link-shortener --checks=plugin_review_phpcs

# Database
ddev wp db query "SELECT * FROM wp_openstream_links;"

# Test a redirect
curl -I https://opnstre.am.ddev.site/test-slug
```

## Production

- **SSH**: `ssh sandbox@inn.host.ch -p 2121`
- **WordPress root**: `/opnstre.am/`
- **WP-CLI on production**: `ssh sandbox@inn.host.ch -p 2121 "cd /opnstre.am && wp <command>"`
- **Deploy**: push to `main` triggers GitHub Actions (rsync + rewrite flush)

## Key Technical Decisions

- **Rewrite rule**: `^([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]|[a-zA-Z0-9])/?$` — allows dashes in custom slugs; broad regex is safe because this is a dedicated installation
- **301 redirects**: permanent redirects, standard for link shorteners
- **dbDelta()**: requires two spaces before PRIMARY KEY, each field on its own line
- **%i placeholder**: WordPress 6.2+ identifier placeholder for table/column names in `$wpdb->prepare()`
- **ORDER BY pattern**: Use `%i` for the column name and hardcode `ASC`/`DESC` as SQL keywords (branch into separate queries). Do not use `sanitize_sql_orderby()` with string interpolation — the plugin check sniff loses variable sanitization tracking on reassignment.
- **phpcs multi-line suppression**: Use `phpcs:disable`/`phpcs:enable` blocks instead of `phpcs:ignore` for multi-line `$wpdb` statements. `phpcs:ignore` only covers the next line.
- **Clean uninstall**: drops the database table and deletes options on plugin deletion
- **Plugin check target**: 0 errors, 0 warnings from `ddev wp plugin check openstream-link-shortener`

## Versioning & Releases

- Semantic versioning: `MAJOR.MINOR.PATCH` (e.g., 1.0.0, 1.0.1, 1.1.0)
- Version must be updated in **three places** when bumping:
  1. Plugin header `Version:` in `openstream-link-shortener.php`
  2. `OPENSTREAM_LINK_SHORTENER_VERSION` constant in `openstream-link-shortener.php`
  3. `Stable tag:` in `readme.txt`
- Git tag format: `1.0.0` (no `v` prefix)
- GitHub releases: `gh release create <tag> <zip> --title "<tag>" --notes "..."`
- Zip is built from `wp-content/plugins/openstream-link-shortener/` with the plugin folder as root
