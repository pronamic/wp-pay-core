<?php
/**
 * Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 1.0.0
 */
class Util {
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
	 * @param string $class_name  Class name to check for the specified method.
	 * @param string $method_name Method name to check for existence.
	 *
	 * @return boolean
	 */
	public static function class_method_exists( $class_name, $method_name ) {
		return class_exists( $class_name ) && method_exists( $class_name, $method_name );
	}
}
