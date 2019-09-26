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

/**
 * Extension exception.
 *
 * @author  Reüel van der Steege
 * @version 2.2.4
 * @since   2.2.4
 */
class ExtensionException extends PayException {
	/**
	 * Constructor.
	 *
	 * @param string $extension_id Extension ID.
	 * @param string $message      Message.
	 * @param mixed  $data         Additional data.
	 */
	public function __construct( $extension_id, $message, $data = null ) {
		// Error code.
		$error_code = sprintf( 'extension_%s_error', $extension_id );

		parent::__construct( $error_code, $message, $data );
	}
}
