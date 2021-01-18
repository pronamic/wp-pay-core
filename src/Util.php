<?php
/**
 * Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use DateInterval;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Money\Money;
use SimpleXMLElement;
use WP_Error;

/**
 * WordPress utility class
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.0.1
 */
class Util {
	/**
	 * Remote get body.
	 *
	 * @link       https://developer.wordpress.org/reference/functions/wp_remote_request/
	 *
	 * @param string $url                    The URL to use for the remote request.
	 * @param int    $required_response_code The required response code.
	 * @param array  $args                   The WordPress HTTP API request arguments.
	 *
	 * @deprecated 2.0.9 Use Pronamic\WordPress\Pay\Core\Util::remote_get_body() instead.
	 *
	 * @return array|bool|string|WP_Error
	 */
	public static function remote_get_body( $url, $required_response_code = 200, array $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::remote_get_body()' );

		return Core_Util::remote_get_body( $url, $required_response_code, $args );
	}

	/**
	 * SimpleXML load string.
	 *
	 * @param string $string The XML string to convert to a SimpleXMLElement object.
	 *
	 * @deprecated 2.0.9 Use Pronamic\WordPress\Pay\Core\Util::simplexml_load_string() instead.
	 *
	 * @return SimpleXMLElement|WP_Error
	 */
	public static function simplexml_load_string( $string ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::simplexml_load_string()' );

		return Core_Util::simplexml_load_string( $string );
	}

	/**
	 * Amount to cents.
	 *
	 * @param float $price The amount to convert to cents.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Money\Money::get_cents() instead.
	 *
	 * @return float
	 */
	public static function amount_to_cents( $price ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Money\Money::get_cents()' );

		$money = new Money( $price );

		return $money->get_cents();
	}

	/**
	 * Cents to amount.
	 *
	 * @param int $cents The numberof cents to convert to an amount.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Pay\Core\Util::cents_to_amount() instead.
	 *
	 * @return float
	 */
	public static function cents_to_amount( $cents ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::cents_to_amount()' );

		return Core_Util::cents_to_amount( $cents );
	}

	/**
	 * Convert boolean to an numceric boolean.
	 *
	 * @link https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 *
	 * @param boolean $boolean The boolean to convert to 1 or 0.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Pay\Core\Util::boolean_to_numeric() instead.
	 *
	 * @return int
	 */
	public static function boolean_to_numeric( $boolean ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::boolean_to_numeric()' );

		return Core_Util::boolean_to_numeric( $boolean );
	}

	/**
	 * Convert boolean to an string boolean.
	 *
	 * @link https://github.com/eet-nu/buckaroo-ideal/blob/master/lib/buckaroo-ideal/request.rb#L136
	 *
	 * @param boolean $boolean The boolean to convert to the string 'true' or 'false'.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Pay\Core\Util::boolean_to_string() instead.
	 *
	 * @return string
	 */
	public static function boolean_to_string( $boolean ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::boolean_to_string()' );

		return Core_Util::boolean_to_string( $boolean );
	}

	/**
	 * Format date interval.
	 *
	 * @param DateInterval $date_interval Date interval.
	 *
	 * @return string
	 */
	public static function format_date_interval( DateInterval $date_interval ) {
		// Periods.
		$periods = array();

		foreach ( array( 'y', 'm', 'd', 'h', 'i', 's' ) as $period ) {
			$value = $date_interval->$period;

			// Check value.
			if ( 0 === $value ) {
				continue;
			}

			// Format.
			$format = '';

			switch ( $period ) {
				case 'y':
					/* translators: %s: number of years */
					$format = _n( '%s year', '%s years', $value, 'pronamic_ideal' );

					break;
				case 'm':
					/* translators: %s: number of months */
					$format = _n( '%s month', '%s months', $value, 'pronamic_ideal' );

					break;
				case 'd':
					/* translators: %s: number of days */
					$format = _n( '%s day', '%s days', $value, 'pronamic_ideal' );

					break;
				case 'h':
					/* translators: %s: number of hours */
					$format = _n( '%s hour', '%s hours', $value, 'pronamic_ideal' );

					break;
				case 'i':
					/* translators: %s: number of minutes */
					$format = _n( '%s minute', '%s minutes', $value, 'pronamic_ideal' );

					break;
				case 's':
					/* translators: %s: number of seconds */
					$format = _n( '%s second', '%s seconds', $value, 'pronamic_ideal' );

					break;
			}

			// Add period.
			$periods[] = \sprintf( $format, $value );
		}

		// Multiple periods.
		if ( count( $periods ) > 1 ) {
			$last_period = \array_pop( $periods );

			$formatted = \implode( ', ', $periods );

			return sprintf(
				/* translators: 1: formatted periods, 2: last formatted period */
				__( '%1$s and %2$s', 'pronamic_ideal' ),
				$formatted,
				$last_period
			);
		}

		// Single period.
		$formatted = \implode( ', ', $periods );

		return $formatted;
	}

