<?php
/**
 * Customer helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;

/**
 * Customer helper
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class CustomerHelper {
	/**
	 * Complement customer.
	 *
	 * @param Customer $customer Customer to complement.
	 */
	public static function complement_customer( Customer $customer ) {
		// Name.
		if ( null === $customer->get_name() && is_user_logged_in() ) {
			$user = wp_get_current_user();

			$data = array(
				'first_name' => $user->user_firstname,
				'last_name'  => $user->user_lastname,
			);

			$data = array_map( 'trim', $data );
			$data = array_filter( $data );

			if ( ! empty( $data ) ) {
				$name = new ContactName();

				$customer->set_name( $name );
			}
		}

		// Name.
		$name = $customer->get_name();

		if ( null !== $name ) {
			ContactNameHelper::complement_name( $name );
		}

		// Locale.
		if ( null === $customer->get_locale() ) {
			$locales = array();

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

				// Make sure locale includes a country (i.e. Firefox sets `nl` as accepted language).
				if ( false !== $http_locale ) {
					if ( false === strpos( $http_locale, '_' ) ) {
						$http_locale = sprintf(
							'%1$s_%2$s',
							$http_locale,
							strtoupper( $http_locale )
						);
					}

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
			$user_agent = filter_var( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

			$customer->set_user_agent( $user_agent );
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
}
