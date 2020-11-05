<?php
/**
 * Subscription Helper Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\TaxedMoney;

/**
 * Subscription Helper Test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionHelperTest extends \WP_UnitTestCase {
	/**
	 * Test calculate end date no start date.
	 */
	public function test_calculate_end_date_no_start_date() {
		$subscription = new Subscription();

		$end_date = SubscriptionHelper::calculate_end_date( $subscription );

		$this->assertNull( $end_date );
	}

	/**
	 * Test calculate end date no interval.
	 */
	public function test_calculate_end_date_no_interval() {
		$subscription = new Subscription();

		$subscription->set_start_date( new DateTime( '2005-05-05 00:00:00' ) );

		$end_date = SubscriptionHelper::calculate_end_date( $subscription );

		$this->assertNull( $end_date );
	}

	/**
	 * Test calculate end date.
	 */
	public function test_calculate_end_date() {
		$subscription = new Subscription();

		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( '2005-05-05 00:00:00' ),
			new SubscriptionInterval( 'P1M' ),
			new TaxedMoney( 5, 'EUR' )
		);

		$phase->set_total_periods( 12 );

		$subscription->add_phase( $phase );

		// Calculate.
		$end_date = SubscriptionHelper::calculate_end_date( $subscription );

		$this->assertInstanceOf( \DateTimeInterface::class, $end_date );
		$this->assertEquals( '2006-05-05 00:00:00', $end_date->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test calculate expirty date no start date.
	 */
	public function test_calculate_expiry_date_no_start_date() {
		$subscription = new Subscription();

		// Calculate.
		$expiry_date = SubscriptionHelper::calculate_expiry_date( $subscription );

		$this->assertNull( $expiry_date );
	}

	/**
	 * Test calculate expiry date.
	 */
	public function test_calculate_expiry_date() {
		$subscription = new Subscription();

		$subscription->set_start_date( new \DateTime( '2005-05-05 00:00:00' ) );

		// Calculate.
		$expiry_date = SubscriptionHelper::calculate_expiry_date( $subscription );

		$this->assertInstanceOf( \DateTimeInterface::class, $expiry_date );
		$this->assertEquals( '2005-05-05 00:00:00', $expiry_date->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test calculate next payment date no start date.
	 */
	public function test_calculate_next_payment_date_no_start_date() {
		$subscription = new Subscription();

		// Calculate.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Can not calculate next payment date of subscription without phases.' );

		$next_payment_date = SubscriptionHelper::calculate_next_payment_date( $subscription );
	}

	/**
	 * Test calculate next payment date no interval.
	 */
	public function test_calculate_next_payment_date_no_interval() {
		$subscription = new Subscription();

		$subscription->set_start_date( new \DateTime( '2005-05-05 00:00:00' ) );

		// Calculate.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Can not calculate next payment date of subscription without phases.' );

		$next_payment_date = SubscriptionHelper::calculate_next_payment_date( $subscription );
	}

	/**
	 * Test calculate next payment.
	 *
	 * @dataProvider subscription_interval_provider
	 * @param string $start_date    Start date.
	 * @param string $interval_spec Interval specification.
	 * @param int    $recurrences   Recurrences.
	 * @param string $expected_date Expected date.
	 */
	public function test_calculate_next_payment_date( $start_date, $interval_spec, $recurrences, $expected_date ) {
		$subscription = new Subscription();

		// Start Date.
		$subscription->set_start_date( new \DateTime( $start_date ) );

		// Recurrences.
		$subscription->frequency = $recurrences;

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( $start_date ),
			new SubscriptionInterval( $interval_spec ),
			new TaxedMoney( 100, 'USD' )
		);

		$phase->set_total_periods( $recurrences );

		$subscription->add_phase( $phase );

		$phase->next_period( $subscription );

		// Calculate.
		$next_payment_date = SubscriptionHelper::calculate_next_payment_date( $subscription );

		if ( null !== $next_payment_date ) {
			$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_date );

			$next_payment_date = $next_payment_date->format( 'Y-m-d' );
		}

		$this->assertEquals( $expected_date, $next_payment_date );
	}

	/**
	 * Subscription interval provider.
	 *
	 * @return array
	 */
	public function subscription_interval_provider() {
		return array(
			array( '2005-05-05', 'P1W', 1, null ),
			array( '2005-05-05', 'P3W', 2, '2005-05-26' ),
			array( '2005-05-05', 'P1M', 3, '2005-06-05' ),
			array( '2005-05-05', 'P1M', 6, '2005-06-05' ),
			array( '2005-05-05', 'P1Y', 1, null ),
			array( '2005-05-05', 'P1Y', 4, '2006-05-05' ),
		);
	}

	/**
	 * Test calculate next payment delivery date empty.
	 */
	public function test_calculate_next_payment_delivery_date_empty() {
		$subscription = new Subscription();

		// Calculate.
		$next_payment_delivery_date = SubscriptionHelper::calculate_next_payment_delivery_date( $subscription );

		$this->assertNull( $next_payment_delivery_date );
	}

	/**
	 * Test calculate next payment delivery date.
	 */
	public function test_calculate_next_payment_delivery_date() {
		$subscription = new Subscription();

		// Next Payment Date.
		$subscription->set_next_payment_date( new \Pronamic\WordPress\DateTime\DateTime( '2005-05-05' ) );

		// Calculate.
		$next_payment_delivery_date = SubscriptionHelper::calculate_next_payment_delivery_date( $subscription );

		$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_delivery_date );
		$this->assertEquals( '2005-05-05', $next_payment_delivery_date->format( 'Y-m-d' ) );
	}

	/**
	 * Test calculate next payment delivery date filter
	 */
	public function test_calculate_next_payment_delivery_date_filter() {
		$subscription = new Subscription();

		// Next Payment Date.
		$subscription->set_next_payment_date( new \Pronamic\WordPress\DateTime\DateTime( '2005-05-05' ) );

		// Filter.
		\add_filter(
			'pronamic_pay_subscription_next_payment_delivery_date',
			function( $next_payment_delivery_date, $subscription ) {
				return new \Pronamic\WordPress\DateTime\DateTime( '1970-01-01' );
			},
			10,
			2
		);

		// Calculate.
		$next_payment_delivery_date = SubscriptionHelper::calculate_next_payment_delivery_date( $subscription );

		$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_delivery_date );
		$this->assertEquals( '1970-01-01', $next_payment_delivery_date->format( 'Y-m-d' ) );
	}
}
