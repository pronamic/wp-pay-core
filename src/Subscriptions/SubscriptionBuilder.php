<?php
/**
 * Subscription Builder
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscription Builder
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionBuilder {
	/**
	 * Phases.
	 *
	 * @var array
	 */
	private $phases;

	/**
	 * Construct subscription.
	 */
	public function __construct() {
		$this->phases = array();
	}

	/**
	 * With phase.
	 *
	 * @param SubscriptionPhase $phase Subscription phase.
	 * @return $this
	 */
	public function with_phase( SubscriptionPhase $phase ) {
		$this->phases[] = $phase;

		return $this;
	}

	/**
	 * Create subscription.
	 *
	 * @return Subscription
	 * @throws \Exception Throws exception on date error.
	 */
	public function create() {
		$subscription = new Subscription();

		foreach ( $this->phases as $phase ) {
			$subscription->add_phase( $phase );
		}

		return $subscription;
	}
}
