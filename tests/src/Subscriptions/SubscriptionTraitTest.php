<?php
/**
 * Subscription 2 Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription 2 Test
 *
 * @author Remco Tolsma
 * @version unreleased
 */
class SubscriptionTraitTest extends \WP_UnitTestCase {
	/**
	 * Create new subscription.
	 *
	 * @return Subscription
	 */
	private function new_subscription() {
		$subscription = new Subscription();

		return $subscription;
	}

	/**
	 * New period definition for subscription.
	 *
	 * @return PeriodDefinition
	 */
	private function new_period_definition( $subscription ) {
		$period_definition = $subscription->new_period_definition( new \DateTimeImmutable(), 'W', 1, new Money( 50, 'EUR' ) );

		return $period_definition;
	}

	/**
	 * Test new period definition.
	 */
	public function test_new_period_definition() {
		$subscription = $this->new_subscription();

		$period_definition_1 = $this->new_period_definition( $subscription );

		$this->assertInstanceOf( PeriodDefinition::class, $period_definition_1 );
		$this->assertEquals( 1, $period_definition_1->get_sequence_number() );

		$period_definition_2 = $this->new_period_definition( $subscription );

		$this->assertInstanceOf( PeriodDefinition::class, $period_definition_2 );
		$this->assertEquals( 2, $period_definition_2->get_sequence_number() );
	}

	/**
	 * Test completed.
	 */
	public function test_completed() {
		$subscription = $this->new_subscription();

		$period_definition_1 = $this->new_period_definition( $subscription );
		$period_definition_1->set_status( 'completed' );

		$period_definition_2 = $this->new_period_definition( $subscription );
		$period_definition_2->set_status( 'completed' );

		$this->assertTrue( $subscription->is_completed() );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$subscription = $this->new_subscription();

		$period_definition_1 = $this->new_period_definition( $subscription );
		$period_definition_2 = $this->new_period_definition( $subscription );

		$this->assertTrue( $subscription->is_infinite() );
	}

	/**
	 * Test current period definition.
	 */
	public function test_current_period_definition() {
		$subscription = $this->new_subscription();

		$period_definition_1 = $this->new_period_definition( $subscription );
		$period_definition_1->set_status( 'completed' );

		$period_definition_2 = $this->new_period_definition( $subscription );

		$period_definition_3 = $this->new_period_definition( $subscription );

		$current_period_definition = $subscription->get_current_period_definition();

		$this->assertEquals( $period_definition_2, $current_period_definition );
	}

	/**
	 * Test in trial period.
	 */
	public function test_in_trial_period() {
		$subscription = $this->new_subscription();

		$period_definition_1 = $this->new_period_definition( $subscription );
		$period_definition_2 = $this->new_period_definition( $subscription );
		$period_definition_3 = $this->new_period_definition( $subscription );

		$current_period_definition = $subscription->get_current_period_definition();

		$this->assertFalse( $subscription->in_trial_period() );

		$period_definition_1->set_type( 'trial' );

		$this->assertTrue( $subscription->in_trial_period() );

		$period_definition_1->set_status( 'completed' );

		$this->assertFalse( $subscription->in_trial_period() );
	}
}
