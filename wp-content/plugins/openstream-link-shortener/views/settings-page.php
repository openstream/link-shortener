<?php
/**
 * Settings page template for the Openstream Link Shortener.
 *
 * @package Openstream_Link_Shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Link Shortener Settings', 'openstream-link-shortener' ); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'openstream_link_shortener_settings' );
		do_settings_sections( 'openstream-link-shortener-settings' );
		submit_button();
		?>
	</form>
</div>
