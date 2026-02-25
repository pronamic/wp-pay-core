<?php
/**
 * Pointer Reports
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h3><?php esc_html_e( 'Reports', 'pronamic_ideal' ); ?></h3>

<p>
	<?php esc_html_e( 'The Pronamic Pay reports page shows you an graph of all the payments of this year.', 'pronamic_ideal' ); ?>
	<?php esc_html_e( 'You can see the number of successful payments and the total amount of pending, successful, cancelled and failed payments.', 'pronamic_ideal' ); ?>
</p>
