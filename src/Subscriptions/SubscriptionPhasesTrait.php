<?php
/**
 * Subscription Phases Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateTime;
use Pronamic\WordPress\Money\Money;

/**
 * Subscription Phases Trait
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
trait SubscriptionPhasesTrait {
	/**
	 * Phases.
	 *
	 * @var SubscriptionPhase[]
	 */
	private $phases = array();

	/**
	 * Get phases.
	 *
	 * @return array<int, SubscriptionPhase>
	 */
	public function get_phases() {
		return $this->phases;
	}

	/**
	 * Add the specified phase to this subscription.
	 *
	 * @param SubscriptionPhase $phase Phase.
	 * @return void
	 */
	public function add_phase( SubscriptionPhase $phase ) {
		$this->phases[] = $phase;
	}

	/**
	 * Create new phase for this subscription.
	 *
	 * @param DateTime $start_date     Start date.
	 * @param string   $interval_spec  Interval specification.
	 * @param Money    $amount         Amount.
	 * @return SubscriptionPhase
	 */
	public function new_phase( $start_date, $interval_spec, $amount ) {
		$phase = new SubscriptionPhase( $start_date, $interval_spec, $amount );

		$sequence_number = \count( $this->phases ) + 1;

		$phase->set_sequence_number( $sequence_number );

		$this->phases[] = $phase;

		return $phase;
	}

	/**
	 * Check if all the periods within the subscription phases are created.
	 *
	 * @return bool True if all created, false otherwise.
	 */
	public function all_periods_created() {
		foreach ( $this->phases as $phase ) {
			if ( ! $phase->all_periods_created() ) {
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
		foreach ( $this->phases as $phase ) {
			if ( $phase->is_infinite() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get current phase or null if all completed.
	 *
	 * @return SubscriptionPhase|null
	 */
	public function get_current_phase() {
		foreach ( $this->phases as $phase ) {
			if ( ! $phase->all_periods_created() ) {
				return $phase;
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
		$current_phase = $this->get_current_phase();

		if ( null === $current_phase ) {
			return false;
		}

		return $current_phase->is_trial();
	}

	/**
	 * Get the next period.
	 *
	 * @return Period|null
	 */
	public function get_next_period() {
		$current_phase = $this->get_current_phase();

		if ( null === $current_phase ) {
			return null;
		}

		return $current_phase->get_next_period();
	}

	/**
	 * Next period.
	 *
	 * @return Period|null
	 */
	public function next_period() {
		$current_phase = $this->get_current_phase();

		if ( null === $current_phase ) {
			return null;
		}

		return $current_phase->next_period();
	}
}
