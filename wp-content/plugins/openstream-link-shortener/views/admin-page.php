<?php
/**
 * Admin page template for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 *
 * @var Openstream_Link_Shortener_List_Table $list_table The list table instance.
 * @var string                               $new_link   Newly created short URL, if any.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Link Shortener', 'openstream-link-shortener' ); ?></h1>

	<?php settings_errors( 'openstream_link_shortener' ); ?>

	<?php if ( ! empty( $new_link ) ) : ?>
		<div class="notice notice-success openstream-new-link-notice">
			<p>
				<?php esc_html_e( 'Short link created:', 'openstream-link-shortener' ); ?>
				<a href="<?php echo esc_url( $new_link ); ?>" target="_blank"><strong><?php echo esc_html( $new_link ); ?></strong></a>
				<button type="button" class="button button-small openstream-copy-btn" data-url="<?php echo esc_attr( $new_link ); ?>">
					<?php esc_html_e( 'Copy', 'openstream-link-shortener' ); ?>
				</button>
			</p>
		</div>
	<?php endif; ?>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only, deletion was already verified with nonce. ?>
	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Link deleted.', 'openstream-link-shortener' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="openstream-shorten-form">
		<h2><?php esc_html_e( 'Shorten a URL', 'openstream-link-shortener' ); ?></h2>
		<form method="post" action="">
			<?php wp_nonce_field( 'openstream_shorten_link', 'openstream_shorten_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="destination_url"><?php esc_html_e( 'Destination URL', 'openstream-link-shortener' ); ?></label>
					</th>
					<td>
						<input type="url" id="destination_url" name="destination_url" class="large-text" placeholder="https://example.com/long-url" required />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="custom_slug"><?php esc_html_e( 'Custom Slug', 'openstream-link-shortener' ); ?></label>
					</th>
					<td>
						<input type="text" id="custom_slug" name="custom_slug" class="regular-text" placeholder="<?php esc_attr_e( 'Optional — leave blank for auto-generated', 'openstream-link-shortener' ); ?>" pattern="[a-zA-Z0-9]+" />
						<p class="description">
							<?php
							printf(
								/* translators: %s: example short URL */
								esc_html__( 'Letters and numbers only. Example: %s', 'openstream-link-shortener' ),
								'<code>' . esc_html( home_url( '/spotify' ) ) . '</code>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Shorten', 'openstream-link-shortener' ), 'primary', 'submit', true ); ?>
		</form>
	</div>

	<hr />

	<h2><?php esc_html_e( 'Your Links', 'openstream-link-shortener' ); ?></h2>

	<form method="get">
		<input type="hidden" name="page" value="openstream-link-shortener" />
		<?php
		$list_table->search_box( __( 'Search Links', 'openstream-link-shortener' ), 'openstream-search' );
		$list_table->display();
		?>
	</form>
</div>
