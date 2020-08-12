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
	 * @var string
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
	 * Interval unit, also know as:
	 * - Period designator.
	 *
	 * @link https://www.php.net/manual/en/dateinterval.construct.php
	 * @link https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_date-add
	 * @var string
	 */
	private $interval_unit;

	/**
	 * Interval value, also known as:
	 * - Interval number.
	 * - Interval count.
	 * - Interval expression.
	 *
	 * @var int
	 */
	private $interval_value;

	/**
	 * Number recurrences, also known as:
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
	private $number_recurrences;

	/**
	 * Number recurrences created.
	 *
	 * @var int
	 */
	private $number_recurrences_created;

	/**
	 * The date this period defintion will start.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $start_date;

	/**
	 * The date the create the next period.
	 *
	 * @var DateTimeImmutable|null
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
	 * @param DateTimeImmutable $start_date     Start date.
	 * @param string            $interval_unit  Interval unit.
	 * @param int               $interval_value Interval value.
	 * @param Money             $amount         Amount.
	 * @return void
	 */
	public function __construct( $start_date, $interval_unit, $interval_value, $amount ) {
		$this->sequence_number = 1;
		$this->start_date      = clone $start_date;
		$this->next_date       = clone $start_date;
		$this->interval_unit   = $interval_unit;
		$this->interval_value  = $interval_value;
		$this->amount          = $amount;

		$this->number_recurrences_created = 0;
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
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = $start_date;
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
	 * Set number recurrences
	 *
	 * @param int|null $number_recurrences Number recurrences.
	 * @return void
	 */
	public function set_number_recurrences( $number_recurrences ) {
		$this->number_recurrences = $number_recurrences;
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
	 * The period defintion is infinite when the number recurrences is undefined.
	 *
	 * @return bool True if infinite, false otherwise.
	 */
	public function is_infinite() {
		return ( null === $this->number_recurrences );
	}

	/**
	 * Check if this period definition is completed.
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_completed() {
		if ( 'completed' === $this->status ) {
			return true;
		}

		if ( $this->number_recurrences === $this->number_recurrences_created ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this period definition is canceled.
	 *
	 * @link https://www.grammarly.com/blog/canceled-vs-cancelled/
	 * @link https://docs.mollie.com/reference/v2/subscriptions-api/cancel-subscription
	 * @return bool True if canceled, false otherwise.
	 */
	public function is_canceled() {
		return ( 'canceled' === $this->status );
	}

	/**
	 * Check if this period definition is a trial.
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
	 * @return \DateInterval
	 * @throws \Exception Throws exception on invalid interval spec.
	 */
	public function get_date_interval() {
		$duration = 'P' . $this->interval_value . $this->interval_unit;

		return new \DateInterval( $duration );
	}

	/**
	 * Get end date.
	 *
	 * @return DateTimeImmutable|null
	 * @throws \Exception Throws exception on invalid interval spec.
	 */
	public function get_end_date() {
		if ( null === $this->number_recurrences ) {
			return null;
		}

		$date = clone $this->start_date;

		$interval = new \DateInterval( 'P' . ( $this->interval_value * $this->number_recurrences ) . $this->interval_unit );

		$date = $date->add( $interval );

		return $date;
	}

	/**
	 * Get next period.
	 *
	 * @return Period
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	public function get_next_period() {
		if ( null === $this->next_date ) {
			return null;
		}

		if ( $this->is_completed() ) {
			return null;
		}

		$start = clone $this->next_date;

		$end = clone $this->next_date;

		$end = $end->add( $this->get_date_interval() );

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

		$this->number_recurrences_created++;

		$this->next_date = null;

		if ( ! $this->is_completed() ) {
			$this->next_date = clone $next_period->get_end_date();
		}

		return $next_period;
	}

	/**
	 * Get JSON object.
	 *
	 * @return object
	 */
	public function jsonSerialize() {
		return (object) array(
			'type'         => $this->type,
			'name'         => $this->name,
			'status'       => $this->status,
			'start_date'   => $this->start_date->format( \DATE_ATOM ),
			'interval'     => (object) array(
				'unit'  => $this->interval_unit,
				'value' => $this->interval_value,
			),
			'amount'       => MoneyJsonTransformer::to_json( $this->amount ),
			'proration'    => $this->proration,
			// Readonly.
			'is_infinite'  => $this->is_infinite(),
			'is_completed' => $this->is_completed(),
			'is_canceled'  => $this->is_canceled(),
			'is_trial'     => $this->is_trial(),
		);
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
			->with_interval( $proration_difference->days, 'D' )
			->with_number_recurrences( 1 )
			->with_proration()
			->create();

		$phase->set_start_date( $proration_phase->get_end_date() );

		return $proration_phase;
	}
}
