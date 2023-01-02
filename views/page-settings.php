<?php
/**
 * Page Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$message_id = array_key_exists( 'message', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['message'] ) ) : '';

if ( ! empty( $message_id ) ) {
	switch ( $message_id ) {
		case 'pages-generated':
			printf(
				'<div id="message" class="updated"><p>%s</p></div>',
				esc_html__( 'The default payment status pages are created.', 'pronamic_ideal' )
			);

			break;
		case 'pages-not-generated':
			printf(
				'<div id="message" class="error"><p>%s</p></div>',
				esc_html__( 'The default payment status pages could not be created.', 'pronamic_ideal' )
			);

			break;
	}
}

?>

<div class="wrap pronamic-pay-settings">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<form action="options.php" method="post">
		<?php wp_nonce_field( 'pronamic_pay_settings', 'pronamic_pay_nonce' ); ?>

		<?php settings_fields( 'pronamic_pay' ); ?>

		<?php do_settings_sections( 'pronamic_pay' ); ?>

		<?php submit_button(); ?>
	</form>

	<?php require __DIR__ . '/pronamic.php'; ?>
</div>
