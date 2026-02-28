<?php
/**
 * Core functionality for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles rewrite rules, redirects, and slug generation.
 */
class Openstream_Link_Shortener {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ) );
	}

	/**
	 * Register the rewrite rule for short URLs.
	 *
	 * @return void
	 */
	public static function register_rewrite_rules() {
		add_rewrite_rule(
			'^([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]|[a-zA-Z0-9])/?$',
			'index.php?openstream_slug=$matches[1]',
			'top'
		);
	}

	/**
	 * Register the custom query variable.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public static function register_query_vars( $vars ) {
		$vars[] = 'openstream_slug';
		return $vars;
	}

	/**
	 * Handle redirect if a valid slug is requested.
	 *
	 * @return void
	 */
	public static function maybe_redirect() {
		$slug = get_query_var( 'openstream_slug' );

		if ( empty( $slug ) ) {
			return;
		}

		$link = Openstream_Link_Shortener_DB::get_link_by_slug( $slug );

		if ( null === $link ) {
			return;
		}

		Openstream_Link_Shortener_DB::increment_click_count( $link->id );

		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Destination URLs are external by design.
		wp_redirect( $link->destination_url, 301 );
		exit;
	}

	/**
	 * Generate a random alphanumeric slug.
	 *
	 * @param int $length Length of the slug. Default 6.
	 * @return string A unique random slug.
	 */
	public static function generate_slug( $length = 6 ) {
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max_index  = strlen( $characters ) - 1;

		do {
			$slug = '';
			for ( $i = 0; $i < $length; $i++ ) {
				$slug .= $characters[ wp_rand( 0, $max_index ) ];
			}
		} while ( Openstream_Link_Shortener_DB::slug_exists( $slug ) );

		return $slug;
	}
}
