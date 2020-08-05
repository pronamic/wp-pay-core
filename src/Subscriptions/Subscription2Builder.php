<?php
/**
 * Subscription 2 Builder
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription 2 Builder
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class Subscription2Builder {
	/**
	 * Period definitions.
	 *
	 * @var array
	 */
	private $period_definitions;

	/**
	 * Construct subscription.
	 */
	public function __construct() {
		$this->period_definitions = array();
	}

	public static function new() {
		return new self();
	}

	public function with_period_definition( PeriodDefinition $period_definition ) {
		$this->period_definitions[] = $period_definition;

		return $this;
	}

	public function create() {
		$subscription = new Subscription2();

		$subscription->period_definitions = $this->period_definitions;

		return $subscription;
	}
}
