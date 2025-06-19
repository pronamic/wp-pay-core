<?php
/**
 * Subscription Phase
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\DateTime\DateTimeInterface;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;

/**
 * Subscription Phase
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
class SubscriptionPhase implements \JsonSerializable {
	/**
	 * Subscription.
	 *
	 * @var Subscription
	 */
	private $subscription;

	/**
	 * The sequence number.
	 *
	 * @var int|null
	 */
	private $sequence_number;

	/**
	 * Canceled at.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $canceled_at;

	/**
	 * Amount.
	 *
	 * @var Money
	 */
	private $amount;

	/**
	 * Interval.
	 *
	 * @var SubscriptionInterval
	 */
	private $interval;

	/**
	 * The date this phase will start.
	 *
	 * @var DateTimeImmutable
	 */
	private $start_date;

	/**
	 * The date this phase will end.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $end_date;

	/**
	 * Alignment rate.
	 *
	 * @var float|null
	 */
	private $alignment_rate;

	/**
	 * Proration.
	 *
	 * @var bool
	 */
	private $is_prorated;

	/**
	 * Boolean flag to indicate a trial subscription phase.
	 *
	 * @var bool
	 */
	private $is_trial;

	/**
	 * Construct subscription phase.
	 *
	 * @param Subscription         $subscription Subscription.
	 * @param \DateTimeInterface   $start_date   Start date.
	 * @param SubscriptionInterval $interval     Interval.
	 * @param Money                $amount       Amount.
	 * @return void
	 */
	public function __construct( Subscription $subscription, \DateTimeInterface $start_date, SubscriptionInterval $interval, Money $amount ) {
		$this->subscription = $subscription;

		$this->set_start_date( $start_date );

		$this->interval = $interval;
		$this->amount   = $amount;

		$this->is_prorated = false;
		$this->is_trial    = false;
	}

	/**
	 * Get subscription.
	 *
	 * @return Subscription
	 */
	public function get_subscription() {
		return $this->subscription;
	}

	/**
	 * Get sequence number.
	 *
	 * @return int|null
	 */
	public function get_sequence_number() {
		return $this->sequence_number;
	}

	/**
	 * Set sequence number.
	 *
	 * @param int $sequence_number Sequence number.
	 * @return void
	 */
	public function set_sequence_number( $sequence_number ) {
		$this->sequence_number = $sequence_number;
	}

	/**
	 * Get start date.
	 *
	 * @return DateTimeImmutable
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Set start date.
	 *
	 * @param \DateTimeInterface $start_date Start date.
	 * @return void
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = DateTimeImmutable::create_from_interface( $start_date );
	}

	/**
	 * Get end date.
	 *
	 * @return DateTimeImmutable|null
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Set end date.
	 *
	 * @param \DateTimeInterface|null $end_date End date.
	 * @return void
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = ( null === $end_date ) ? null : DateTimeImmutable::create_from_interface( $end_date );
	}

	/**
	 * Get next date.
	 *
	 * @return DateTimeImmutable|null
	 */
	public function get_next_date() {
		/**
		 * Check whether all periods have been created, if so there is no next date.
		 */
		if ( $this->all_periods_created() ) {
			return null;
		}

		/**
		 * Check whether phase has been canceled, if so there is no next date.
		 */
		if ( $this->is_canceled() ) {
			return null;
		}

		/**
		 * Ok.
		 */
		return $this->subscription->get_next_payment_date();
	}

	/**
	 * Set next date.
	 *
	 * @param \DateTimeInterface|null $next_date Next date.
	 * @return void
	 */
	public function set_next_date( $next_date ) {
		$this->subscription->set_next_payment_date( $next_date );
	}

	/**
	 * Check if this phase is canceled.
	 *
	 * @link https://www.grammarly.com/blog/canceled-vs-cancelled/
	 * @link https://docs.mollie.com/reference/v2/subscriptions-api/cancel-subscription
	 * @return bool True if canceled, false otherwise.
	 */
	public function is_canceled() {
		return ( null !== $this->canceled_at );
	}

	/**
	 * Get canceled date.
	 *
	 * @return DateTimeImmutable|null Canceled date or null if phase is not canceled (yet).
	 */
	public function get_canceled_at() {
		return $this->canceled_at;
	}

	/**
	 * Set canceled date.
	 *
	 * @param DateTimeImmutable|null $canceled_at Canceled date.
	 * @return void
	 */
	public function set_canceled_at( ?DateTimeImmutable $canceled_at = null ) {
		$this->canceled_at = $canceled_at;
	}

	/**
	 * Get amount.
	 *
	 * @return Money
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Set amount.
	 *
	 * @param Money $amount Amount.
	 * @return void
	 */
	public function set_amount( $amount ) {
		$this->amount = $amount;
	}

	/**
	 * Get total periods.
	 *
	 * @return int|null
	 */
	public function get_total_periods() {
		if ( null === $this->end_date ) {
			return null;
		}

		$period = new \DatePeriod( $this->start_date, $this->interval, $this->end_date );

		return \iterator_count( $period );
	}

	/**
	 * Set total periods.
	 *
	 * @param int|null $total_periods Total periods to create.
	 * @return void
	 */
	public function set_total_periods( $total_periods ) {
		$this->set_end_date( null === $total_periods ? null : $this->add_interval( $this->start_date, $total_periods ) );
	}

	/**
	 * Get periods created.
	 *
	 * @return int
	 */
	public function get_periods_created() {
		$next_payment_date = $this->subscription->get_next_payment_date();

		$end_date = $next_payment_date ?? $this->end_date;

		if ( null === $end_date ) {
			return 0;
		}

		if ( null !== $this->end_date && $end_date > $this->end_date ) {
			$end_date = $this->end_date;
		}

		$period = new \DatePeriod(
			new \DateTimeImmutable( $this->start_date->format( 'Y-m-d 00:00:00' ) ),
			$this->interval,
			new \DateTimeImmutable( $end_date->format( 'Y-m-d 00:00:00' ) )
		);

		return \iterator_count( $period );
	}

	/**
	 * Set periods created.
	 *
	 * @param int $periods_created The number of periods created.
	 * @return void
	 */
	public function set_periods_created( $periods_created ) {
		$this->set_next_date( $this->add_interval( $this->start_date, $periods_created ) );
	}

	/**
	 * Get the number of periods that are remaining.
	 *
	 * @return int|null
	 */
	public function get_periods_remaining() {
		if ( null === $this->end_date ) {
			// Infinite.
			return null;
		}

		$period = new \DatePeriod( $this->start_date, $this->interval, $this->end_date );

		$total_periods = \iterator_count( $period );

		return $total_periods - $this->get_periods_created();
	}

	/**
	 * Is alignment.
	 *
	 * @return bool
	 */
	public function is_alignment() {
		return ( null !== $this->alignment_rate );
	}

	/**
	 * Get alignment rate.
	 *
	 * @return float|null
	 */
	public function get_alignment_rate() {
		return $this->alignment_rate;
	}

	/**
	 * Set alignment rate.
	 *
	 * @param float|null $alignment_rate Alignment rate.
	 * @return void
	 */
	public function set_alignment_rate( $alignment_rate ) {
		$this->alignment_rate = $alignment_rate;
	}

	/**
	 * Is prorated.
	 *
	 * @return bool
	 */
	public function is_prorated() {
		return $this->is_prorated;
	}

	/**
	 * Set prorated.
	 *
	 * @param bool $is_prorated Proration.
	 * @return void
	 */
	public function set_prorated( $is_prorated ) {
		$this->is_prorated = $is_prorated;
	}

	/**
	 * Check if this phase is a trial.
	 *
	 * @return bool True if trial, false otherwise.
	 */
	public function is_trial() {
		return $this->is_trial;
	}

	/**
	 * Set trial.
	 *
	 * @param bool $is_trial Trial.
	 * @return void
	 */
	public function set_trial( $is_trial ) {
		$this->is_trial = $is_trial;
	}

	/**
	 * The subscription phase is infinite when the total periods number is undefined.
	 *
	 * @return bool True if infinite, false otherwise.
	 */
	public function is_infinite() {
		return ( null === $this->end_date );
	}

	/**
	 * Check if all periods are created.
	 *
	 * @return bool True if all periods are created, false otherwise.
	 */
	public function all_periods_created() {
		return $this->is_completed_to_date( $this->subscription->get_next_payment_date() );
	}

	/**
	 * Check if this phase is completed to date.
	 *
	 * @param DateTimeInterface|null $date Date.
	 * @return bool True if phase is completed to date, false otherwise.
	 */
	public function is_completed_to_date( ?DateTimeInterface $date = null ) {
		if ( null === $date ) {
			return true;
		}

		if ( null === $this->end_date ) {
			return false;
		}

		return $date >= $this->end_date;
	}

	/**
	 * Get interval.
	 *
	 * @link https://www.php.net/manual/en/class.dateinterval.php
	 * @link https://www.php.net/manual/en/dateinterval.construct.php
	 * @return SubscriptionInterval
	 */
	public function get_interval() {
		return $this->interval;
	}

	/**
	 * Add subscription phase interval to date.
	 *
	 * @param DateTimeImmutable $date  Date to add interval period to.
	 * @param int               $times Number of times to add interval.
	 * @return DateTimeImmutable
	 */
	private function add_interval( $date, $times = 1 ) {
		// If times is zero there is nothing to add.
		if ( 0 === $times ) {
			return $date;
		}

		// Multiply date interval.
		return $date->add( $this->interval->multiply( $times ) );
	}

	/**
	 * Get period for the specified start date.
	 *
	 * @param DateTimeImmutable $start_date Start date.
	 * @return SubscriptionPeriod|null
	 * @throws \Exception Throws exception on invalid date period.
	 */
	public function get_period( ?DateTimeImmutable $start_date = null ) {
		if ( null === $start_date ) {
			return null;
		}

		if ( $this->start_date > $start_date ) {
			return null;
		}

		$end_date = $this->add_interval( $start_date );

		if ( null !== $this->end_date && $end_date > $this->end_date ) {
			$end_date = $this->end_date;

			if ( $start_date > $end_date ) {
				throw new \Exception( 'The start date of a subscription period cannot be later than the end date.' );
			}
		}

		$period = new SubscriptionPeriod( $this, $start_date, $end_date, $this->get_amount() );

		return $period;
	}

	/**
	 * Get next period.
	 *
	 * @return SubscriptionPeriod|null
	 */
	public function get_next_period() {
		return $this->get_period( $this->get_next_date() );
	}

	/**
	 * Next period.
	 *
	 * This method works like the PHP native `next` function, it will advance the internal
	 * pointer of this subscription phase.
	 *
	 * @return SubscriptionPeriod|null
	 */
	public function next_period() {
		$next_period = $this->get_next_period();

		if ( null === $next_period ) {
			return null;
		}

		$this->set_next_date( $next_period->get_end_date() );

		return $next_period;
	}

	/**
	 * Get JSON object.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return (object) [
			'subscription'      => (object) [
				'$ref' => \rest_url(
					\sprintf(
						'/%s/%s/%d',
						'pronamic-pay/v1',
						'subscriptions',
						$this->subscription->get_id()
					)
				),
			],
			'sequence_number'   => $this->get_sequence_number(),
			'start_date'        => $this->start_date->format( \DATE_ATOM ),
			'end_date'          => ( null === $this->end_date ) ? null : $this->end_date->format( \DATE_ATOM ),
			'interval'          => $this->interval->get_specification(),
			'amount'            => $this->amount->jsonSerialize(),
			// Numbers.
			'total_periods'     => $this->get_total_periods(),
			'periods_created'   => $this->get_periods_created(),
			'periods_remaining' => $this->get_periods_remaining(),
			// Other.
			'canceled_at'       => ( null === $this->canceled_at ) ? null : $this->canceled_at->format( \DATE_ATOM ),
			'alignment_rate'    => $this->alignment_rate,
			// Flags.
			'is_alignment'      => $this->is_alignment(),
			'is_prorated'       => $this->is_prorated(),
			'is_trial'          => $this->is_trial(),
			// Readonly.
			'is_infinite'       => $this->is_infinite(),
			'is_canceled'       => $this->is_canceled(),
		];
	}

	/**
	 * Create subscription phase from object.
	 *
	 * @param mixed $json JSON.
	 * @return SubscriptionPhase
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( ! isset( $json->subscription ) ) {
			throw new \InvalidArgumentException( 'Object must contain `subscription` property.' );
		}

		if ( ! isset( $json->start_date ) ) {
			throw new \InvalidArgumentException( 'Object must contain `start_date` property.' );
		}

		if ( ! isset( $json->interval ) ) {
			throw new \InvalidArgumentException( 'Object must contain `interval` property.' );
		}

		if ( ! isset( $json->amount ) ) {
			throw new \InvalidArgumentException( 'Object must contain `amount` property.' );
		}

		$start_date = new DateTimeImmutable( $json->start_date );

		$phase = new self(
			$json->subscription,
			$start_date,
			new SubscriptionInterval( $json->interval ),
			MoneyJsonTransformer::from_json( $json->amount )
		);

		if ( property_exists( $json, 'total_periods' ) ) {
			$phase->set_total_periods( $json->total_periods );
		}

		if ( property_exists( $json, 'end_date' ) ) {
			$phase->set_end_date( null === $json->end_date ? null : new DateTimeImmutable( $json->end_date ) );
		}

		if ( property_exists( $json, 'periods_created' ) ) {
			$phase->set_periods_created( $json->periods_created );
		}

		if ( property_exists( $json, 'alignment_rate' ) ) {
			$phase->set_alignment_rate( $json->alignment_rate );
		}

		if ( property_exists( $json, 'is_prorated' ) ) {
			$phase->set_prorated( \boolval( $json->is_prorated ) );
		}

		if ( property_exists( $json, 'is_trial' ) ) {
			$phase->set_trial( \boolval( $json->is_trial ) );
		}

		if ( property_exists( $json, 'canceled_at' ) ) {
			if ( null !== $json->canceled_at ) {
				$phase->set_canceled_at( new DateTimeImmutable( $json->canceled_at ) );
			}
		}

		return $phase;
	}

	/**
	 * Align the phase to align date.
	 *
	 * @param self               $phase          The phase to align.
	 * @param \DateTimeInterface $align_date     The alignment date.
	 * @return SubscriptionPhase
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	public static function align( self $phase, \DateTimeInterface $align_date ) {
		$start_date = $phase->get_start_date();

		$next_date = $start_date->add( $phase->get_interval() );

		$regular_difference = $start_date->diff( $next_date, true );

		/**
		 * PHPStan fix.
		 *
		 * If the DateInterval object was created by DateTime::diff(), then this is the total
		 * number of days between the start and end dates. Otherwise, days will be FALSE.
		 */
		if ( false === $regular_difference->days ) {
			throw new \Exception( 'Could not calculate the total number of days between the phase start date and the next period start date.' );
		}

		$alignment_difference = $start_date->diff( $align_date, true );

		/**
		 * PHPStan fix.
		 *
		 * If the DateInterval object was created by DateTime::diff(), then this is the total
		 * number of days between the start and end dates. Otherwise, days will be FALSE.
		 */
		if ( false === $alignment_difference->days ) {
			throw new \Exception( 'Could not calculate the total number of days between the phase start date and the next alignment date.' );
		}

		$alignment_interval = new SubscriptionInterval( 'P' . $alignment_difference->days . 'D' );

		$alignment_phase = new self( $phase->get_subscription(), $start_date, $alignment_interval, $phase->get_amount() );

		$alignment_end_date = $start_date->add( $alignment_interval );

		$alignment_phase->set_end_date( $alignment_end_date );
		$alignment_phase->set_alignment_rate( $alignment_difference->days / $regular_difference->days );

		$phase->set_start_date( $alignment_end_date );

		if ( null !== $phase->end_date ) {
			$end_date = $phase->end_date->add( $alignment_interval );

			$phase->set_end_date( $end_date );
		}

		return $alignment_phase;
	}
}
