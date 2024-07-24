<?php
/**
 * Subscription Interval
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscription Interval
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.4.0
 * @link    https://github.com/briannesbitt/Carbon/blob/2.40.0/src/Carbon/CarbonInterval.php
 * @link    https://github.com/frak/s3bk/blob/master/src/S3Bk/Type/StringableInterval.php
 * @link    https://github.com/stylers-llc/laratask/blob/master/src/Support/DateInterval.php
 */
class SubscriptionInterval extends \DateInterval implements \JsonSerializable {
	/**
	 * Specification.
	 *
	 * @var string
	 */
	private $specification;

	/**
	 * Construct interval.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_8601#Durations
	 * @link https://www.php.net/manual/en/dateinterval.construct.php
	 * @link https://github.com/php/php-src/blob/php-7.4.10/ext/date/php_date.c#L414-L416
	 * @param string $specification An interval specification.
	 */
	public function __construct( $specification ) {
		$this->specification = $specification;

		parent::__construct( $specification );
	}

	/**
	 * Get specification.
	 *
	 * @return string
	 */
	public function get_specification() {
		return $this->specification;
	}

	/**
	 * Multiply.
	 *
	 * @param int $times Number of times to multiply with.
	 * @return SubscriptionInterval
	 * @throws \InvalidArgumentException Throws exception if times to multiply is zero.
	 */
	public function multiply( $times ) {
		if ( 0 === $times ) {
			throw new \InvalidArgumentException( 'Subscription interval cannot be multiplied by 0.' );
		}

		$invert = ( $times < 0 );

		$times = \absint( $times );

		$interval_spec = 'P';

		// Date.
		$date = \array_filter(
			[
				'Y' => $this->y * $times,
				'M' => $this->m * $times,
				'D' => $this->d * $times,
			]
		);

		foreach ( $date as $unit => $value ) {
			$interval_spec .= $value . $unit;
		}

		// Time.
		$time = \array_filter(
			[
				'H' => $this->h * $times,
				'M' => $this->i * $times,
				'S' => $this->s * $times,
			]
		);

		if ( count( $time ) > 0 ) {
			$interval_spec .= 'T';

			foreach ( $time as $unit => $value ) {
				$interval_spec .= $value . $unit;
			}
		}

		// Interval.
		$interval = new self( $interval_spec );

		$interval->invert = \intval( $invert );

		return $interval;
	}

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->specification;
	}

	/**
	 * To string.
	 *
	 * @link https://www.php.net/manual/en/language.oop5.magic.php#object.tostring
	 * @return string
	 */
	public function __toString() {
		return $this->specification;
	}
}
