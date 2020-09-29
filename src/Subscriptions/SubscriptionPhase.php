<?php
/**
 * Subscription Phase
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateTimeImmutable;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;

/**
 * Subscription Phase
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionPhase implements \JsonSerializable {
	/**
	 * Boolean flag to allow month overflow.
	 *
	 * @link https://carbon.nesbot.com/docs/#overflow-static-helpers
	 * @var bool
	 */
	private $month_overflow = false;

	/**
	 * The sequence number.
	 *
	 * @var int
	 */
	private $sequence_number;

	/**
	 * Type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Status.
	 *
	 * @var string
	 */
	private $status;

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
	 * Total periods, also known as:
	 * - Number recurrences
	 * - Frequency.
	 * - Times.
	 * - Recurrences.
	 * - Cycles number.
	 * - Total cycles.
	 * - Maximum renewals.
	 * - Product length.
	 * - Limit cycles number.
	 * - Number billing cycles.
	 *
	 * @var int|null
	 */
	private $total_periods;

	/**
	 * Number periods created, also known as:
	 * - Number recurrences created.
	 *
	 * @var int
	 */
	private $periods_created;

	/**
	 * The date this phase will start.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $start_date;

	/**
	 * The start date of the next period, also known as:
	 * - Billing cycle anchor (billing_cycle_anchor).
	 * - Period anchor.
	 *
	 * @link https://stripe.com/docs/billing/subscriptions/billing-cycle
	 * @link https://stripe.com/docs/api/subscriptions/create#create_subscription-billing_cycle_anchor
	 * @var DateTimeImmutable
	 */
	private $next_date;

	/**
	 * Proration.
	 *
	 * @var bool
	 */
	private $proration;

	/**
	 * Construct subscription phase.
	 *
	 * @param DateTimeImmutable $start_date    Start date.
	 * @param string            $interval_spec Interval specification.
	 * @param Money             $amount        Amount.
	 * @return void
	 */
	public function __construct( $start_date, $interval_spec, $amount ) {
		$this->sequence_number = 1;
		$this->start_date      = clone $start_date;
		$this->amount          = $amount;

		$this->periods_created = 0;

		$this->interval = new SubscriptionInterval( $interval_spec );
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get sequence number.
	 *
	 * @return int
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
	 * @param DateTimeImmutable $start_date Start date.
	 * @return void
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = $start_date;
	}

	/**
	 * Get next date.
	 *
	 * @return DateTimeImmutable
	 * @throws \InvalidArgumentException Throws invalid argument exception without start date.
	 */
	public function get_next_date() {
		$start = $this->start_date;

		if ( null === $start ) {
			throw new \InvalidArgumentException( 'Can not get next date of subscription phase without start date.' );
		}

		// Calculate next date.
		$next_date = null;

		if ( ! $this->is_completed() ) {
			if ( 0 === $this->periods_created ) {
				return $start;
			}

			$next_date = $this->add_interval( $start, $this->periods_created );
		}

		return $next_date;
	}

	/**
	 * Set status.
	 *
	 * @param string $status Status.
	 * @return void
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Set type.
	 *
	 * @param string $type Type.
	 * @return void
	 */
	public function set_type( $type ) {
		$this->type = $type;
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
		return $this->total_periods;
	}

	/**
	 * Set total periods.
	 *
	 * @param int|null $total_periods Total periods to create.
	 * @return void
	 */
	public function set_total_periods( $total_periods ) {
		$this->total_periods = $total_periods;
	}

	/**
	 * Get periods created.
	 *
	 * @return int
	 */
	public function get_periods_created() {
		return $this->periods_created;
	}

	/**
	 * Set periods created.
	 *
	 * @param int|null $periods_created The number of periods created.
	 * @return void
	 */
	public function set_periods_created( $periods_created ) {
		$this->periods_created = $periods_created;
	}

	/**
	 * Get the number of periods that are remaining.
	 *
	 * @return int|null
	 */
	public function get_periods_remaining() {
		if ( null === $this->total_periods ) {
			return null;
		}

		return $this->total_periods - $this->periods_created;
	}

	/**
	 * Is proration?
	 *
	 * @return bool
	 */
	public function is_proration() {
		return $this->proration;
	}

	/**
	 * Set proration
	 *
	 * @param bool $proration Proration.
	 * @return void
	 */
	public function set_proration( $proration ) {
		$this->proration = $proration;
	}

	/**
	 * The subscription phase is infinite when the total periods number is undefined.
	 *
	 * @return bool True if infinite, false otherwise.
	 */
	public function is_infinite() {
		return ( null === $this->total_periods );
	}

	/**
	 * Check if this phase is completed.
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_completed() {
		if ( 'completed' === $this->status ) {
			return true;
		}

		if ( $this->total_periods === $this->periods_created ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this phase is canceled.
	 *
	 * @link https://www.grammarly.com/blog/canceled-vs-cancelled/
	 * @link https://docs.mollie.com/reference/v2/subscriptions-api/cancel-subscription
	 * @return bool True if canceled, false otherwise.
	 */
	public function is_canceled() {
		return ( 'canceled' === $this->status );
	}

	/**
	 * Check if this phase is a trial.
	 *
	 * @return bool True if trial, false otherwise.
	 */
	public function is_trial() {
		return ( 'trial' === $this->type );
	}

	/**
	 * Get interval date.
	 *
	 * @link https://www.php.net/manual/en/class.dateinterval.php
	 * @link https://www.php.net/manual/en/dateinterval.construct.php
	 * @return SubscriptionInterval
	 */
	public function get_date_interval() {
		return $this->interval;
	}

	/**
	 * Get end date.
	 *
	 * @return DateTimeImmutable|null
	 * @throws \Exception Throws exception on invalid interval spec.
	 */
	public function get_end_date() {
		if ( null === $this->total_periods ) {
			return null;
		}

		$start_date = clone $this->start_date;

		$end_date = $this->add_interval( $start_date, $this->total_periods );

		return $end_date;
	}

	/**
	 * Add subscription phase interval to date.
	 *
	 * @param \DateTimeImmutable $date  Date to add interval period to.
	 * @param int                $times Number of times to add interval.
	 * @return \DateTimeImmutable
	 */
	private function add_interval( $date, $times = 1 ) {
		$interval = $this->interval;

		// Multiply date interval.
		if ( 1 !== $times ) {
			$interval = $interval->multiply( $times );
		}

		$date = $date->add( $interval );

		/**
		 * Month overflow.
		 *
		 * @link https://carbon.nesbot.com/docs/#overflow-static-helpers
		 * @link https://github.com/briannesbitt/Carbon/blob/2.38.0/src/Carbon/Traits/Units.php#L309-L311
		 * @link https://stackoverflow.com/questions/3602405/php-datetimemodify-adding-and-subtracting-months
		 */
		if ( false === $this->month_overflow && $this->interval->m > 0 ) {
			$day_1 = $this->start_date->format( 'd' );
			$day_2 = $date->format( 'd' );

			if ( $day_1 > 28 && $day_2 < 3 ) {
				$date = $date->modify( 'last day of previous month' );

				return $date;
			}

			if ( $day_1 !== $day_2 ) {
				$date = $date->modify( 'last day of this month' );

				return $date;
			}
		}

		return $date;
	}

	/**
	 * Get next period.
	 *
	 * @return Period
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	public function get_next_period() {
		if ( $this->is_completed() ) {
			return null;
		}

		$start = $this->get_next_date();
		$end   = $this->add_interval( $start );

		$period = new Period( null, $this, $start, $end, clone $this->amount );

		return $period;
	}

	/**
	 * Next period.
	 *
	 * @return Period|null
	 */
	public function next_period() {
		$next_period = $this->get_next_period();

		if ( null === $next_period ) {
			return null;
		}

		$this->periods_created++;

		return $next_period;
	}

	/**
	 * Get JSON object.
	 *
	 * @return object
	 */
	public function jsonSerialize() {
		return (object) array(
			'type'              => $this->type,
			'name'              => $this->name,
			'status'            => $this->status,
			'start_date'        => $this->start_date->format( \DATE_ATOM ),
			'interval'          => $this->interval->get_specification(),
			'amount'            => MoneyJsonTransformer::to_json( $this->amount ),
			'proration'         => $this->proration,
			// Numbers.
			'total_periods'     => $this->total_periods,
			'periods_created'   => $this->periods_created,
			'periods_remaining' => $this->get_periods_remaining(),
			// Readonly.
			'is_infinite'       => $this->is_infinite(),
			'is_completed'      => $this->is_completed(),
			'is_canceled'       => $this->is_canceled(),
			'is_trial'          => $this->is_trial(),
		);
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

		$builder = new SubscriptionPhaseBuilder();

		if ( property_exists( $json, 'type' ) ) {
			$builder->with_type( $json->type );
		}

		if ( property_exists( $json, 'name' ) ) {
			$builder->with_name( $json->name );
		}

		if ( property_exists( $json, 'status' ) ) {
			$builder->with_status( $json->status );
		}

		if ( property_exists( $json, 'start_date' ) ) {
			$builder->with_start_date( new \DateTimeImmutable( $json->start_date ) );
		}

		if ( property_exists( $json, 'interval' ) ) {
			$interval = $json->interval;

			// @todo Remove development transform from interval object to interval specification.
			if ( \is_object( $interval ) ) {
				$interval = 'P' . $interval->value . $interval->unit;
			}

			$builder->with_interval( $interval );
		}

		if ( property_exists( $json, 'amount' ) ) {
			$builder->with_amount( MoneyJsonTransformer::from_json( $json->amount ) );
		}

		if ( property_exists( $json, 'total_periods' ) ) {
			$builder->with_total_periods( $json->total_periods );
		}

		if ( property_exists( $json, 'periods_created' ) ) {
			$builder->with_periods_created( $json->periods_created );
		}

		return $builder->create();
	}

	/**
	 * Prorate the phase to align date.
	 *
	 * @param self              $phase          The phase to align.
	 * @param DateTimeImmutable $align_date     The alignment date.
	 * @param bool              $prorate_amount Flag to prorate the amount.
	 * @return SubscriptionPhase
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	public static function prorate( self $phase, DateTimeImmutable $align_date, $prorate_amount ) {
		$start_date = $phase->get_start_date();

		$next_date = $start_date->add( $phase->get_date_interval() );

		$regular_difference   = $start_date->diff( $next_date, true );
		$proration_difference = $start_date->diff( $align_date, true );

		$proration_amount = clone $phase->get_amount();

		if ( $prorate_amount ) {
			$proration_amount = $proration_amount->divide( $regular_difference->days )->multiply( $proration_difference->days );
		}

		$proration_phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $proration_amount )
			->with_interval( 'P' . $proration_difference->days . 'D' )
			->with_total_periods( 1 )
			->with_proration()
			->create();

		// Remove one period from regular phase.
		$total_periods = $phase->get_total_periods();

		if ( null !== $total_periods ) {
			$phase->set_total_periods( --$total_periods );
		}

		$phase->set_start_date( $proration_phase->get_end_date() );

		return $proration_phase;
	}
}
