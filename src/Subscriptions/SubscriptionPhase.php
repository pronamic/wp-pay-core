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
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\TaxedMoneyJsonTransformer;

/**
 * Subscription Phase
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionPhase implements \JsonSerializable {
	/**
	 * Canceled at.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $canceled_at;

	/**
	 * Amount.
	 *
	 * @var TaxedMoney
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
	 * @var DateTimeImmutable
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
	 * @param DateTimeImmutable    $start_date Start date.
	 * @param SubscriptionInterval $interval   Interval.
	 * @param TaxedMoney           $amount     Amount.
	 * @return void
	 */
	public function __construct( DateTimeImmutable $start_date, SubscriptionInterval $interval, TaxedMoney $amount ) {
		$this->start_date = $start_date;
		$this->interval   = $interval;
		$this->amount     = $amount;

		$this->periods_created = 0;
		$this->is_prorated     = false;
		$this->is_trial        = false;
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
		 * If there are periods created we add these created periods.
		 */
		return $this->add_interval( $this->start_date, $this->periods_created );
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
	public function set_canceled_at( DateTimeImmutable $canceled_at = null ) {
		$this->canceled_at = $canceled_at;
	}

	/**
	 * Get amount.
	 *
	 * @return TaxedMoney
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Set amount.
	 *
	 * @param TaxedMoney $amount Amount.
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
	 * @param int $periods_created The number of periods created.
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
			// Infinite.
			return null;
		}

		return $this->total_periods - $this->periods_created;
	}

	/**
	 * Is alignment.
	 *
	 * @return bool
	 */
	public function is_alignment() {
		return $this->is_alignment;
	}

	/**
	 * Set alignment.
	 *
	 * @param bool $is_alignment Alignment.
	 * @return void
	 */
	public function set_alignment( $is_alignment ) {
		$this->is_alignment = $is_alignment;
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
		return ( null === $this->total_periods );
	}

	/**
	 * Check if all periods are created.
	 *
	 * @return bool True if all periods are created, false otherwise.
	 */
	public function all_periods_created() {
		return ( $this->total_periods === $this->periods_created );
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
			// Infinite.
			return null;
		}

		return $this->add_interval( $this->start_date, $this->total_periods );
	}

	/**
	 * Add subscription phase interval to date.
	 *
	 * @param \DateTimeImmutable $date  Date to add interval period to.
	 * @param int                $times Number of times to add interval.
	 * @return \DateTimeImmutable
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
	 * Get next period.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return SubscriptionPeriod|null
	 */
	public function get_next_period( Subscription $subscription ) {
		if ( $this->all_periods_created() ) {
			return null;
		}

		$start = $this->get_next_date();

		if ( null === $start ) {
			return null;
		}

		$end = $this->add_interval( $start );

		$period = new SubscriptionPeriod(
			(int) $subscription->get_id(),
			new DateTime( $start->format( \DateTimeInterface::ATOM ) ),
			new DateTime( $end->format( \DateTimeInterface::ATOM ) ),
			$this->get_amount()
		);

		return $period;
	}

	/**
	 * Next period.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return SubscriptionPeriod|null
	 */
	public function next_period( Subscription $subscription ) {
		try {
			$next_period = $this->get_next_period( $subscription );
		} catch ( \Exception $e ) {
			return null;
		}

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
			'start_date'        => $this->start_date->format( \DATE_ATOM ),
			'interval'          => $this->interval->get_specification(),
			'amount'            => MoneyJsonTransformer::to_json( $this->amount ),
			// Numbers.
			'total_periods'     => $this->total_periods,
			'periods_created'   => $this->periods_created,
			'periods_remaining' => $this->get_periods_remaining(),
			// Other.
			'canceled_at'       => ( null === $this->canceled_at ) ? null : $this->canceled_at->format( \DATE_ATOM ),
			// Flags.
			'is_alignment'      => $this->is_alignment(),
			'is_prorated'       => $this->is_prorated(),
			'is_trial'          => $this->is_trial(),
			// Readonly.
			'is_infinite'       => $this->is_infinite(),
			'is_canceled'       => $this->is_canceled(),
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

		if ( property_exists( $json, 'start_date' ) ) {
			$builder->with_start_date( new \DateTimeImmutable( $json->start_date ) );
		}

		if ( property_exists( $json, 'interval' ) ) {
			$builder->with_interval( $json->interval );
		}

		if ( property_exists( $json, 'amount' ) ) {
			$builder->with_amount( TaxedMoneyJsonTransformer::from_json( $json->amount ) );
		}

		if ( property_exists( $json, 'total_periods' ) ) {
			$builder->with_total_periods( $json->total_periods );
		}

		if ( property_exists( $json, 'periods_created' ) ) {
			$builder->with_periods_created( $json->periods_created );
		}

		if ( property_exists( $json, 'is_alignment' ) ) {
			$builder->with_alignment( \boolval( $json->is_alignment ) );
		}

		if ( property_exists( $json, 'is_prorated' ) ) {
			$builder->with_proration( \boolval( $json->is_prorated ) );
		}

		if ( property_exists( $json, 'is_trial' ) ) {
			$builder->with_trial( \boolval( $json->is_trial ) );
		}

		if ( property_exists( $json, 'canceled_at' ) ) {
			$builder->with_canceled_at( new \DateTimeImmutable( $json->canceled_at ) );
		}

		return $builder->create();
	}

	/**
	 * Align the phase to align date.
	 *
	 * @param self              $phase          The phase to align.
	 * @param DateTimeImmutable $align_date     The alignment date.
	 * @param bool              $prorate_amount Flag to prorate the amount.
	 * @return SubscriptionPhase
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	public static function align( self $phase, DateTimeImmutable $align_date, $prorate_amount = false ) {
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
			->with_alignment( true )
			->with_proration( $prorate_amount )
			->create();

		// Remove one period from regular phase.
		$total_periods = $phase->get_total_periods();

		if ( null !== $total_periods ) {
			$phase->set_total_periods( $total_periods - 1 );
		}

		$phase->set_start_date( $proration_phase->get_end_date() );

		return $proration_phase;
	}
}
