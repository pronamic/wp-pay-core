<?php
/**
 * Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\Parser as MoneyParser;
use Pronamic\WordPress\Pay\Util as Pay_Util;
use SimpleXMLElement;
use WP_Error;

/**
 * Title: WordPress utility class
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class Util {
	/**
	 * Remote get body.
	 *
	 * @param string $url                    URL to request.
	 * @param int    $required_response_code Required response code.
	 * @param array  $args                   Remote request arguments.
	 *
	 * @return array|bool|string|WP_Error
	 */
	public static function remote_get_body( $url, $required_response_code = 200, array $args = array() ) {
		$result = wp_remote_request( $url, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/*
		 * The response code is cast to a integer since WordPress 4.1, therefor we can't use
		 * strict comparison on the required response code.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/4.1/wp-includes/class-http.php#L528-L529
		 * @link https://github.com/WordPress/WordPress/blob/4.0/wp-includes/class-http.php#L527
		 */
		if ( wp_remote_retrieve_response_code( $result ) == $required_response_code ) { // WPCS: loose comparison ok.
			return wp_remote_retrieve_body( $result );
		}

		// Wrong response code.
		return new WP_Error(
			'wrong_response_code',
			sprintf(
				/* translators: 1: received responce code, 2: required response code */
				__( 'The response code (<code>%1$s<code>) was incorrect, required response code <code>%2$s</code>.', 'pronamic_ideal' ),
				wp_remote_retrieve_response_code( $result ),
				$required_response_code
			)
		);
	}

	/**
	 * SimpleXML load string.
	 *
	 * @param string $string The XML string to convert to a SimpleXMLElement object.
	 *
	 * @return SimpleXMLElement|WP_Error
	 */
	public static function simplexml_load_string( $string ) {
		$result = false;

		// Suppress all XML errors.
		$use_errors = libxml_use_internal_errors( true );

		// Load.
		$xml = simplexml_load_string( $string );

		if ( false !== $xml ) {
			$result = $xml;
		} else {
			$error = new WP_Error( 'simplexml_load_error', __( 'Could not load the XML string.', 'pronamic_ideal' ) );

			foreach ( libxml_get_errors() as $e ) {
				$error->add( 'libxml_error', $e->message, $e );
			}

			libxml_clear_errors();

			$result = $error;
		}

		// Set back to previous value.
		libxml_use_internal_errors( $use_errors );

		return $result;
	}

	/**
	 * Amount to cents.
	 *
	 * @param float $amount The amount to conver to cents.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Money\Money::get_cents() instead.
	 *
	 * @return int
	 */
	public static function amount_to_cents( $amount ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Money\Money::get_cents()' );

		$money = new Money( $amount );

		return $money->get_cents();
	}

	/**
	 * Cents to amount.
	 *
	 * @param int $cents The cents to convert to float value.
	 *
	 * @return float
	 */
	public static function cents_to_amount( $cents ) {
		return $cents / 100;
	}

	/**
	 * String to amount (user input string).
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.6/wp-includes/functions.php#L206-L237
	 *
	 * @version 1.3.1
	 * @since 1.3.0
	 * @deprecated 2.0.3 Use Pronamic\WordPress\Money\Parser::parse( $amount )->get_amount() instead.
	 *
	 * @param string $value The string value to convert to a float value.
	 *
	 * @return float
	 */
	public static function string_to_amount( $value ) {
		_deprecated_function( __FUNCTION__, '2.0.3', 'Pronamic\WordPress\Money\Parser::parse()->get_amount()' );

		$money_parser = new MoneyParser();

		$amount = $money_parser->parse( $value )->get_amount();

		return $amount;
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

		if ( in_array( $interval_char, array( 'D', 'W', 'M', 'Y' ), true ) ) {
			return $interval_char;
		}

		// Find interval period by counting string replacements.
		$periods = array(
			'D' => array( 'D', 'day' ),
			'W' => array( 'W', 'week' ),
			'M' => array( 'M', 'month' ),
			'Y' => array( 'Y', 'year' ),
		);

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
	 * Convert boolean to an numceric boolean.
	 *
	 * @link https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 *
	 * @param boolean $boolean The boolean value to convert to an integer value.
	 *
	 * @return int
	 */
	public static function boolean_to_numeric( $boolean ) {
		return $boolean ? 1 : 0;
	}

	/**
	 * Convert boolean to an string boolean
	 *
	 * @link https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 *
	 * @param bool $boolean The boolean value to convert to a string value.
	 * @return string
	 */
	public static function boolean_to_string( $boolean ) {
		return $boolean ? 'true' : 'false';
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
	 * Build URL with the specified parameters
	 *
	 * @param string $url        URL to extend with the specified parameters.
	 * @param array  $parameters URL parameters.
	 *
	 * @return string
	 */
	public static function build_url( $url, array $parameters ) {
		return $url . '?' . _http_build_query( $parameters, null, '&' );
	}

	/**
	 * Get remote address.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.8/wp-admin/includes/class-wp-community-events.php#L210-L274
	 *
	 * @return mixed|null
	 */
	public static function get_remote_address() {
		// In order of preference, with the best ones for this purpose first.
		$headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( isset( $_SERVER[ $header ] ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
				$addresses = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );

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
	 * Convert input fields array to HTML.
	 *
	 * @param array $fields Array with fields data to convert to HTML.
	 *
	 * @return string
	 */
	public static function input_fields_html( array $fields ) {
		$html = '';

		foreach ( $fields as $field ) {
			if ( ! isset( $field['type'] ) ) {
				continue;
			}

			switch ( $field['type'] ) {
				case 'select':
					$html .= sprintf(
						'<label for="%s">%s</label> ',
						esc_attr( $field['id'] ),
						$field['label']
					);

					$html .= sprintf(
						'<select id="%s" name="%s">%s</select>',
						esc_attr( $field['id'] ),
						esc_attr( $field['name'] ),
						Pay_Util::select_options_grouped( $field['choices'] )
					);

					break;
				default:
					$html .= sprintf(
						'<label for="%s">%s</label> ',
						esc_attr( $field['id'] ),
						$field['label']
					);

					$type = $field['type'];

					if ( empty( $type ) ) {
						$type = 'text';
					}

					$attributes = array(
						'type' => $type,
						'id'   => $field['id'],
						'name' => $field['name'],
					);

					$html .= sprintf(
						'<input %s>',
						Pay_Util::array_to_html_attributes( $attributes )
					);

					break;
			}
		}

		return $html;
	}

	/**
	 * Method exists
	 *
	 * This helper function was created to fix an issue with `method_exists` calls
	 * and non existings classes.
	 *
	 * @param string $class  Class name to check for the specifiekd method.
	 * @param string $method Method name to check for existance.
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
}
