<?php
/**
 * Plugin Name: Openstream Link Shortener
 * Description: A self-hosted link shortener for a dedicated WordPress installation.
 * Version:     1.1.0
 * Author:      Openstream
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: openstream-link-shortener
 * Requires PHP: 8.2
 * Requires at least: 6.7
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPENSTREAM_LINK_SHORTENER_VERSION', '1.1.0' );
define( 'OPENSTREAM_LINK_SHORTENER_PATH', plugin_dir_path( __FILE__ ) );
define( 'OPENSTREAM_LINK_SHORTENER_URL', plugin_dir_url( __FILE__ ) );

require_once OPENSTREAM_LINK_SHORTENER_PATH . 'includes/class-openstream-link-shortener-db.php';
require_once OPENSTREAM_LINK_SHORTENER_PATH . 'includes/class-openstream-link-shortener.php';
require_once OPENSTREAM_LINK_SHORTENER_PATH . 'includes/class-openstream-link-shortener-admin.php';
require_once OPENSTREAM_LINK_SHORTENER_PATH . 'includes/class-openstream-link-shortener-list-table.php';

register_activation_hook(
	__FILE__,
	function () {
		Openstream_Link_Shortener_DB::create_table();
		Openstream_Link_Shortener::register_rewrite_rules();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		flush_rewrite_rules();
	}
);

Openstream_Link_Shortener::init();
Openstream_Link_Shortener_DB::maybe_upgrade();

if ( is_admin() ) {
	Openstream_Link_Shortener_Admin::init();
}
