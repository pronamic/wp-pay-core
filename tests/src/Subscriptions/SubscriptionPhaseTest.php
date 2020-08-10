<?php
/**
 * Subscription Phase Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription Phase Test
 *
 * @author Remco Tolsma
 * @version unreleased
 */
class SubscriptionPhaseTest extends \WP_UnitTestCase {
	/**
	 * New period definition.
	 *
	 * @return SubscriptionPhase
	 */
	private function new_subscription_phase() {
		$subscription_phase = new SubscriptionPhase( new \DateTimeImmutable(), 'Y', 5, new Money( 50, 'EUR' ) );

		return $subscription_phase;
	}

	/**
	 * Test date interval.
	 */
	public function test_date_interval() {
		$subscription_phase = $this->new_subscription_phase();

		$date_interval = $subscription_phase->get_date_interval();

		$this->assertInstanceOf( \DateInterval::class, $date_interval );
		$this->assertEquals( 5, $date_interval->y );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertTrue( $subscription_phase->is_infinite() );

		$subscription_phase->set_number_recurrences( 5 );

		$this->assertFalse( $subscription_phase->is_infinite() );
	}

	/**
	 * Test completed.
	 */
	public function test_completed() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertFalse( $subscription_phase->is_completed() );

		$subscription_phase->set_status( 'completed' );

		$this->assertTrue( $subscription_phase->is_completed() );
	}

	/**
	 * Test trial.
	 */
	public function test_trial() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertFalse( $subscription_phase->is_trial() );

		$subscription_phase->set_type( 'trial' );

		$this->assertTrue( $subscription_phase->is_trial() );
	}

	/**
	 * Test sequence number.
	 */
	public function test_sequence_number() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertEquals( 1, $subscription_phase->get_sequence_number() );

		$subscription_phase->set_sequence_number( 3 );

		$this->assertEquals( 3, $subscription_phase->get_sequence_number() );
	}

	/**
	 * Test prorate.
	 */
	public function test_prorate() {
		/**
		 * To-do:
		 * - Prorating First Payment
		 * - Do not charge at sign-up
		 * - Charge full amount at sign-up
		 */
		$amount = new Money( 100, 'USD' );

		$prorating_rule = new ProratingRule( 'Y' );

		$prorating_rule->by_numeric_month( 1 );
		$prorating_rule->by_numeric_day_of_the_month( 1 );

		$start_date = new \DateTimeImmutable( '2020-07-01 00:00:00' );

		$align_date = $prorating_rule->get_date( $start_date );

		// Regular phase.
		$regular_phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $amount )
			->with_interval( 1, 'Y' )
			->create();

		// Proration phase.
		$proration_phase = SubscriptionPhase::prorate( $regular_phase, $align_date, true );

		// Asserts.
		$this->assertEquals( 50.41, round( $proration_phase->get_amount()->get_value(), 2 ) );
		$this->assertEquals( '2020-07-01 00:00:00', $proration_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2021-01-01 00:00:00', $proration_phase->get_end_date()->format( 'Y-m-d H:i:s' ) );
		
		$this->assertEquals( 100.00, round( $regular_phase->get_amount()->get_value(), 2 ) );
		$this->assertEquals( '2021-01-01 00:00:00', $regular_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}
}
