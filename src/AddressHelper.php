<?php
/**
 * Address helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Exception;
use VIISON\AddressSplitter\AddressSplitter;

/**
 * Address helper
 *
 * @author Remco Tolsma
 * @since  1.4.0
 */
class AddressHelper {
	/**
	 * Complement address.
	 *
	 * @param Address $address Address to complement.
	 */
	public static function complement_address( Address $address ) {
		try {
			$parts = AddressSplitter::splitAddress( $address->get_line_1() );

			if ( null === $address->get_street_name() && array_key_exists( 'streetName', $parts ) ) {
				$address->set_street_name( $parts['streetName'] );
			}

			if ( null === $address->get_house_number() && array_key_exists( 'houseNumber', $parts ) ) {
				$address->set_house_number( $parts['houseNumber'] );
			}

			if ( array_key_exists( 'houseNumberParts', $parts ) ) {
				$house_number_parts = $parts['houseNumberParts'];

				if ( is_array( $house_number_parts ) ) {
					if ( null === $address->get_house_number_base() && array_key_exists( 'base', $house_number_parts ) ) {
						$address->set_house_number_base( $house_number_parts['base'] );
					}

					if ( null === $address->get_house_number_addition() && array_key_exists( 'extension', $house_number_parts ) ) {
						$address->set_house_number_addition( $house_number_parts['extension'] );
					}
				}
			}
		} catch ( Exception $e ) {
			// On exceptions the address wil not be complemented, no problem.
			return;
		}
	}
}
