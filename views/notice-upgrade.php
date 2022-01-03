<?php
/**
 * Admin View: Notice - Upgrade
 *
 * @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/admin/views/html-notice-update.php
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

$upgrade_link = wp_nonce_url(
	add_query_arg(
		array(
			'page'                 => 'pronamic_ideal',
			'pronamic_pay_upgrade' => true,
		),
		admin_url( 'admin.php' )
	),
	'pronamic_pay_upgrade',
	'pronamic_pay_nonce'
);

?>
<div class="updated">
	<p>
		<strong><?php esc_html_e( 'Pronamic Pay Upgrade Required', 'pronamic_ideal' ); ?></strong> –
		<?php esc_html_e( 'We just need to update your install to the latest version.', 'pronamic_ideal' ); ?>
	</p>

	<p class="submit">
		<a href="<?php echo \esc_url( $upgrade_link ); ?>" class="pp-upgrade-now button-primary"><?php esc_html_e( 'Run the upgrader', 'pronamic_ideal' ); ?></a>
	</p>
</div>

<script type="text/javascript">
	jQuery( '.pp-upgrade-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the upgrader now?', 'pronamic_ideal' ) ); ?>' );
	});
</script>
