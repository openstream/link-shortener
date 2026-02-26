<?php
/**
 * List table for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Displays links in a WordPress admin list table.
 */
class Openstream_Link_Shortener_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'link',
				'plural'   => 'links',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get the table columns.
	 *
	 * @return array Column slug => label.
	 */
	public function get_columns() {
		return array(
			'short_url'       => __( 'Short URL', 'openstream-link-shortener' ),
			'destination_url' => __( 'Destination URL', 'openstream-link-shortener' ),
			'click_count'     => __( 'Clicks', 'openstream-link-shortener' ),
			'created_at'      => __( 'Created', 'openstream-link-shortener' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array Column slug => array( orderby, default_desc ).
	 */
	public function get_sortable_columns() {
		return array(
			'short_url'   => array( 'slug', false ),
			'click_count' => array( 'click_count', true ),
			'created_at'  => array( 'created_at', true ),
		);
	}

	/**
	 * Prepare items for display.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$per_page = 20;
		$page     = $this->get_pagenum();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce not needed for read-only list table display.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sorting params for read-only display.
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sorting params for read-only display.
		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';

		$this->items = Openstream_Link_Shortener_DB::get_links(
			array(
				'per_page' => $per_page,
				'page'     => $page,
				'orderby'  => $orderby,
				'order'    => $order,
				'search'   => $search,
			)
		);

		$total_items = Openstream_Link_Shortener_DB::count_links( $search );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Render the short URL column.
	 *
	 * @param object $item The link row.
	 * @return string Column HTML.
	 */
	public function column_short_url( $item ) {
		$short_url = home_url( '/' . $item->slug );

		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'   => 'openstream-link-shortener',
					'action' => 'delete',
					'link'   => $item->id,
				),
				admin_url( 'admin.php' )
			),
			'openstream_delete_link_' . $item->id
		);

		$actions = array(
			'delete' => sprintf(
				'<a href="%s" onclick="return confirm(\'%s\');">%s</a>',
				esc_url( $delete_url ),
				esc_js( __( 'Are you sure you want to delete this link?', 'openstream-link-shortener' ) ),
				esc_html__( 'Delete', 'openstream-link-shortener' )
			),
		);

		return sprintf(
			'<a href="%1$s" target="_blank">%2$s</a> <button type="button" class="button button-small openstream-copy-btn" data-url="%1$s">%3$s</button>%4$s',
			esc_url( $short_url ),
			esc_html( $short_url ),
			esc_html__( 'Copy', 'openstream-link-shortener' ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Render the destination URL column.
	 *
	 * @param object $item The link row.
	 * @return string Column HTML.
	 */
	public function column_destination_url( $item ) {
		$display_url = esc_html( $item->destination_url );
		if ( strlen( $item->destination_url ) > 80 ) {
			$display_url = esc_html( substr( $item->destination_url, 0, 80 ) ) . '&hellip;';
		}

		return sprintf(
			'<a href="%s" target="_blank" rel="noopener">%s</a>',
			esc_url( $item->destination_url ),
			$display_url
		);
	}

	/**
	 * Render the click count column.
	 *
	 * @param object $item The link row.
	 * @return string Column HTML.
	 */
	public function column_click_count( $item ) {
		return esc_html( number_format_i18n( $item->click_count ) );
	}

	/**
	 * Render the created at column.
	 *
	 * @param object $item The link row.
	 * @return string Column HTML.
	 */
	public function column_created_at( $item ) {
		return esc_html(
			date_i18n(
				get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				strtotime( $item->created_at )
			)
		);
	}

	/**
	 * Default column rendering.
	 *
	 * @param object $item        The link row.
	 * @param string $column_name The column slug.
	 * @return string Column HTML.
	 */
	public function column_default( $item, $column_name ) {
		return esc_html( $item->$column_name );
	}

	/**
	 * Message displayed when there are no links.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No links found.', 'openstream-link-shortener' );
	}
}
