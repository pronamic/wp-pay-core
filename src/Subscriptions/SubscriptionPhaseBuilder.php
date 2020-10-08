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
use Pronamic\WordPress\Money\TaxedMoney;

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
	 * @var TaxedMoney|null
	 */
	private $amount;

	/**
	 * Interval.
	 *
	 * @var SubscriptionInterval|null
	 */
	private $interval;

	/**
	 * Boolean flag to indicate a alignment subscription phase.
	 *
	 * @var bool
	 */
	private $is_alignment = false;

	/**
	 * Boolean flag to indicate a prorated subscription phase.
	 *
	 * @var bool
	 */
	private $is_prorated = false;

	/**
	 * Boolean flag to indicate a trial subscription phase.
	 *
	 * @var bool
	 */
	private $is_trial = false;

	/**
	 * Canceled at.
	 *
	 * @var DateTimeImmutable|null
	 */
	private $canceled_at;

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
	 * With canceled at.
	 *
	 * @param DateTimeImmutable|null $canceled_at Canceled at.
	 * @return $this
	 */
	public function with_canceled_at( DateTimeImmutable $canceled_at = null ) {
		$this->canceled_at = $canceled_at;

		return $this;
	}

	/**
	 * With total periods.
	 *
	 * @param int|null $total_periods Number of periods to create.
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
	 * @param TaxedMoney $amount Amount.
	 * @return $this
	 */
	public function with_amount( TaxedMoney $amount ) {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * With interval.
	 *
	 * @param string $interval_spec Interval specification.
	 * @return $this
	 */
	public function with_interval( $interval_spec ) {
		$this->interval = new SubscriptionInterval( $interval_spec );

		return $this;
	}

	/**
	 * With alignment.
	 *
	 * @return $this
	 */
	public function with_alignment( $is_alignment = true ) {
		$this->is_alignment = $is_alignment;

		return $this;
	}

	/**
	 * With proration.
	 *
	 * @return $this
	 */
	public function with_proration( $is_prorated = true ) {
		$this->is_prorated = $is_prorated;

		return $this;
	}

	/**
	 * With trial.
	 *
	 * @return $this
	 */
	public function with_trial( $is_trial = true ) {
		$this->is_trial = $is_trial;

		return $this;
	}

	/**
	 * Create subscription phase.
	 *
	 * @return SubscriptionPhase
	 * @throws \InvalidArgumentException Throws exception if required arguments are not set.
	 */
	public function create() {
		if ( null === $this->start_date ) {
			throw new \InvalidArgumentException( 'Start date is required for subscription phase.' );
		}

		if ( null === $this->interval ) {
			throw new \InvalidArgumentException( 'Interval specification is required for subscription phase.' );
		}

		if ( null === $this->amount ) {
			throw new \InvalidArgumentException( 'Amount is required for subscription phase.' );
		}

		$phase = new SubscriptionPhase( $this->start_date, $this->interval, $this->amount );

		// Alignment.
		$phase->set_alignment( $this->is_alignment );

		// Prorated.
		$phase->set_prorated( $this->is_prorated );

		// Trial.
		$phase->set_trial( $this->is_trial );

		// Total periods.
		$phase->set_total_periods( $this->total_periods );

		// Periods created.
		if ( null !== $this->periods_created ) {
			$phase->set_periods_created( $this->periods_created );
		}

		$phase->set_canceled_at( $this->canceled_at );

		return $phase;
	}
}
