<?php
/**
 * Alignment Rule
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateTimeImmutable;

/**
 * Alignment Rule
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
class AlignmentRule {
	/**
	 * Weekdays indexed 0 (for Sunday) through 6 (for Saturday).
	 *
	 * @var array<int, string>
	 */
	private static $weekdays = [
		0 => 'Sunday',
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
	];

	/**
	 * Frequency.
	 *
	 * @var string
	 */
	private $frequency;

	/**
	 * Day of the week.
	 *
	 * @var string|null
	 */
	private $by_day_of_the_week;

	/**
	 * Day of the month.
	 *
	 * @var int|null
	 */
	private $by_day_of_the_month;

	/**
	 * Number of month.
	 *
	 * @var int|null
	 */
	private $by_month;

	/**
	 * Construct prorating rule.
	 *
	 * @param string $frequency Frequency.
	 */
	public function __construct( $frequency ) {
		$this->frequency = $frequency;
	}

	/**
	 * By numeric day of the week.
	 *
	 * @param int $number Number of day in the week (0 = Sunday).
	 * @return $this
	 */
	public function by_numeric_day_of_the_week( $number ) {
		$this->by_day_of_the_week = self::$weekdays[ $number ];

		return $this;
	}

	/**
	 * By numeric day of the month.
	 *
	 * @param int $number Day of the month.
	 * @return $this
	 */
	public function by_numeric_day_of_the_month( $number ) {
		$this->by_day_of_the_month = $number;

		return $this;
	}

	/**
	 * By numeric month.
	 *
	 * @param int $number Number of month.
	 * @return $this
	 */
	public function by_numeric_month( $number ) {
		$this->by_month = $number;

		return $this;
	}

	/**
	 * Get date.
	 *
	 * @param DateTimeImmutable|null $date Date.
	 * @return DateTimeImmutable
	 * @throws \Exception Throws exception on date error.
	 */
	public function get_date( DateTimeImmutable $date = null ) {
		if ( null === $date ) {
			$date = new DateTimeImmutable();
		}

		return $this->apply_properties( $date );
	}

	/**
	 * Apply properties.
	 *
	 * @param DateTimeImmutable $date Date.
	 * @return DateTimeImmutable
	 */
	private function apply_properties( DateTimeImmutable $date ) {
		$year  = \intval( $date->format( 'Y' ) );
		$month = \intval( $date->format( 'm' ) );
		$day   = \intval( $date->format( 'd' ) );

		// 1 > null === true
		if ( $day >= $this->by_day_of_the_month && 'W' !== $this->frequency ) {
			++$month;
		}

		if ( null !== $this->by_day_of_the_month ) {
			$day = $this->by_day_of_the_month;
		}

		if ( null !== $this->by_month ) {
			if ( $month > $this->by_month ) {
				++$year;
			}

			$month = $this->by_month;
		}

		$date = $date->setDate( $year, $month, $day );

		// Day of the week.
		$day_of_the_week = $this->by_day_of_the_week;

		if ( null === $day_of_the_week && 'W' === $this->frequency ) {
			$day_of_the_week = $date->format( 'l' );
		}

		if ( null !== $day_of_the_week ) {
			$date = $date->modify( 'Next ' . $day_of_the_week );
		}

		return $date;
	}
}
