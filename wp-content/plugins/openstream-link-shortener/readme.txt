=== Openstream Link Shortener ===
Contributors: openstream
Tags: link shortener, short links, url shortener, redirects
Requires at least: 6.7
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A self-hosted link shortener that turns a dedicated WordPress installation into a standalone link shortener service.

== Description ==

Openstream Link Shortener turns a dedicated WordPress installation into a link shortener (like bit.ly).

This is an **admin-area only** plugin — there is no frontend interface. The public-facing site only handles redirects for your short links. It is designed to run on a separate WordPress installation on your short-link domain, completely independent from your main website.

For example, you might use `example.com` for your main website and `exmpl.co` as a dedicated WordPress installation running this plugin. All link management happens in the WordPress admin of that dedicated installation.

Features:

* Create short links with auto-generated or custom slugs
* Click tracking
* Admin UI with searchable, sortable link table
* Copy-to-clipboard for short URLs
* Admin-only — all functionality is in the WordPress admin; the frontend only handles redirects
* Settings page to toggle hiding of default WordPress admin menus
* Clean uninstall — removes all data when the plugin is deleted

**Important:** This plugin is designed for a dedicated WordPress installation that serves only as a link shortener. It can optionally take over the admin menu and dashboard (Settings → Link Shortener). It does not create short links for posts or pages — it works as a standalone service.

== Installation ==

1. Set up a dedicated WordPress installation on your short-link domain
2. Upload the `openstream-link-shortener` directory to `wp-content/plugins/`
3. Activate the plugin
4. Ensure pretty permalinks are enabled (Settings → Permalinks)

== Changelog ==

= 1.1.0 =
* Add settings page to toggle hiding of default WordPress admin menus
* Allow dashes in custom short link slugs
* Increase slug column from 20 to 255 characters with automatic migration
* Add automatic rewrite flush on deploy

= 1.0.0 =
* Initial release
* Create short links with auto-generated or custom slugs
* Click tracking
* Searchable, sortable link table
* Copy-to-clipboard for short URLs
* Settings page to toggle hiding of default WordPress admin menus
* Clean uninstall
