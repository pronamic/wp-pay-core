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
	 * Type.
	 *
	 * @var string|null
	 */
	private $type;

	/**
	 * Number of recurrences.
	 *
	 * @var int|null
	 */
	private $number_recurrences;

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
	 * With number of recurrences.
	 *
	 * @param int $number_recurrences Number of recurrences.
	 * @return $this
	 */
	public function with_number_recurrences( $number_recurrences ) {
		$this->number_recurrences = $number_recurrences;

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
		$period_definition = new SubscriptionPhase( $this->start_date, $this->interval_unit, $this->interval_value, $this->amount );

		$period_definition->set_type( $this->type );
		$period_definition->set_number_recurrences( $this->number_recurrences );
		$period_definition->set_proration( $this->proration );

		return $period_definition;
	}
}
