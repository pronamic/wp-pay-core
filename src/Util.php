<?php
/**
 * Util
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use DateInterval;

/**
 * WordPress utility class
 *
 * @version 2.5.0
 * @since   2.0.1
 */
class Util {
	/**
	 * Format date interval.
	 *
	 * @param DateInterval $date_interval Date interval.
	 *
	 * @return string
	 */
	public static function format_date_interval( DateInterval $date_interval ) {
		// Periods.
		$periods = [];

		foreach ( [ 'y', 'm', 'd', 'h', 'i', 's' ] as $period ) {
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
		return match ( $period ) {
			/* translators: %s: interval */
			'D', 'day', 'days' => sprintf( _n( 'Every %s day', 'Every %s days', $interval, 'pronamic_ideal' ), $interval ),
			/* translators: %s: interval */
			'W', 'week', 'weeks' => sprintf( _n( 'Every %s week', 'Every %s weeks', $interval, 'pronamic_ideal' ), $interval ),
			/* translators: %s: interval */
			'M', 'month', 'months' => sprintf( _n( 'Every %s month', 'Every %s months', $interval, 'pronamic_ideal' ), $interval ),
			/* translators: %s: interval */
			'Y', 'year', 'years' => sprintf( _n( 'Every %s year', 'Every %s years', $interval, 'pronamic_ideal' ), $interval ),
			default => null,
		};
	}

	/**
	 * Convert single interval period character to full name.
	 *
	 * @param string $interval_period string Short interval period (D, W, M or Y).
	 *
	 * @return string
	 */
	public static function to_interval_name( $interval_period ) {
		return match ( $interval_period ) {
			'D' => 'days',
			'W' => 'weeks',
			'M' => 'months',
			'Y' => 'years',
			default => $interval_period,
		};
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
	 * Flattens a multi-dimensional array into a single level array that uses "square bracket" notation to indicate depth.
	 *
	 * @link https://github.com/pronamic/wp-pay-core/issues/73
	 * @param iterable $data   Data.
	 * @param string   $name   Parent.
	 * @param array    $result Result.
	 * @return array
	 */
	public static function array_square_bracket( $data, $name = '', $result = [] ) {
		foreach ( $data as $key => $item ) {
			if ( '' !== $name ) {
				$key = $name . '[' . $key . ']';
			}

			if ( is_array( $item ) ) {
				$result = self::array_square_bracket( $item, $key, $result );
			} else {
				$result[ $key ] = $item;
			}
		}

		return $result;
	}
	/**
	 * Get hidden inputs HTML for data.
	 *
	 * @param array $data Array with name and value pairs to convert to hidden HTML input elements.
	 *
	 * @return string
	 */
	public static function html_hidden_fields( $data ) {
		$html = '';

		$data = self::array_square_bracket( $data );

		foreach ( $data as $name => $value ) {
			$html .= sprintf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
		}

		return $html;
	}
}
