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
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionPhaseTest extends \WP_UnitTestCase {
	/**
	 * New period definition.
	 *
	 * @return SubscriptionPhase
	 */
	private function new_subscription_phase() {
		$subscription = new Subscription();

		$subscription_phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable(),
			new SubscriptionInterval( 'P5Y' ),
			new TaxedMoney( 50, 'EUR' )
		);

		return $subscription_phase;
	}

	/**
	 * Test date interval.
	 */
	public function test_date_interval() {
		$phase = $this->new_subscription_phase();

		$interval = $phase->get_interval();

		$this->assertInstanceOf( \DateInterval::class, $interval );
		$this->assertEquals( 5, $interval->y );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$phase = $this->new_subscription_phase();

		$this->assertTrue( $phase->is_infinite() );

		$phase->set_total_periods( 5 );

		$this->assertFalse( $phase->is_infinite() );
	}

	/**
	 * Test trial.
	 */
	public function test_trial() {
		$phase = $this->new_subscription_phase();

		$this->assertFalse( $phase->is_trial() );

		$phase->set_trial( true );

		$this->assertTrue( $phase->is_trial() );
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

		$alignment_rule = new AlignmentRule( 'Y' );

		$alignment_rule->by_numeric_month( 1 );
		$alignment_rule->by_numeric_day_of_the_month( 1 );

		$start_date = new \DateTimeImmutable( '2020-07-01 00:00:00' );

		$align_date = $alignment_rule->get_date( $start_date );

		// Regular phase.
		$subscription = new Subscription();

		$regular_phase = new SubscriptionPhase(
			$subscription,
			$start_date,
			new SubscriptionInterval( 'P1Y' ),
			$amount
		);

		// Alignment phase.
		$alignment_phase = SubscriptionPhase::align( $regular_phase, $align_date );

		$alignment_rate = $alignment_phase->get_alignment_rate();

		// Asserts.
		$this->assertEquals( 50.41, round( $alignment_phase->get_amount()->multiply( $alignment_rate )->get_value(), 2 ) );
		$this->assertEquals( '2020-07-01 00:00:00', $alignment_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2021-01-01 00:00:00', $alignment_phase->get_end_date()->format( 'Y-m-d H:i:s' ) );

		$this->assertEquals( 100.00, round( $regular_phase->get_amount()->get_value(), 2 ) );
		$this->assertEquals( '2021-01-01 00:00:00', $regular_phase->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test month overflow.
	 */
	public function test_month_overflow() {
		$amount = new TaxedMoney( 100, 'USD' );

		$start_date = new \DateTimeImmutable( '2020-01-31 00:00:00' );

		$subscription = new Subscription();

		$phase = new SubscriptionPhase(
			$subscription,
			$start_date,
			new SubscriptionInterval( 'P1M' ),
			$amount
		);

		$subscription->add_phase( $phase );

		$period_1 = $phase->next_period( $subscription );
		$period_2 = $phase->next_period( $subscription );
		$period_3 = $phase->next_period( $subscription );

		$this->assertEquals( '2020-01-31 00:00:00', $period_1->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-03-02 00:00:00', $period_2->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-03-31 00:00:00', $period_3->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test month overflow.
	 */
	public function test_month_overflow_29() {
		$amount = new TaxedMoney( 100, 'USD' );

		$start_date = new \DateTimeImmutable( '2020-01-29 00:00:00' );

		$subscription = new Subscription();

		$phase = new SubscriptionPhase(
			$subscription,
			$start_date,
			new SubscriptionInterval( 'P1M' ),
			$amount
		);

		$subscription->add_phase( $phase );

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

		$subscription = new Subscription();

		$phase = new SubscriptionPhase(
			$subscription,
			$start_date,
			new SubscriptionInterval( 'P1W' ),
			$amount
		);

		$subscription->add_phase( $phase );

		$period_1 = $phase->next_period( $subscription );
		$period_2 = $phase->next_period( $subscription );
		$period_3 = $phase->next_period( $subscription );

		$this->assertEquals( '2020-01-29 00:00:00', $period_1->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-05 00:00:00', $period_2->get_start_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-02-12 00:00:00', $period_3->get_start_date()->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test alignment first payment.
	 *
	 * For example, for a $100 per year subscription that
	 * is synchronized to the 1st of January each year,
	 * if a customer signs up on the 1st July, they will
	 * be charged $50.41 at the time of signup (or $50.27
	 * in a leap year). This is because there are 184 days
	 * between 1st July and 1st January, and the per day
	 * rate for a $100 per year subscription is $0.27397.
	 * If the customer was to sign up on the the 15th of
	 * November, they would only pay $12.87 because they
	 * would be paying for only 47 days left in the current
	 * year.
	 *
	 * @link https://docs.woocommerce.com/document/subscriptions/renewal-synchronisation/
	 * @link https://github.com/wp-pay-extensions/gravityforms/blob/2.4.1/src/PaymentData.php#L269-L337
	 * @link https://knowledgecenter.zuora.com/Billing/Subscriptions/Subscriptions/G_Proration
	 * @link https://stripe.com/docs/billing/subscriptions/prorations
	 */
	public function test_prorating_first_payment() {
		/**
		 * To-do:
		 * - Prorating First Payment
		 * - Do not charge at sign-up
		 * - Charge full amount at sign-up
		 */
		$amount = new TaxedMoney( 100, 'USD' );

		$alignment_rule = new AlignmentRule( 'Y' );

		$alignment_rule->by_numeric_month( 1 );
		$alignment_rule->by_numeric_day_of_the_month( 1 );

		$start_date = new \DateTimeImmutable( '2020-07-01 00:00:00' );

		$alignment_date = $alignment_rule->get_date( $start_date );

		/**
		 * Subscription.
		 */
		$subscription = new Subscription();

		/**
		 * Regular phase.
		 */
		$regular_phase = new SubscriptionPhase(
			$subscription,
			$start_date,
			new SubscriptionInterval( 'P1Y' ),
			$amount
		);

		$alignment_phase = SubscriptionPhase::align( $regular_phase, $alignment_date );

		$prorate_amount = $amount->multiply( $alignment_phase->get_alignment_rate() );

		$alignment_interval = $alignment_phase->get_interval();

		$this->assertEquals( 'P184D', \strval( $alignment_interval ) );
		$this->assertEquals( '2021-01-01', $alignment_phase->get_end_date()->format( 'Y-m-d' ) );

		/**
		 * For example, for a $100 per year subscription that is synchronized
		 * to the 1st of January each year, if a customer signs up on the 1st
		 * July, they will be charged $50.41 at the time of signup (or $50.27
		 * in a leap year).
		 */
		$this->assertEquals( 50.41, round( $prorate_amount->get_value(), 2 ) );
	}
}
