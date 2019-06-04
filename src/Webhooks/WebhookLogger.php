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
use Pronamic\WordPress\Pay\Plugin;

/**
 * Webhook logger class
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class WebhookLogger {
	public function setup() {
		add_action( 'pronamic_pay_webhook_log_payment', array( $this, 'log_payment' ) );
	}

	public function log_payment( $payment ) {
		$request_info = new WebhookRequestInfo(
			new DateTime(),
			( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			file_get_contents( 'php://input' )
		);

		$request_info->set_payment( $payment );

		$this->log_request( $request_info );
	}

	public function log_request( WebhookRequestInfo $request_info ) {
		$payment = $request_info->get_payment();

		if ( null === $payment ) {
			return;
		}

		$config_id = $payment->get_config_id();

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return;
		}

		// Update webhook log.
		update_post_meta( $config_id, '_pronamic_gateway_webhook_log', wp_slash( wp_json_encode( $request_info ) ) );
	}
}
