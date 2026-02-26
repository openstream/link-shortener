# Openstream Link Shortener

A free, self-hosted link shortener WordPress plugin — an alternative to Bitly's $30/month custom domain plan.

## Why?

Bitly removed custom domain support from its free plan. If you want to use your own domain (e.g., `opnstre.am/spotify`) to create short links, you now need to pay $30/month or more.

This plugin turns a cheap WordPress installation into a fully functional link shortener with your own domain. No monthly fees — just the cost of a domain and basic hosting.

## How It Works

This plugin is designed for a **dedicated WordPress installation** on a separate short-link domain. It does not add short links to your existing website's posts or pages — it runs independently as a standalone link shortener service.

For example, at Openstream we use `openstream.ch` for our main website and `opnstre.am` as a separate WordPress installation running only this plugin. All short links (like `opnstre.am/spotify`) are managed from the admin area of that dedicated installation and redirect visitors to their destination URLs. The frontend of the site has no public content — it only handles redirects.

## Features

- **Custom slugs** — create memorable short links like `yourdomain.com/spotify`
- **Auto-generated slugs** — random 6-character slugs as a fallback
- **Click tracking** — see how many times each link has been clicked
- **Admin UI** — searchable, sortable link table with copy-to-clipboard
- **Admin-only** — all functionality lives in the WordPress admin area; the frontend only handles redirects
- **Clean uninstall** — removes all data when the plugin is deleted

## Requirements

- WordPress 6.7+ with PHP 8.2+
- Pretty permalinks enabled
- A **dedicated** WordPress installation on your short-link domain (the plugin takes over the admin dashboard and all frontend requests)

## Installation

1. Set up a WordPress installation on your short-link domain (e.g., `opnstre.am`)
2. Clone this repository into the WordPress installation root
3. Activate the plugin from the WordPress admin
4. Make sure pretty permalinks are enabled (Settings → Permalinks → select any structure other than "Plain")

## Usage

After activation, the WordPress admin becomes your link shortener dashboard. Enter a destination URL, optionally set a custom slug, and click "Shorten." There is no frontend interface — the public-facing site only processes redirects for your short links.

## License

GPL v2 or later.
