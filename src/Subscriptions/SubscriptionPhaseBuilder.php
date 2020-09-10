<?php
/**
 * Subscription Phase Builder
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateTimeImmutable;
use Pronamic\WordPress\Money\Money;

/**
 * Subscription Phase Builder
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionPhaseBuilder {
	/**
	 * Start date.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $start_date;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Type.
	 *
	 * @var string|null
	 */
	private $type;

	/**
	 * Total periods.
	 *
	 * @var int|null
	 */
	private $total_periods;

	/**
	 * Periods created.
	 *
	 * @var int|null
	 */
	private $periods_created;

	/**
	 * Amount.
	 *
	 * @var Money|null
	 */
	private $amount;

	/**
	 * Interval value.
	 *
	 * @var int|null
	 */
	private $interval_value;

	/**
	 * Interval unit.
	 *
	 * @var string|null
	 */
	private $interval_unit;

	/**
	 * Whether to prorate amount.
	 *
	 * @var bool|null
	 */
	private $proration;

	/**
	 * With start date.
	 *
	 * @param DateTimeImmutable $start_date Start date.
	 * @return $this
	 */
	public function with_start_date( DateTimeImmutable $start_date ) {
		$this->start_date = $start_date;

		return $this;
	}

	/**
	 * With name.
	 *
	 * @param string $name Name.
	 * @return $this
	 */
	public function with_name( $name ) {
		$this->name = $name;

		return $this;
	}

	/**
	 * With status.
	 *
	 * @param string $status Status.
	 * @return $this
	 */
	public function with_status( $status ) {
		$this->status = $status;

		return $this;
	}

	/**
	 * With type.
	 *
	 * @param string $type Type.
	 * @return $this
	 */
	public function with_type( $type ) {
		$this->type = $type;

		return $this;
	}

	/**
	 * With total periods.
	 *
	 * @param int $total_periods Number of periods to create.
	 * @return $this
	 */
	public function with_total_periods( $total_periods ) {
		$this->total_periods = $total_periods;

		return $this;
	}

	/**
	 * With periods created.
	 *
	 * @param int $periods_created Number of periods created.
	 * @return $this
	 */
	public function with_periods_created( $periods_created ) {
		$this->periods_created = $periods_created;

		return $this;
	}

	/**
	 * With amount.
	 *
	 * @param Money $amount Amount.
	 * @return $this
	 */
	public function with_amount( $amount ) {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * With interval.
	 *
	 * @param int    $interval_value Interval value.
	 * @param string $interval_unit  Interval unit.
	 * @return $this
	 */
	public function with_interval( $interval_value, $interval_unit ) {
		$this->interval_value = $interval_value;
		$this->interval_unit  = $interval_unit;

		return $this;
	}

	/**
	 * With proration.
	 *
	 * @return $this
	 */
	public function with_proration() {
		$this->proration = true;

		return $this;
	}

	/**
	 * Create subscription phase.
	 *
	 * @return SubscriptionPhase
	 */
	public function create() {
		$phase = new SubscriptionPhase( $this->start_date, $this->interval_unit, $this->interval_value, $this->amount );

		$phase->set_type( $this->type );
		$phase->set_total_periods( $this->total_periods );
		$phase->set_proration( $this->proration );

		if ( null !== $this->periods_created ) {
			$phase->set_periods_created( $this->periods_created );
		}

		return $phase;
	}
}
