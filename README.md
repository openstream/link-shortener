# Openstream Link Shortener

A free, self-hosted link shortener WordPress plugin — an alternative to Bitly's $30/month custom domain plan.

## Why?

Bitly removed custom domain support from its free plan. If you want to use your own domain (e.g., `opnstre.am/spotify`) to create short links, you now need to pay $30/month or more.

This plugin turns a cheap WordPress installation into a fully functional link shortener with your own domain. No monthly fees — just the cost of a domain and basic hosting.

## Features

- **Custom slugs** — create memorable short links like `yourdomain.com/spotify`
- **Auto-generated slugs** — random 6-character slugs as a fallback
- **Click tracking** — see how many times each link has been clicked
- **Admin UI** — searchable, sortable link table with copy-to-clipboard
- **Clean uninstall** — removes all data when the plugin is deleted

## Requirements

- WordPress 6.7+ with PHP 8.2+
- Pretty permalinks enabled
- A dedicated WordPress installation (the plugin takes over the admin dashboard)

## Installation

1. Clone this repository into your WordPress installation root
2. Activate the plugin from the WordPress admin
3. Make sure pretty permalinks are enabled (Settings → Permalinks → select any structure other than "Plain")

## Usage

After activation, the WordPress admin becomes your link shortener dashboard. Enter a destination URL, optionally set a custom slug, and click "Shorten."

## License

GPL v2 or later.
