<?php
/**
 * Gateway exception.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

class GatewayException extends PayException {
	/**
	 * Constructor.
	 *
	 * @param string $gateway_id Gateway ID.
	 * @param string $message    Message.
	 */
	public function __construct( $gateway_id, $message, $data = null ) {
		// Error code.
		$error_code = sprintf( '%s_gateway_error', $gateway_id );

		return parent::__construct( $error_code, $message, $data );
	}
}
