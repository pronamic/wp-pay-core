<?php
/**
 * Address helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
 * @version 2.2.6
 * @since   2.1.0
 */
class AddressHelper {
	/**
	 * Complement address.
	 *
	 * @param Address $address Address to complement.
	 * @return void
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
			// On exceptions the address will not be complemented, no problem.
			return;
		}
	}

	/**
	 * Anonymize address.
	 *
	 * @param Address $address Address to complement.
	 * @return void
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

	/**
	 * Create an address from an array.
	 *
	 * @param array $data Data.
	 * @return Address|null
	 */
	public static function from_array( $data ) {
		$data = \array_filter(
			$data,
			function ( $value ) {
				return ( null !== $value ) && ( '' !== $value );
			}
		);

		if ( empty( $data ) ) {
			return null;
		}

		$address = new Address();

		if ( \array_key_exists( 'name', $data ) ) {
			$name = $data['name'];

			if ( $name instanceof ContactName ) {
				$address->set_name( $name );
			}
		}

		if ( \array_key_exists( 'line_1', $data ) ) {
			$address->set_line_1( $data['line_1'] );
		}

		if ( \array_key_exists( 'line_2', $data ) ) {
			$address->set_line_2( $data['line_2'] );
		}

		if ( \array_key_exists( 'postal_code', $data ) ) {
			$address->set_postal_code( $data['postal_code'] );
		}

		if ( \array_key_exists( 'city', $data ) ) {
			$address->set_city( $data['city'] );
		}

		if ( \array_key_exists( 'region', $data ) ) {
			$address->set_region( $data['region'] );
		}

		if ( \array_key_exists( 'country_code', $data ) ) {
			$address->set_country_code( $data['country_code'] );
		}

		if ( \array_key_exists( 'email', $data ) ) {
			$address->set_email( $data['email'] );
		}

		if ( \array_key_exists( 'phone', $data ) ) {
			$address->set_phone( $data['phone'] );
		}

		return $address;
	}
}
