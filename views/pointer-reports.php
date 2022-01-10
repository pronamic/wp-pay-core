<?php
/**
 * Pointer Reports
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

if ( ! isset( $admin_tour ) ) {
	return;
}

?>
<h3><?php esc_html_e( 'Reports', 'pronamic_ideal' ); ?></h3>

<p>
	<?php esc_html_e( 'The Pronamic Pay reports page shows you an graph of all the payments of this year.', 'pronamic_ideal' ); ?>
	<?php esc_html_e( 'You can see the number of successful payments and the total amount of pending, successful, cancelled and failed payments.', 'pronamic_ideal' ); ?>
</p>

<div class="wp-pointer-buttons pp-pointer-buttons">
	<a href="<?php echo \esc_url( add_query_arg( 'page', 'pronamic_pay_tools', admin_url( 'admin.php' ) ) ); ?>" class="button-secondary pp-pointer-button-prev"><?php esc_html_e( 'Previous', 'pronamic_ideal' ); ?></a>

	<span class="pp-pointer-buttons-right">
		<a href="<?php echo \esc_url( $admin_tour->get_close_url() ); ?>" class="button-secondary pp-pointer-button-close"><?php esc_html_e( 'Close', 'pronamic_ideal' ); ?></a>
	</span>
</div>
