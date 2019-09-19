<?php
/**
 * Extension exception.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

class ExtensionException extends PayException {
	/**
	 * Constructor.
	 *
	 * @param string $extension_id Extension ID.
	 * @param string $message      Message.
	 */
	public function __construct( $extension_id, $message, $data = null ) {
		// Error code.
		$error_code = sprintf( 'extension_%s_error', $extension_id );

		return parent::__construct( $error_code, $message, $data );
	}
}
