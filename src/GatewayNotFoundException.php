<?php
/**
 * Gateway not found exception.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Gateway not found exception.
 *
 * @author  Reüel van der Steege
 * @version 2.2.4
 * @since   2.2.4
 */
class GatewayNotFoundException extends PayException {
	/**
	 * Constructor.
	 *
	 * @param null|string|int $config_id Config ID.
	 * @param mixed           $data      Additional data.
	 */
	public function __construct( $config_id, $data = null ) {
		$message = \sprintf(
			/* translators: %s: gateway config ID */
			__( 'Gateway not found (%s).', 'pronamic_ideal' ),
			$config_id
		);

		parent::__construct( 'gateway_not_found', $message, $data );
	}
}
