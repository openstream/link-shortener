<?php
/**
 * Database operations for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all database CRUD operations.
 */
class Openstream_Link_Shortener_DB {

	/**
	 * Database version for schema migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0';

	/**
	 * Get the full table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'openstream_links';
	}

	/**
	 * Create the links table.
	 *
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(20) NOT NULL,
			destination_url text NOT NULL,
			click_count bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'openstream_link_shortener_db_version', self::DB_VERSION );
	}

	/**
	 * Insert a new link.
	 *
	 * @param string $slug            The short URL slug.
	 * @param string $destination_url The destination URL.
	 * @return int|false The inserted row ID, or false on failure.
	 */
	public static function insert_link( $slug, $destination_url ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
		$result = $wpdb->insert(
			self::table_name(),
			array(
				'slug'            => $slug,
				'destination_url' => $destination_url,
			),
			array( '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get a link by its slug.
	 *
	 * @param string $slug The slug to look up.
	 * @return object|null The link row, or null if not found.
	 */
	public static function get_link_by_slug( $slug ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for slug lookups.
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE slug = %s',
				self::table_name(),
				$slug
			)
		);
	}

	/**
	 * Get a link by its ID.
	 *
	 * @param int $id The link ID.
	 * @return object|null The link row, or null if not found.
	 */
	public static function get_link_by_id( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE id = %d',
				self::table_name(),
				$id
			)
		);
	}

	/**
	 * Delete a link by its ID.
	 *
	 * @param int $id The link ID.
	 * @return int|false Number of rows deleted, or false on error.
	 */
	public static function delete_link( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return $wpdb->delete(
			self::table_name(),
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Increment the click count for a link.
	 *
	 * @param int $id The link ID.
	 * @return int|false Number of rows updated, or false on error.
	 */
	public static function increment_click_count( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, click counter.
		return $wpdb->query(
			$wpdb->prepare(
				'UPDATE %i SET click_count = click_count + 1 WHERE id = %d',
				self::table_name(),
				$id
			)
		);
	}

	/**
	 * Get a paginated list of links.
	 *
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 *     @type int    $per_page Number of links per page. Default 20.
	 *     @type int    $page     Current page number. Default 1.
	 *     @type string $orderby  Column to sort by. Default 'created_at'.
	 *     @type string $order    Sort direction (ASC or DESC). Default 'DESC'.
	 *     @type string $search   Search term for slug or destination URL.
	 * }
	 * @return array List of link objects.
	 */
	public static function get_links( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'orderby'  => 'created_at',
			'order'    => 'DESC',
			'search'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$allowed_orderby = array( 'slug', 'click_count', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$offset = absint( ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] ) );

		if ( ! empty( $args['search'] ) ) {
			$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			if ( 'ASC' === $order ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
				return $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM %i WHERE slug LIKE %s OR destination_url LIKE %s ORDER BY %i ASC LIMIT %d OFFSET %d',
						self::table_name(),
						$like,
						$like,
						$orderby,
						absint( $args['per_page'] ),
						$offset
					)
				);
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
			return $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %i WHERE slug LIKE %s OR destination_url LIKE %s ORDER BY %i DESC LIMIT %d OFFSET %d',
					self::table_name(),
					$like,
					$like,
					$orderby,
					absint( $args['per_page'] ),
					$offset
				)
			);
		}

		if ( 'ASC' === $order ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
			return $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %i ORDER BY %i ASC LIMIT %d OFFSET %d',
					self::table_name(),
					$orderby,
					absint( $args['per_page'] ),
					$offset
				)
			);
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY %i DESC LIMIT %d OFFSET %d',
				self::table_name(),
				$orderby,
				absint( $args['per_page'] ),
				$offset
			)
		);
	}

	/**
	 * Count total links, optionally filtered by search.
	 *
	 * @param string $search Optional search term.
	 * @return int Total number of links.
	 */
	public static function count_links( $search = '' ) {
		global $wpdb;

		if ( ! empty( $search ) ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %i WHERE slug LIKE %s OR destination_url LIKE %s',
					self::table_name(),
					$like,
					$like
				)
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i',
				self::table_name()
			)
		);
	}

	/**
	 * Check if a slug already exists.
	 *
	 * @param string $slug The slug to check.
	 * @return bool True if the slug exists.
	 */
	public static function slug_exists( $slug ) {
		return null !== self::get_link_by_slug( $slug );
	}
}
