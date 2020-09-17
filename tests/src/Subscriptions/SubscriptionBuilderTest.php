<?php
/**
 * Subscription Builder Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription Builder Test
 *
 * @author Remco Tolsma
 * @version unreleased
 */
class SubscriptionBuilderTest extends \WP_UnitTestCase {
	/**
	 * Test builder.
	 */
	public function test_builder() {
		$trial = ( new SubscriptionPhaseBuilder() )
			->with_start_date( new \DateTimeImmutable( '2020-05-05 00:00:00' ) )
			->with_type( 'trial' )
			->with_total_periods( 1 )
			->with_amount( new Money( 50, 'EUR' ) )
			->with_interval( 'P1M' )
			->create();

		$regular = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $trial->get_end_date() )
			->with_amount( new Money( 100, 'EUR' ) )
			->with_interval( 'P1Y' )
			->create();

		$subscription = ( new SubscriptionBuilder() )
			->with_phase( $trial )
			->with_phase( $regular )
			->create();

		$current_phase = $subscription->get_current_phase();

		$this->assertInstanceOf( SubscriptionPhase::class, $current_phase );
		$this->assertTrue( $current_phase->is_trial() );
		$this->assertTrue( $subscription->in_trial_period() );

		$period = $subscription->next_period();

		$this->assertInstanceOf( Period::class, $period );
		$this->assertTrue( $period->is_trial() );
		$this->assertEquals( new \DateTimeImmutable( '2020-05-05 00:00:00' ), $period->get_start_date() );
		$this->assertEquals( new \DateTimeImmutable( '2020-06-05 00:00:00' ), $period->get_end_date() );

		$period = $subscription->next_period();

		$this->assertInstanceOf( Period::class, $period );
		$this->assertFalse( $period->is_trial() );
		$this->assertEquals( new \DateTimeImmutable( '2020-06-05 00:00:00' ), $period->get_start_date() );
		$this->assertEquals( new \DateTimeImmutable( '2021-06-05 00:00:00' ), $period->get_end_date() );

		$period = $subscription->next_period();

		$this->assertInstanceOf( Period::class, $period );
		$this->assertFalse( $period->is_trial() );
		$this->assertEquals( new \DateTimeImmutable( '2021-06-05 00:00:00' ), $period->get_start_date() );
		$this->assertEquals( new \DateTimeImmutable( '2022-06-05 00:00:00' ), $period->get_end_date() );
	}

	/**
	 * Test prorating first payment.
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
		$amount = new Money( 100, 'USD' );

		$prorating_rule = new ProratingRule( 'Y' );

		$prorating_rule->by_numeric_month( 1 );
		$prorating_rule->by_numeric_day_of_the_month( 1 );

		$start_date = new \DateTimeImmutable( '2020-07-01 00:00:00' );

		$align_date = $prorating_rule->get_date( $start_date );

		// `z` » The day of the year (starting from 0).
		$days_in_year = 1 + $start_date->modify( 'last day of december this year' )->format( 'z' );

		$date_interval = $start_date->diff( $align_date, true );

		$this->assertEquals( 184, $date_interval->days );

		// 2020 is a leap year, so there are 366 days in the year.
		$this->assertEquals( 366, $days_in_year );

		// Prorate amount.
		$prorate_amount = $amount->divide( $days_in_year )->multiply( $date_interval->days );

		$this->assertEquals( 50.27, round( $prorate_amount->get_value(), 2 ) );

		// Phase alignment.
		$phase_alignment = ( new SubscriptionPhaseBuilder() )
			->with_start_date( $start_date )
			->with_amount( $prorate_amount )
			->with_interval( 'P' . $date_interval->days . 'D' )
			->with_total_periods( 1 )
			->with_proration()
			->create();

		$this->assertEquals( '2021-01-01', $phase_alignment->get_end_date()->format( 'Y-m-d' ) );
	}
}
