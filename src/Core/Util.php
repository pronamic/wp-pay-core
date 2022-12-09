<?php
/**
 * Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util as Pay_Util;

/**
 * Title: WordPress utility class
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 1.0.0
 */
class Util {
	/**
	 * SimpleXML load string.
	 *
	 * @link https://akrabat.com/throw-an-exception-when-simplexml_load_string-fails/
	 * @link https://www.php.net/manual/en/class.invalidargumentexception.php
	 * @link https://www.php.net/manual/en/class.libxmlerror.php
	 * @deprecated Deprecated since version 5.0, see https://github.com/pronamic/wp-pay-core/issues/85 for more information.
	 * @param string $string The XML string to convert to a SimpleXMLElement object.
	 * @return \SimpleXMLElement
	 * @throws \InvalidArgumentException If string could not be loaded in to a SimpleXMLElement object.
	 */
	public static function simplexml_load_string( $string ) {
		// Suppress all XML errors.
		$use_errors = libxml_use_internal_errors( true );

		// Load.
		$xml = simplexml_load_string( $string );

		// Check result.
		if ( false !== $xml ) {
			// Set back to previous value.
			libxml_use_internal_errors( $use_errors );

			return $xml;
		}

		// Error message.
		$messages = [
			__( 'Could not load the XML string.', 'pronamic_ideal' ),
		];

		foreach ( libxml_get_errors() as $error ) {
			$messages[] = sprintf(
				'%s on line: %s, column: %s',
				$error->message,
				$error->line,
				$error->column
			);
		}

		// Clear errors.
		libxml_clear_errors();

		// Set back to previous value.
		libxml_use_internal_errors( $use_errors );

		// Throw exception.
		$message = implode( PHP_EOL, $messages );

		throw new \InvalidArgumentException( $message );
	}

	/**
	 * No cache.
	 *
	 * @return void
	 */
	public static function no_cache() {
		// @link https://github.com/woothemes/woocommerce/blob/2.3.11/includes/class-wc-cache-helper.php
		// @link https://www.w3-edge.com/products/w3-total-cache/
		$do_not_constants = [
			'DONOTCACHEPAGE',
			'DONOTCACHEDB',
			'DONOTMINIFY',
			'DONOTCDN',
			'DONOTCACHEOBJECT',
		];

		foreach ( $do_not_constants as $do_not_constant ) {
			if ( ! defined( $do_not_constant ) ) {
				define( $do_not_constant, true );
			}
		}

		nocache_headers();
	}

	/**
	 * String to interval period (user input string).
	 *
	 * @since 2.0.3
	 * @param string $interval Interval user input string.
	 * @return string|null
	 */
	public static function string_to_interval_period( $interval ) {
		if ( ! is_string( $interval ) ) {
			return null;
		}

		$interval = trim( $interval );

		// Check last character for period.
		$interval_char = strtoupper( substr( $interval, - 1, 1 ) );

		if ( in_array( $interval_char, [ 'D', 'W', 'M', 'Y' ], true ) ) {
			return $interval_char;
		}

		// Find interval period by counting string replacements.
		$periods = [
			'D' => [ 'D', 'day' ],
			'W' => [ 'W', 'week' ],
			'M' => [ 'M', 'month' ],
			'Y' => [ 'Y', 'year' ],
		];

		foreach ( $periods as $interval_period => $search ) {
			$count = 0;

			str_ireplace( $search, '', $interval, $count );

			if ( $count > 0 ) {
				return $interval_period;
			}
		}

		return null;
	}

	/**
	 * Convert the specified period to a single char notation.
	 *
	 * @since 1.3.9
	 *
	 * @param string $period The period value to convert to a single character/string value.
	 *
	 * @return string
	 */
	public static function to_period( $period ) {
		if ( false !== strpos( $period, 'day' ) || false !== strpos( $period, 'daily' ) ) {
			return 'D';
		}

		if ( false !== strpos( $period, 'week' ) ) {
			return 'W';
		}

		if ( false !== strpos( $period, 'month' ) ) {
			return 'M';
		}

		if ( false !== strpos( $period, 'year' ) ) {
			return 'Y';
		}

		return $period;
	}

	/**
	 * Get remote address.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.8/wp-admin/includes/class-wp-community-events.php#L210-L274
	 * @since 2.1.0
	 * @return mixed|null
	 */
	public static function get_remote_address() {
		// In order of preference, with the best ones for this purpose first.
		$headers = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $headers as $header ) {
			if ( isset( $_SERVER[ $header ] ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
				$addresses = explode( ',', \sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );

				$addresses = array_slice( $addresses, 0, 1 );

				foreach ( $addresses as $address ) {
					$address = trim( $address );

					$address = filter_var( $address, FILTER_VALIDATE_IP );

					if ( false === $address ) {
						continue;
					}

					return $address;
				}
			}
		}

		return null;
	}

	/**
	 * Method exists
	 *
	 * This helper function was created to fix an issue with `method_exists` calls
	 * and non existing classes.
	 *
	 * @param string $class  Class name to check for the specified method.
	 * @param string $method Method name to check for existence.
	 *
	 * @return boolean
	 */
	public static function class_method_exists( $class, $method ) {
		return class_exists( $class ) && method_exists( $class, $method );
	}

	/**
	 * Check if input type has vars.
	 *
	 * @param int   $type           One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
	 * @param array $variable_names Array of variable names to check in input type.
	 *
	 * @return bool
	 */
	public static function input_has_vars( $type, $variable_names ) {
		foreach ( $variable_names as $variable_name ) {
			if ( ! filter_has_var( $type, $variable_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Switch to user locale.
	 *
	 * @return void
	 */
	public static function switch_to_user_locale() {
		\switch_to_locale( \get_user_locale() );

		\add_filter( 'determine_locale', 'get_user_locale' );

		Plugin::load_plugin_textdomain();

		\remove_filter( 'determine_locale', 'get_user_locale' );
	}
}
