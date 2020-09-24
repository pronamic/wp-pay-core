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

use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\PaymentLines;

/**
 * Subscription Builder
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionBuilder {
	/**
	 * Customer.
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Payment Lines.
	 *
	 * @var PaymentLines|null
	 */
	private $lines;

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
	 * With customer.
	 *
	 * @param Customer $customer Customer.
	 * @return $this
	 */
	public function with_customer( Customer $customer ) {
		$this->customer = $customer;

		return $this;
	}

	/**
	 * With lines.
	 *
	 * @param PaymentLines $lines Payment lines.
	 * @return $this
	 */
	public function with_lines( PaymentLines $lines ) {
		$this->lines = $lines;

		return $this;
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

		// Customer.
		$subscription->set_customer( $this->customer );

		// Lines.
		$subscription->set_lines( $this->lines );

		// Phases.
		foreach ( $this->phases as $phase ) {
			$subscription->add_phase( $phase );
		}

		return $subscription;
	}
}
