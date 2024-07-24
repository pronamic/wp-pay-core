<?php
/**
 * Contact name helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use DateTime;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;

/**
 * Contact name helper
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.0.8
 */
class ContactNameHelper {
	/**
	 * Complement name.
	 *
	 * @param ContactName $name Contact name to complement.
	 * @return void
	 */
	public static function complement_name( ContactName $name ) {
		// Name.
		if ( \is_user_logged_in() ) {
			$user = \wp_get_current_user();

			if ( null === $name->get_first_name() && ! empty( $user->user_firstname ) ) {
				$name->set_first_name( $user->user_firstname );
			}

			if ( null === $name->get_last_name() && ! empty( $user->user_lastname ) ) {
				$name->set_last_name( $user->user_lastname );
			}
		}

		// Initials.
		if ( null === $name->get_initials() ) {
			// First and middle name could contain multiple names.
			$names = [];

			$first_name = $name->get_first_name();

			if ( null !== $first_name ) {
				$names = \array_merge( $names, \explode( ' ', $first_name ) );
			}

			$middle_name = $name->get_middle_name();

			if ( null !== $middle_name ) {
				$names = \array_merge( $names, \explode( ' ', $middle_name ) );
			}

			$names = \array_map( 'trim', $names );

			$names = \array_filter( $names );

			if ( \count( $names ) > 0 ) {
				$initials = array_map(
					function ( $name ) {
						return self::string_to_uppercase( \mb_substr( $name, 0, 1 ) ) . '.';
					},
					$names
				);

				$name->set_initials( implode( '', $initials ) );
			}
		}

		/*
		 * Parse full name.
		 *
		 * @link https://github.com/dschnelldavis/parse-full-name
		 * @link https://github.com/joshfraser/PHP-Name-Parser
		 * @link https://github.com/jasonpriem/HumanNameParser.php
		 * @todo
		 */
	}

	/**
	 * Convert string to uppercase.
	 *
	 * @param string $value String.
	 * @return string
	 */
	private static function string_to_uppercase( $value ) {
		if ( \function_exists( 'mb_strtoupper' ) ) {
			return \mb_strtoupper( $value );
		}

		return \strtoupper( $value );
	}

	/**
	 * Anonymize customer.
	 *
	 * @param ContactName $name Contact name to anonymize.
	 * @return void
	 */
	public static function anonymize_name( ContactName $name ) {
		$name->set_full_name( PrivacyManager::anonymize_data( 'text', $name->get_full_name() ) );
		$name->set_first_name( PrivacyManager::anonymize_data( 'text', $name->get_first_name() ) );
		$name->set_middle_name( PrivacyManager::anonymize_data( 'text', $name->get_middle_name() ) );
		$name->set_last_name( PrivacyManager::anonymize_data( 'text', $name->get_last_name() ) );
	}

	/**
	 * Create a contact name from an array.
	 *
	 * @param array $data Data.
	 * @return ContactName|null
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

		$name = new ContactName();

		if ( \array_key_exists( 'first_name', $data ) ) {
			$name->set_first_name( $data['first_name'] );
		}

		if ( \array_key_exists( 'last_name', $data ) ) {
			$name->set_last_name( $data['last_name'] );
		}

		return $name;
	}
}
