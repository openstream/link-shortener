<?php
/**
 * Uninstall handler for the Openstream Link Shortener.
 *
 * Drops the custom database table and removes options.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall cleanup.
$wpdb->query(
	$wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'openstream_links' )
);

delete_option( 'openstream_link_shortener_db_version' );
