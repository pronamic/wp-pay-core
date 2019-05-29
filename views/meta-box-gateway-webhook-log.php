<?php
/**
 * Meta Box Gateway Webhook Log
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

if ( ! $gateway->supports( 'webhook-log' ) ) {
	esc_html_e( 'This gateway does not support webhook logging.', 'pronamic_ideal' );
}

if ( $gateway->supports( 'webhook-log' ) ) {
	esc_html_e( 'This gateway supports webhook logging.', 'pronamic_ideal' );
}
