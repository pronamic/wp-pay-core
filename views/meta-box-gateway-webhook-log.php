<?php
/**
 * Meta Box Gateway Webhook Log
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var string                               $gateway_id Gateway ID.
 * @var int                                  $config_id  Configuration ID.
 * @var \Pronamic\WordPress\Pay\Core\Gateway $gateway    Gateway.
 */

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Webhooks\WebhookRequestInfo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	printf(
		/* translators: %s: Exception message. */
		esc_html__( 'The following error occurred when reading the webhook request information: "%s".', 'pronamic_ideal' ),
		esc_html( $e->getMessage() )
	);

	return;
}

$payment = $webhook_log_request_info->get_payment();

$payment_id = ( null === $payment ) ? null : $payment->get_id();

if ( null !== $payment_id ) {
	echo wp_kses(
		sprintf(
			/* translators: 1: formatted date, 2: payment edit url, 3: payment id */
			__(
				'Last webhook request processed on %1$s for <a href="%2$s" title="Payment %3$s">payment #%3$s</a>.',
				'pronamic_ideal'
			),
			$webhook_log_request_info->get_request_date()->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) ),
			esc_url( (string) get_edit_post_link( $payment_id ) ),
			(string) $payment_id
		),
		[
			'a' => [
				'href'  => true,
				'title' => true,
			],
		]
	);
} else {
	echo esc_html(
		sprintf(
			/* translators: %s: formatted date */
			__(
				'Last webhook request processed on %s.',
				'pronamic_ideal'
			),
			$webhook_log_request_info->get_request_date()->format_i18n( _x( 'l j F Y \a\t H:i', 'full datetime format', 'pronamic_ideal' ) )
		)
	);
}
