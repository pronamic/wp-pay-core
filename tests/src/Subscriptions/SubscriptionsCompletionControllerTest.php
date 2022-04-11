<?php
/**
 * Subscriptions completion controller test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Subscriptions completion controller test.
 */
class SubscriptionsCompletionControllerTest extends TestCase {
	/**
	 * Test completion.
	 */
	public function test_completion() {
		/**
		 * Notifications controller.
		 */
		$completion_controller = new SubscriptionsCompletionController();

		/**
		 * Subscription.
		 */
		$subscription = new Subscription();

		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTime( '-1 month midnight', new \DateTimeZone( 'GMT' ) ),
			new SubscriptionInterval( 'P1M' ),
			new Money( '10', 'EUR' )
		);

		$phase->set_total_periods( 1 );

		$subscription->add_phase( $phase );

		$period = $subscription->new_period();

		$subscription->save();

		/**
		 * Test.
		 */
		$this->assertNotNull( $subscription->get_id() );

		/**
		 * End date.
		 */
		$end_date = $subscription->get_end_date();

		$expected = new \DateTime( 'midnight', new \DateTimeZone( 'GMT' ) );

		$this->assertEquals( $expected, $end_date );

		/**
		 * Complete subscription.
		 */
		$completion_controller->complete_subscription( $subscription );

		/**
		 * Test status.
		 */
		$this->assertSame( SubscriptionStatus::COMPLETED, $subscription->status );
	}
}
