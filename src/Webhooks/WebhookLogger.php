<?php
/**
 * Webhook logger
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Webhooks;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Webhook logger class
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class WebhookLogger {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'pronamic_pay_webhook_log_payment', array( $this, 'log_payment' ) );
	}

	/**
	 * Log payment.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return void
	 *
	 * @throws \Exception Throws an Exception on request date error.
	 */
	public function log_payment( Payment $payment ) {
		$post_data = file_get_contents( 'php://input' );

		if ( ! $post_data ) {
			$post_data = null;
		}

		$request_info = new WebhookRequestInfo(
			new DateTime(),
			( is_ssl() ? 'https://' : 'http://' ) . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ),
			$post_data
		);

		$request_info->set_payment( $payment );

		$this->log_request( $request_info );
	}

	/**
	 * Log request.
	 *
	 * @param WebhookRequestInfo $request_info Request info.
	 *
	 * @return void
	 */
	public function log_request( WebhookRequestInfo $request_info ) {
		// Payment.
		$payment = $request_info->get_payment();

		if ( null === $payment ) {
			return;
		}

		// Config ID.
		$config_id = $payment->get_config_id();

		if ( null === $config_id ) {
			return;
		}

		// Gateway.
		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return;
		}

		// Update webhook log.
		$json = wp_json_encode( $request_info );

		if ( $json ) {
			update_post_meta( $config_id, '_pronamic_gateway_webhook_log', wp_slash( $json ) );

			// Delete outdated webhook URLs transient.
			delete_transient( 'pronamic_outdated_webhook_urls' );
		}
	}
}
