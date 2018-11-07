<?php
/**
 * Contact name helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use DateTime;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;

/**
 * Contact name helper
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   2.0.8
 */
class ContactNameHelper {
	/**
	 * Complement customer.
	 *
	 * @param Customer $customer Customer to complement.
	 */
	public static function complement_name( ContactName $name ) {
		// Name.
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			if ( null === $name->get_first_name() && ! empty( $user->user_firstname ) ) {
				$name->set_first_name( $user->user_firstname );
			}

			if ( null === $name->get_last_name() && ! empty( $user->user_lastname ) ) {
				$name->set_last_name( $user->user_lastname );
			}
		}

		// Initials.
		if ( null === $name->get_initials() ) {
			$names = explode( ' ', trim( $name->get_first_name() . ' ' . $name->get_middle_name() ) );

			$initials = array_map( function( $name ) {
				return strtoupper( substr( $name, 0, 1 ) ) . '.';
			}, $names );

			$name->set_initials( implode( '', $initials ) );
		}
	}

	/**
	 * Anonymize customer.
	 *
	 * @param Customer $customer Customer to anonymize.
	 */
	public static function anonymize_name( Customer $customer ) {
		$name->set_first_name( null );
		$name->set_middle_name( null );
		$name->set_last_name( null );
	}
}
