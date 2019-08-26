<?php
/**
 * Address helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Exception;
use VIISON\AddressSplitter\AddressSplitter;

/**
 * Address helper
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class AddressHelper {
	/**
	 * Complement address.
	 *
	 * @param Address $address Address to complement.
	 */
	public static function complement_address( Address $address ) {
		// Name.
		$name = $address->get_name();

		if ( null !== $name ) {
			ContactNameHelper::complement_name( $name );
		}

		// Address lines.
		$line_1 = $address->get_line_1();

		if ( empty( $line_1 ) ) {
			// If address line 1 is empty we can't use it to complement the address.
			return;
		}

		try {
			$parts = AddressSplitter::splitAddress( $line_1 );

			if ( null === $address->get_street_name() && array_key_exists( 'streetName', $parts ) ) {
				$address->set_street_name( $parts['streetName'] );
			}

			if ( null === $address->get_house_number() && array_key_exists( 'houseNumber', $parts ) ) {
				$address->set_house_number( $parts['houseNumber'] );
			}

			if ( array_key_exists( 'houseNumberParts', $parts ) ) {
				$house_number_parts = $parts['houseNumberParts'];

				if ( null === $address->get_house_number_base() && array_key_exists( 'base', $house_number_parts ) && ! empty( $house_number_parts['base'] ) ) {
					$address->set_house_number_base( $house_number_parts['base'] );
				}

				if ( null === $address->get_house_number_addition() && array_key_exists( 'extension', $house_number_parts ) && ! empty( $house_number_parts['extension'] ) ) {
					$address->set_house_number_addition( $house_number_parts['extension'] );
				}
			}
		} catch ( Exception $e ) {
			// On exceptions the address wil not be complemented, no problem.
			return;
		}
	}

	/**
	 * Anonymize address.
	 *
	 * @param Address $address Address to complement.
	 */
	public static function anonymize_address( Address $address ) {
		$address->set_company_name( PrivacyManager::anonymize_data( 'text', $address->get_company_name() ) );
		$address->set_coc_number( PrivacyManager::anonymize_data( 'text', $address->get_coc_number() ) );
		$address->set_email( PrivacyManager::anonymize_data( 'email_mask', $address->get_email() ) );
		$address->set_line_1( PrivacyManager::anonymize_data( 'text', $address->get_line_1() ) );
		$address->set_line_2( PrivacyManager::anonymize_data( 'text', $address->get_line_2() ) );
		$address->set_street_name( PrivacyManager::anonymize_data( 'text', $address->get_street_name() ) );
		$address->set_postal_code( PrivacyManager::anonymize_data( 'text', $address->get_postal_code() ) );
		$address->set_city( PrivacyManager::anonymize_data( 'text', $address->get_city() ) );
		$address->set_phone( PrivacyManager::anonymize_data( 'phone', $address->get_phone() ) );

		// Country code only accepts ISO 3166-1 alpha-2 strings and null.
		$address->set_country_code( null );

		// Anonymize house number.
		$house_number = $address->get_house_number();

		if ( null !== $house_number ) {
			$house_number->anonymize();
		}

		// Anonymize region.
		$region = $address->get_region();

		if ( null !== $region ) {
			$region->anonymize();
		}

		// Anonymize country.
		$country = $address->get_country();

		if ( null !== $country ) {
			$country->anonymize();
		}

		// Anonymize name.
		$name = $address->get_name();

		if ( null !== $name ) {
			ContactNameHelper::anonymize_name( $name );
		}
	}
}
