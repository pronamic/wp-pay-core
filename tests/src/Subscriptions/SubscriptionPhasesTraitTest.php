<?php
/**
 * Subscription Phases Trait Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;

/**
 * Subscription Phases Trait Test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionPhasesTraitTest extends \WP_UnitTestCase {
	/**
	 * Create new subscription.
	 *
	 * @return Subscription
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	private function new_subscription() {
		$subscription = new Subscription();

		return $subscription;
	}

	/**
	 * New phase for subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return SubscriptionPhase
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	private function new_phase( $subscription ) {
		$phase = $subscription->new_phase( new \DateTimeImmutable(), 'P1W', new TaxedMoney( 50, 'EUR' ) );

		return $phase;
	}

	/**
	 * Test new period definition.
	 */
	public function test_new_period_definition() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );

		$this->assertInstanceOf( SubscriptionPhase::class, $phase_1 );

		$phase_2 = $this->new_phase( $subscription );

		$this->assertInstanceOf( SubscriptionPhase::class, $phase_2 );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );
		$phase_2 = $this->new_phase( $subscription );

		$this->assertTrue( $subscription->is_infinite() );
	}

	/**
	 * Test current period definition.
	 */
	public function test_current_period_definition() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );

		$phase_1->set_total_periods( 1 );
		$phase_1->set_periods_created( 1 );

		$phase_2 = $this->new_phase( $subscription );

		$phase_3 = $this->new_phase( $subscription );

		$current_phase = $subscription->get_current_phase();

		$this->assertEquals( $phase_2, $current_phase );
	}

	/**
	 * Test in trial period.
	 */
	public function test_in_trial_period() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );
		$phase_2 = $this->new_phase( $subscription );
		$phase_3 = $this->new_phase( $subscription );

		$current_phase = $subscription->get_current_phase();

		$this->assertFalse( $subscription->in_trial_period() );

		$phase_1->set_trial( true );

		$this->assertTrue( $subscription->in_trial_period() );

		$phase_1->set_total_periods( 1 );
		$phase_1->set_periods_created( 1 );

		$this->assertFalse( $subscription->in_trial_period() );
	}
}
