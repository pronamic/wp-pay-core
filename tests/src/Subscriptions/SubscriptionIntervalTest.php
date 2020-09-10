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
}
