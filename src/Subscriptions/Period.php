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

use DateTimeImmutable;
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
	 * Phase.
	 *
	 * @var SubscriptionPhase
	 */
	private $phase;

	/**
	 * Start date.
	 *
	 * @var DateTimeImmutable
	 */
	private $start_date;

	/**
	 * End date.
	 *
	 * @var DateTimeImmutable
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
	 *
	 * @param Subscription      $subscription Subscription.
	 * @param SubscriptionPhase $phase        Subscription phase.
	 * @param DateTimeImmutable $start_date   Start date.
	 * @param DateTimeImmutable $end_date     End date.
	 * @param Money             $amount       Amount.
	 * @return void
	 */
	public function __construct( $subscription, $phase, $start_date, $end_date, $amount ) {
		$this->subscription = $subscription;
		$this->phase        = $phase;
		$this->start_date   = clone $start_date;
		$this->end_date     = clone $end_date;
		$this->amount       = $amount;
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
	 * Get end date.
	 *
	 * @return DateTimeImmutable
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Is trial period?
	 *
	 * @return bool
	 */
	public function is_trial() {
		return $this->phase->is_trial();
	}
}
