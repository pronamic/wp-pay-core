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

/**
 * Gateway exception.
 *
 * @author  Reüel van der Steege
 * @version 2.2.4
 * @since   2.2.4
 */
class GatewayException extends PayException {
	/**
	 * Constructor.
	 *
	 * @param string $gateway_id Gateway ID.
	 * @param string $message    Message.
	 * @param mixed  $data       Additional data.
	 */
	public function __construct( $gateway_id, $message, $data = null ) {
		// Error code.
		$error_code = sprintf( '%s_gateway_error', $gateway_id );

		parent::__construct( $error_code, $message, $data );
	}
}
