<?php
/**
 * Customer helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\VatNumbers\VatNumberViesValidator;

/**
 * Customer helper
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.1.0
 */
class CustomerHelper {
	/**
	 * Complement customer.
	 *
	 * @param Customer $customer Customer to complement.
	 * @return void
	 */
	public static function complement_customer( Customer $customer ) {
		// Name.
		if ( null === $customer->get_name() && is_user_logged_in() ) {
			$user = wp_get_current_user();

			$data = [
				'first_name' => $user->user_firstname,
				'last_name'  => $user->user_lastname,
			];

			$data = array_map( 'trim', $data );
			$data = array_filter( $data );

			if ( ! empty( $data ) ) {
				$name = new ContactName();

				$customer->set_name( $name );
			}
		}

		// User ID.
		if ( null === $customer->get_user_id() && is_user_logged_in() ) {
			$customer->set_user_id( \get_current_user_id() );
		}

		// Name.
		$name = $customer->get_name();

		if ( null !== $name ) {
			ContactNameHelper::complement_name( $name );
		}

		// VAT Number validity.
		$vat_number = $customer->get_vat_number();

		if ( null !== $vat_number ) {
			$vat_number_validity = $vat_number->get_validity();

			if ( null === $vat_number_validity ) {
				try {
					$vat_number_validity = VatNumberViesValidator::validate( $vat_number );
				} catch ( \Exception $e ) {
					// On exceptions we have no VAT number validity info, no problem.
					$vat_number_validity = null;
				}

				$vat_number->set_validity( $vat_number_validity );
			}
		}

		// Locale.
		if ( null === $customer->get_locale() ) {
			$locales = [];

			// User locale.
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();

				$locales[] = $user->locale;
			}

			// Locale based on ACCEPT_LANGUAGE header.
			if ( function_exists( 'locale_accept_from_http' ) ) {
				/**
				 * Please note that `locale_accept_from_http` can also return `false`,
				 * this is not documented on PHP.net.
				 *
				 * @link https://www.php.net/manual/en/locale.acceptfromhttp.php
				 * @link https://github.com/php/php-src/blob/php-7.3.5/ext/intl/locale/locale_methods.c#L1578-L1631
				 */
				$http_locale = locale_accept_from_http( Server::get( 'HTTP_ACCEPT_LANGUAGE' ) );

				if ( false !== $http_locale ) {
					$locales[] = $http_locale;
				}
			}

			// Site locale.
			$locales[] = get_locale();

			// Find first valid locale.
			$locales = array_filter( $locales );

			$locale = reset( $locales );

			if ( ! empty( $locale ) ) {
				$customer->set_locale( $locale );
			}
		}

		// Language.
		$locale = $customer->get_locale();

		if ( null === $customer->get_language() && null !== $locale ) {
			$language = substr( $locale, 0, 2 );

			$customer->set_language( $language );
		}

		/**
		 * User Agent.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/4.9.4/wp-includes/comment.php#L1962-L1965
		 */
		if ( null === $customer->get_user_agent() && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

			if ( false !== $user_agent ) {
				$customer->set_user_agent( $user_agent );
			}
		}

		// User IP.
		if ( null === $customer->get_ip_address() ) {
			// IP (@link https://github.com/WordPress/WordPress/blob/4.9.4/wp-includes/comment.php#L1957-L1960).
			$ip_address = Core_Util::get_remote_address();

			if ( ! empty( $ip_address ) ) {
				$customer->set_ip_address( $ip_address );
			}
		}

		// Gender.
		if ( null === $customer->get_gender() && filter_has_var( INPUT_POST, 'pronamic_pay_gender' ) ) {
			$gender = filter_input( INPUT_POST, 'pronamic_pay_gender', FILTER_SANITIZE_STRING );

			if ( Gender::is_valid( $gender ) ) {
				$customer->set_gender( $gender );
			}
		}

		// Birth date.
		if ( null === $customer->get_birth_date() && filter_has_var( INPUT_POST, 'pronamic_pay_birth_date' ) ) {
			$birth_date_string = filter_input( INPUT_POST, 'pronamic_pay_birth_date', FILTER_SANITIZE_STRING );

			$birth_date = DateTime::create_from_format( 'Y-m-d', $birth_date_string );

			if ( false !== $birth_date ) {
				$customer->set_birth_date( $birth_date );
			}
		}
	}

	/**
	 * Anonymize customer.
	 *
	 * @param Customer $customer Customer to anonymize.
	 * @return void
	 */
	public static function anonymize_customer( Customer $customer ) {
		$customer->set_gender( PrivacyManager::anonymize_data( 'text', $customer->get_gender() ) );
		$customer->set_birth_date( null );
		$customer->set_email( PrivacyManager::anonymize_data( 'email_mask', $customer->get_email() ) );
		$customer->set_phone( PrivacyManager::anonymize_data( 'phone', $customer->get_phone() ) );
		$customer->set_ip_address( PrivacyManager::anonymize_ip( $customer->get_ip_address() ) );
		$customer->set_user_agent( PrivacyManager::anonymize_data( 'text', $customer->get_user_agent() ) );

		$name = $customer->get_name();

		if ( null !== $name ) {
			ContactNameHelper::anonymize_name( $name );
		}
	}

	/**
	 * Create a customer from an array.
	 *
	 * @param array $data Data.
	 * @return Customer|null
	 */
	public static function from_array( $data ) {
		$data = \array_filter(
			$data,
			function( $value ) {
				return ( null !== $value ) && ( '' !== $value );
			}
		);

		if ( empty( $data ) ) {
			return null;
		}

		$customer = new Customer();

		if ( \array_key_exists( 'name', $data ) ) {
			$name = $data['name'];

			if ( $name instanceof ContactName ) {
				$customer->set_name( $name );
			}
		}

		if ( \array_key_exists( 'email', $data ) ) {
			$customer->set_email( $data['email'] );
		}

		if ( \array_key_exists( 'phone', $data ) ) {
			$customer->set_phone( $data['phone'] );
		}

		if ( \array_key_exists( 'user_id', $data ) ) {
			$customer->set_user_id( \intval( $data['user_id'] ) );
		}

		return $customer;
	}
}
