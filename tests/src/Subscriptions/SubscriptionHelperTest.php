<?php
/**
 * Subscription Helper Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\TaxedMoney;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Subscription Helper Test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionHelperTest extends TestCase {
	/**
	 * Test next.
	 */
	public function test_next() {
		$subscription = new Subscription();

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( '2005-05-05' ),
			new SubscriptionInterval( 'P1W' ),
			new TaxedMoney( 100, 'USD' )
		);

		$phase->set_total_periods( 1 );

		$subscription->add_phase( $phase );

		$current_phase = $subscription->get_current_phase();

		$this->assertSame( $phase, $current_phase );

		$period = $phase->next_period();

		$current_phase = $subscription->get_current_phase();

		$this->assertNull( $current_phase );

		$this->assertEquals( '2005-05-12', $phase->get_end_date()->format( 'Y-m-d' ) );

		$this->assertEquals( '2005-05-05', $period->get_start_date()->format( 'Y-m-d' ) );
		$this->assertEquals( '2005-05-12', $period->get_end_date()->format( 'Y-m-d' ) );

		$next_payment_date = $subscription->get_next_payment_date();

		$this->assertNull( $next_payment_date );
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
	public function test_next_payment_date( $start_date, $interval_spec, $recurrences, $expected_date ) {
		$subscription = new Subscription();

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( $start_date ),
			new SubscriptionInterval( $interval_spec ),
			new TaxedMoney( 100, 'USD' )
		);

		$phase->set_total_periods( $recurrences );

		$subscription->add_phase( $phase );

		$phase->next_period();

		// Calculate.
		$next_payment_date = $subscription->get_next_payment_date();

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
		return [
			[ '2005-05-05', 'P1W', 1, null ],
			[ '2005-05-05', 'P3W', 2, '2005-05-26' ],
			[ '2005-05-05', 'P1M', 3, '2005-06-05' ],
			[ '2005-05-05', 'P1M', 6, '2005-06-05' ],
			[ '2005-05-05', 'P1Y', 1, null ],
			[ '2005-05-05', 'P1Y', 4, '2006-05-05' ],
		];
	}

	/**
	 * Test calculate next payment delivery date empty.
	 */
	public function test_calculate_next_payment_delivery_date_empty() {
		$subscription = new Subscription();

		// Calculate.
		$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

		$this->assertNull( $next_payment_delivery_date );
	}

	/**
	 * Test calculate next payment delivery date.
	 */
	public function test_calculate_next_payment_delivery_date() {
		$subscription = new Subscription();

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( '2005-05-05' ),
			new SubscriptionInterval( 'P1M' ),
			new TaxedMoney( 100, 'USD' )
		);

		$subscription->add_phase( $phase );

		// Test.
		$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

		$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_delivery_date );
		$this->assertEquals( '2005-05-05', $next_payment_delivery_date->format( 'Y-m-d' ) );
	}

	/**
	 * Test calculate next payment delivery date filter
	 */
	public function test_calculate_next_payment_delivery_date_filter() {
		$subscription = new Subscription();

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( '2005-05-05' ),
			new SubscriptionInterval( 'P1M' ),
			new TaxedMoney( 100, 'USD' )
		);

		$subscription->add_phase( $phase );

		// Filter.
		\add_filter(
			'pronamic_pay_subscription_next_payment_delivery_date',
			function( $next_payment_delivery_date, $subscription ) {
				return new \Pronamic\WordPress\DateTime\DateTime( '1970-01-01' );
			},
			10,
			2
		);

		// Test.
		$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

		$this->assertInstanceOf( \DateTimeInterface::class, $next_payment_delivery_date );
		$this->assertEquals( '1970-01-01', $next_payment_delivery_date->format( 'Y-m-d' ) );
	}
}
