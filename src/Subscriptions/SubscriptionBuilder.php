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

use Pronamic\WordPress\Money\Money;

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

	public function with_phase( SubscriptionPhase $phase ) {
		$this->phases[] = $phase;

		return $this;
	}

	public function create() {
		$subscription = new Subscription();

		$subscription->phases = $this->phases;

		return $subscription;
	}
}
