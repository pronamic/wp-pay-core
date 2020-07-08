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

/**
 * Subscription Helper Test
 *
 * @author Remco Tolsma
 * @version 2.4.0
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

		$subscription->set_start_date( new \DateTime( '2005-05-05 00:00:00' ) );

		$end_date = SubscriptionHelper::calculate_end_date( $subscription );

		$this->assertNull( $end_date );
	}

	/**
	 * Test calculate end date.
	 */
	public function test_calculate_end_date() {
		$subscription = new Subscription();

		// Start Date.
		$subscription->set_start_date( new \DateTime( '2005-05-05 00:00:00' ) );

		// Date Interval.
		$subscription->interval        = 1;
		$subscription->interval_period = 'M';

		// Recurrences.
		$subscription->frequency = 12;

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
	 * Test calculate expirty date.
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
		$this->expectExceptionMessage( 'Can not calculate next payment date of subscription without start date.' );

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
		$this->expectExceptionMessage( 'Can not calculate next payment date of subscription without date interval.' );

		$next_payment_date = SubscriptionHelper::calculate_next_payment_date( $subscription );
	}

	/**
	 * Test calculate next payment.
	 *
	 * @dataProvider subscription_interval_provider
	 * @param string $start_date      Start date.
	 * @param int    $interval        Interval.
	 * @param string $interval_period Interval period.
	 * @param int    $recurrences     Recurrences.
	 * @param string $expected_date   Expected date.
	 */
	public function test_calculate_next_payment_date( $start_date, $interval, $interval_period, $recurrences, $expected_date ) {
		$subscription = new Subscription();

		// Start Date.
		$subscription->set_start_date( new \DateTime( $start_date ) );

		// Date Interval.
		$subscription->interval        = $interval;
		$subscription->interval_period = $interval_period;

		// Recurrences.
		$subscription->frequency = $recurrences;

		// Calculate.
		$next_payment_date = SubscriptionHelper::calculate_next_payment_date( $subscription );

		$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_date );
		$this->assertEquals( $expected_date, $next_payment_date->format( 'Y-m-d' ) );
	}

	/**
	 * Subscription interval provider.
	 *
	 * @return array
	 */
	public function subscription_interval_provider() {
		return array(
			array( '2005-05-05', 1, 'W', 1, '2005-05-12' ),
			array( '2005-05-05', 3, 'W', 2, '2005-05-26' ),
			array( '2005-05-05', 1, 'M', 3, '2005-06-05' ),
			array( '2005-05-05', 1, 'M', 6, '2005-06-05' ),
			array( '2005-05-05', 1, 'Y', 1, '2006-05-05' ),
			array( '2005-05-05', 1, 'Y', 4, '2006-05-05' ),
		);
	}

	/**
	 * Test calculate next payment delivery date empty.
	 */
	public function test_calculate_next_payment_delivery_date_empty() {
		$subscription = new Subscription();

		// Calculate.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Can not calculate next payment delivery date of subscription without next payment date.' );

		$next_payment_delivery_date = SubscriptionHelper::calculate_next_payment_delivery_date( $subscription );
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
