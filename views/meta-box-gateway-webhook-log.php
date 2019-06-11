<?php
/**
 * Meta Box Gateway Webhook Log
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Webhooks\WebhookRequestInfo;

if ( ! $gateway->supports( 'webhook_log' ) ) {
	esc_html_e( 'This gateway does not support webhook logging.', 'pronamic_ideal' );

	return;
}

$webhook_log_json_string = get_post_meta( $config_id, '_pronamic_gateway_webhook_log', true );

if ( empty( $webhook_log_json_string ) ) {
	esc_html_e( 'No webhook request processed yet.', 'pronamic_ideal' );

	return;
}

$object = json_decode( $webhook_log_json_string );

$webhook_log_request_info = WebhookRequestInfo::from_json( $object );

$payment = $webhook_log_request_info->get_payment();

printf(
	/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
	__( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'Last webhook request processed on %1$s for <a href="%2$s" title="Payment %3$s">payment #%3$s</a>.',
		'pronamic_ideal'
	),
	esc_html( $webhook_log_request_info->get_request_date()->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ) ),
	esc_url( get_edit_post_link( $payment->get_id() ) ),
	esc_html( $payment->get_id() )
);
