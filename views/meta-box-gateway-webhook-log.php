<?php
/**
 * Meta Box Gateway Webhook Log
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Webhooks\WebhookRequestInfo;

$integration = pronamic_pay_plugin()->gateway_integrations->get_integration( $gateway_id );

if ( ! $integration || ! $integration->supports( 'webhook_log' ) ) {
	esc_html_e( 'This gateway does not support webhook logging.', 'pronamic_ideal' );

	return;
}

$webhook_log_json_string = get_post_meta( $config_id, '_pronamic_gateway_webhook_log', true );

$config_gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

if ( empty( $webhook_log_json_string ) || $config_gateway_id !== $gateway_id ) {
	esc_html_e( 'No webhook request processed yet.', 'pronamic_ideal' );

	return;
}

$object = json_decode( $webhook_log_json_string );

try {
	$webhook_log_request_info = WebhookRequestInfo::from_json( $object );
} catch ( InvalidArgumentException $e ) {
	$webhook_error = new WP_Error( 'webhook_request_info_error', $e->getMessage() );

	Plugin::render_errors( $webhook_error );

	return;
}

$payment = $webhook_log_request_info->get_payment();

if ( $payment ) {
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
} else {
	printf(
		/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
		__( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'Last webhook request processed on %1$s.',
			'pronamic_ideal'
		),
		esc_html( $webhook_log_request_info->get_request_date()->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ) )
	);
}
