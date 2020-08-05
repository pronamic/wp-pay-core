<?php
/**
 * Subscription 2
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription 2
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class Subscription2 {
	/**
	 * Period definitions.
	 *
	 * @var array
	 */
	public $period_definitions;

	/**
	 * Construct subscription.
	 */
	public function __construct() {
		$this->period_definitions = array();
	}

	/**
	 * Create new period definition for this subscription.
	 */
	public function new_period_definition( $start_date, $interval_unit, $interval_value, $amount ) {
		$period_definition = new PeriodDefinition( $start_date, $interval_unit, $interval_value, $amount );

		$sequence_number = \count( $this->period_definitions ) + 1;

		$period_definition->set_sequence_number( $sequence_number );

		$this->period_definitions[] = $period_definition;

		return $period_definition;
	}

	/**
	 * Check if this subscription is completed.
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_completed() {
		foreach ( $this->period_definitions as $period_definition ) {
			if ( ! $period_definition->is_completed() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if this subscription is infinite.
	 *
	 * @return bool True if infinite, false otherwise.
	 */
	public function is_infinite() {
		foreach ( $this->period_definitions as $period_definition ) {
			if ( $period_definition->is_infinite() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get current period definition or null if all completed.
	 *
	 * @return PeriodDefinition|null
	 */
	public function get_current_period_definition() {
		foreach ( $this->period_definitions as $period_definition ) {
			if ( ! $period_definition->is_completed() ) {
				return $period_definition;
			}
		}

		return null;
	}

	/**
	 * Check if subscription is in a trial period.
	 *
	 * @return bool True if current period definition is a trial, false otherwise.
	 */
	public function in_trial_period() {
		$current_period_definition = $this->get_current_period_definition();

		if ( null === $current_period_definition ) {
			return false;
		}

		return $current_period_definition->is_trial();
	}

	public function next_period() {
		$current_period_definition = $this->get_current_period_definition();

		if ( null === $current_period_definition ) {
			return null;
		}

		return $current_period_definition->next_period();
	}
}
