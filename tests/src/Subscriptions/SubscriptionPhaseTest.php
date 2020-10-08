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

use Pronamic\WordPress\Money\TaxedMoney;

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
		$subscription_phase = new SubscriptionPhase( new \DateTimeImmutable(), new SubscriptionInterval( 'P5Y' ), new TaxedMoney( 50, 'EUR' ) );

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

		$subscription_phase->set_total_periods( 5 );

		$this->assertFalse( $subscription_phase->is_infinite() );
	}

	/**
	 * Test trial.
	 */
	public function test_trial() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertFalse( $subscription_phase->is_trial() );

		$subscription_phase->set_trial( true );

		$this->assertTrue( $subscription_phase->is_trial() );
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
		$amount = new TaxedMoney( 100, 'USD' );

		$prorating_rule = new ProratingRule( 'Y' );

		$prorating_rule->by_numeric_month( 1 );
		$prorating_rule->by_numeric_day_of_the_month( 1 );

		$start_date = new \DateTimeImmutable( '2020-07-01 00:00:00' );

		$align_date = $prorating_rule->get_date( $start_date );

		// Regular phase.
		$regular_phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $amount )
			->with_interval( 'P1Y' )
			->create();

		// Proration phase.
		$proration_phase = SubscriptionPhase::align( $regular_phase, $align_date, true );

		// Asserts.
		$this->assertEquals( 50.41, round( $proration_phase->get_amount()->get_value(), 2 ) );
		$this->assertEquals( '2020-07-01 00:00:00', $proration_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2021-01-01 00:00:00', $proration_phase->get_end_date()->format( 'Y-m-d H:i:s' ) );

		$this->assertEquals( 100.00, round( $regular_phase->get_amount()->get_value(), 2 ) );
		$this->assertEquals( '2021-01-01 00:00:00', $regular_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test month overflow.
	 */
	public function test_month_overflow() {
		$amount = new TaxedMoney( 100, 'USD' );

		$start_date = new \DateTimeImmutable( '2020-01-31 00:00:00' );

		$phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $amount )
			->with_interval( 'P1M' )
			->create();

		$subscription = ( new SubscriptionBuilder() )
			->with_phase( $phase )
			->create();

		$period_1 = $phase->next_period( $subscription );
		$period_2 = $phase->next_period( $subscription );
		$period_3 = $phase->next_period( $subscription );

		$this->assertEquals( '2020-01-31 00:00:00', $period_1->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-29 00:00:00', $period_2->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-03-31 00:00:00', $period_3->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test month overflow.
	 */
	public function test_month_overflow_29() {
		$amount = new TaxedMoney( 100, 'USD' );

		$start_date = new \DateTimeImmutable( '2020-01-29 00:00:00' );

		$phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $amount )
			->with_interval( 'P1M' )
			->create();

		$subscription = ( new SubscriptionBuilder() )
			->with_phase( $phase )
			->create();

		$period_1 = $phase->next_period( $subscription );
		$period_2 = $phase->next_period( $subscription );
		$period_3 = $phase->next_period( $subscription );

		$this->assertEquals( '2020-01-29 00:00:00', $period_1->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-29 00:00:00', $period_2->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-03-29 00:00:00', $period_3->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test month overflow.
	 */
	public function test_month_overflow_weekly() {
		$amount = new TaxedMoney( 100, 'USD' );

		$start_date = new \DateTimeImmutable( '2020-01-29 00:00:00' );

		$phase = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $amount )
			->with_interval( 'P1W' )
			->create();

		$subscription = ( new SubscriptionBuilder() )
			->with_phase( $phase )
			->create();

		$period_1 = $phase->next_period( $subscription );
		$period_2 = $phase->next_period( $subscription );
		$period_3 = $phase->next_period( $subscription );

		$this->assertEquals( '2020-01-29 00:00:00', $period_1->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-05 00:00:00', $period_2->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-12 00:00:00', $period_3->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}
}
