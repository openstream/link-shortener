<?php
/**
 * Admin functionality for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin menu, form processing, and asset enqueueing.
 */
class Openstream_Link_Shortener_Admin {

	/**
	 * The admin page hook suffix.
	 *
	 * @var string
	 */
	private static $hook_suffix = '';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 99 );
		add_action( 'admin_init', array( __CLASS__, 'redirect_dashboard' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_form_submission' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_delete_action' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the plugin menu and take over the admin sidebar.
	 *
	 * @return void
	 */
	public static function register_menu() {
		global $menu;

		// Remove all menu items except Settings and Profile.
		$keep = array( 'options-general.php', 'profile.php' );
		foreach ( $menu as $key => $item ) {
			if ( ! empty( $item[2] ) && ! in_array( $item[2], $keep, true ) ) {
				remove_menu_page( $item[2] );
			}
			// Remove separators.
			if ( isset( $item[4] ) && false !== strpos( $item[4], 'wp-menu-separator' ) ) {
				unset( $menu[ $key ] );
			}
		}

		// Add the plugin menu at the top.
		self::$hook_suffix = add_menu_page(
			__( 'Link Shortener', 'openstream-link-shortener' ),
			__( 'Link Shortener', 'openstream-link-shortener' ),
			'edit_others_posts',
			'openstream-link-shortener',
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-links',
			2
		);
	}

	/**
	 * Redirect the dashboard to the plugin page.
	 *
	 * @return void
	 */
	public static function redirect_dashboard() {
		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Just checking if a query var exists, no data processing.
		if ( 'index.php' === $pagenow && ! isset( $_GET['openstream_slug'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=openstream-link-shortener' ) );
			exit;
		}
	}

	/**
	 * Handle the shorten form submission.
	 *
	 * @return void
	 */
	public static function handle_form_submission() {
		if ( ! isset( $_POST['openstream_shorten_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['openstream_shorten_nonce'] ) ), 'openstream_shorten_link' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'openstream-link-shortener' ) );
		}

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'openstream-link-shortener' ) );
		}

		$destination_url = isset( $_POST['destination_url'] ) ? esc_url_raw( wp_unslash( $_POST['destination_url'] ) ) : '';

		if ( empty( $destination_url ) ) {
			add_settings_error(
				'openstream_link_shortener',
				'empty_url',
				__( 'Please enter a valid URL.', 'openstream-link-shortener' ),
				'error'
			);
			return;
		}

		$custom_slug = isset( $_POST['custom_slug'] ) ? sanitize_title( wp_unslash( $_POST['custom_slug'] ) ) : '';

		if ( ! empty( $custom_slug ) ) {
			if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $custom_slug ) ) {
				add_settings_error(
					'openstream_link_shortener',
					'invalid_slug',
					__( 'Custom slug must contain only letters and numbers.', 'openstream-link-shortener' ),
					'error'
				);
				return;
			}

			if ( Openstream_Link_Shortener_DB::slug_exists( $custom_slug ) ) {
				add_settings_error(
					'openstream_link_shortener',
					'slug_exists',
					__( 'That custom slug is already in use.', 'openstream-link-shortener' ),
					'error'
				);
				return;
			}

			$slug = $custom_slug;
		} else {
			$slug = Openstream_Link_Shortener::generate_slug();
		}

		$link_id = Openstream_Link_Shortener_DB::insert_link( $slug, $destination_url );

		if ( false === $link_id ) {
			add_settings_error(
				'openstream_link_shortener',
				'insert_failed',
				__( 'Failed to create the short link. Please try again.', 'openstream-link-shortener' ),
				'error'
			);
			return;
		}

		$short_url = home_url( '/' . $slug );

		// Redirect to prevent form resubmission.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => 'openstream-link-shortener',
					'new_link'  => rawurlencode( $short_url ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle link deletion.
	 *
	 * @return void
	 */
	public static function handle_delete_action() {
		if ( ! isset( $_GET['action'] ) || 'delete' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || 'openstream-link-shortener' !== $_GET['page'] ) {
			return;
		}

		$link_id = isset( $_GET['link'] ) ? absint( $_GET['link'] ) : 0;

		if ( 0 === $link_id ) {
			return;
		}

		if ( ! wp_verify_nonce(
			isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '',
			'openstream_delete_link_' . $link_id
		) ) {
			wp_die( esc_html__( 'Security check failed.', 'openstream-link-shortener' ) );
		}

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'openstream-link-shortener' ) );
		}

		Openstream_Link_Shortener_DB::delete_link( $link_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'openstream-link-shortener',
					'deleted' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Enqueue admin CSS and JS on the plugin page only.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( $hook_suffix !== self::$hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'openstream-link-shortener-admin',
			OPENSTREAM_LINK_SHORTENER_URL . 'assets/css/admin.css',
			array(),
			OPENSTREAM_LINK_SHORTENER_VERSION
		);

		wp_enqueue_script(
			'openstream-link-shortener-admin',
			OPENSTREAM_LINK_SHORTENER_URL . 'assets/js/admin.js',
			array(),
			OPENSTREAM_LINK_SHORTENER_VERSION,
			true
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'openstream-link-shortener' ) );
		}

		$list_table = new Openstream_Link_Shortener_List_Table();
		$list_table->prepare_items();

		$new_link = '';
		if ( isset( $_GET['new_link'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only, no data modification.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below with sanitize_text_field + esc_url.
			$new_link = esc_url( sanitize_text_field( rawurldecode( wp_unslash( $_GET['new_link'] ) ) ) );
		}

		include OPENSTREAM_LINK_SHORTENER_PATH . 'views/admin-page.php';
	}
}
