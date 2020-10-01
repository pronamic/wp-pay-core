<?php
/**
 * Subscription Interval Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscription Interval Test
 *
 * @author Remco Tolsma
 * @version 2.4.0
 */
class SubscriptionIntervalTest extends \WP_UnitTestCase {
	/**
	 * Test interval.
	 */
	public function test_interval() {
		$interval = new SubscriptionInterval( 'P2Y6M' );

		$this->assertEquals( 'P2Y6M', \strval( $interval ) );
		$this->assertEquals( '"P2Y6M"', \wp_json_encode( $interval ) );
	}

	/**
	 * Test multiply.
	 */
	public function test_multiply() {
		$interval = new SubscriptionInterval( 'P2Y6M' );

		$result = $interval->multiply( 2 );

		$this->assertEquals( 'P4Y12M', \strval( $result ) );
	}

	/**
	 * Test multiply by one.
	 */
	public function test_multiply_by_one() {
		$interval = new SubscriptionInterval( 'P2Y6M' );

		$result = $interval->multiply( 1 );

		$this->assertEquals( 'P2Y6M', \strval( $result ) );
	}

	/**
	 * Test multiply by zero.
	 */
	public function test_multiply_by_zero() {
		$interval = new SubscriptionInterval( 'P2Y6M' );

		$result = $interval->multiply( 0 );

		$this->assertEquals( 'P0Y0M', \strval( $result ) );
	}

	/**
	 * Test negative multiplier.
	 */
	public function test_negative_multiplier() {
		$interval = new SubscriptionInterval( 'P2Y6M' );

		$result = $interval->multiply( -2 );

		$this->assertEquals( 'P4Y12M', \strval( $result ) );
	}
}
