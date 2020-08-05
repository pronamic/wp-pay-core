<?php
/**
 * Period
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Period
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class Period {
	/**
	 * Subscription.
	 *
	 * @var Subscription
	 */
	private $subscription;

	/**
	 * Period definition.
	 *
	 * @var PeriodDefinition
	 */
	private $period_definition;

	/**
	 * Start date.
	 *
	 * @var \DateTimeImmutable
	 */
	private $start_date;

	/**
	 * End date.
	 *
	 * @var \DateTimeImmutable
	 */
	private $end_date;

	/**
	 * Amount.
	 *
	 * @var Money
	 */
	private $amount;

	/**
	 * Construct period.
	 */
	public function __construct( $subscription, $period_definition, $start_date, $end_date, $amount ) {
		$this->subscription      = $subscription;
		$this->period_definition = $period_definition;
		$this->start_date        = clone $start_date;
		$this->end_date          = clone $end_date;
		$this->amount            = $amount;
	}

	public function get_start_date() {
		return $this->start_date;
	}

	public function get_end_date() {
		return $this->end_date;
	}

	public function is_trial() {
		return $this->period_definition->is_trial();
	}
}