	/**
	 * Format recurrences date interval.
	 *
	 * @param DateInterval $date_interval Date interval.
	 *
	 * @return string
	 */
	public static function format_recurrences( DateInterval $date_interval ) {
		$formatted_interval = self::format_date_interval( $date_interval );

		// Check empty date interval.
		if ( empty( $formatted_interval ) ) {
			return '—';
		}

		return sprintf(
			/* translators: %s: formatted date interval periods */
			__( 'Every %s', 'pronamic_ideal' ),
			$formatted_interval
		);
	}

	/**
	 * Format interval.
	 *
	 * @param int    $interval The interval number.
	 * @param string $period   The period indicator.
	 *
	 * @return string|null
	 */
	public static function format_interval( $interval, $period ) {
		switch ( $period ) {
			case 'D':
			case 'day':
			case 'days':
				/* translators: %s: interval */
				return sprintf( _n( 'Every %s day', 'Every %s days', $interval, 'pronamic_ideal' ), $interval );
			case 'W':
			case 'week':
			case 'weeks':
				/* translators: %s: interval */
				return sprintf( _n( 'Every %s week', 'Every %s weeks', $interval, 'pronamic_ideal' ), $interval );
			case 'M':
			case 'month':
			case 'months':
				/* translators: %s: interval */
				return sprintf( _n( 'Every %s month', 'Every %s months', $interval, 'pronamic_ideal' ), $interval );
			case 'Y':
			case 'year':
			case 'years':
				/* translators: %s: interval */
				return sprintf( _n( 'Every %s year', 'Every %s years', $interval, 'pronamic_ideal' ), $interval );
		}

		return null;
	}

	/**
	 * Convert single interval period character to full name.
	 *
	 * @param string $interval_period string Short interval period (D, W, M or Y).
	 *
	 * @return string
	 */
	public static function to_interval_name( $interval_period ) {
		switch ( $interval_period ) {
			case 'D':
				return 'days';
			case 'W':
				return 'weeks';
			case 'M':
				return 'months';
			case 'Y':
				return 'years';
		}

		return $interval_period;
	}

	/**
	 * Format frequency.
	 *
	 * @param int $frequency The number of times.
	 *
	 * @return string
	 */
	public static function format_frequency( $frequency ) {
		if ( empty( $frequency ) ) {
			return _x( 'Unlimited', 'Recurring payment', 'pronamic_ideal' );
		}

		/* translators: %s: frequency */
		return sprintf( _n( '%s period', '%s periods', $frequency, 'pronamic_ideal' ), $frequency );
	}

	/**
	 * Build URL with the specified parameters
	 *
	 * @param string $url        The URL to extend with specified parameters.
	 * @param array  $parameters The parameters to add to the specified URL.
	 *
	 * @deprecated 2.0.9 Use \Pronamic\WordPress\Pay\Core\Util::build_url() instead.
	 *
	 * @return string
	 */
	public static function build_url( $url, array $parameters ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Pronamic\WordPress\Pay\Core\Util::build_url()' );

		return Core_Util::build_url( $url, $parameters );
	}

	/**
	 * Get hidden inputs HTML for data.
	 *
	 * @param array $data Array with name and value pairs to convert to hidden HTML input eleemnts.
	 *
	 * @return string
	 */
	public static function html_hidden_fields( $data ) {
		$html = '';

		foreach ( $data as $name => $value ) {
			$html .= sprintf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
		}

		return $html;
	}

	/**
	 * Array to HTML attributes.
	 *
	 * @param array $attributes The key and value pairs to convert to HTML attributes.
	 *
	 * @return string
	 */
	public static function array_to_html_attributes( array $attributes ) {
		$html = '';

		foreach ( $attributes as $key => $value ) {
			// Check boolean attribute.
			if ( \is_bool( $value ) ) {
				if ( $value ) {
					$html .= sprintf( '%s ', $key );
				}

				continue;
			}

			$html .= sprintf( '%s="%s" ', $key, esc_attr( $value ) );
		}

		$html = trim( $html );

		return $html;
	}

	/**
	 * Select options grouped.
	 *
	 * @param array  $groups         The grouped select options.
	 * @param string $selected_value The selected value.
	 *
	 * @return string
	 */
	public static function select_options_grouped( $groups, $selected_value = null ) {
		$html = '';

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				$optgroup = isset( $group['name'] ) && ! empty( $group['name'] );

				if ( $optgroup ) {
					$html .= '<optgroup label="' . $group['name'] . '">';
				}

				foreach ( $group['options'] as $value => $label ) {
					$html .= '<option value="' . $value . '" ' . selected( $selected_value, $value, false ) . '>' . $label . '</option>';
				}

				if ( $optgroup ) {
					$html .= '</optgroup>';
				}
			}
		}

		return $html;
	}
}
