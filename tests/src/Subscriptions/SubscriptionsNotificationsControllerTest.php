<?php
/**
 * Subscriptions notifications controller test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscriptions notifications controller test.
 */
class SubscriptionsNotificationsControllerTest extends \WP_UnitTestCase {
	/**
	 * Test notification.
	 */
	public function test_notification() {
		/**
		 * Notifications controller.
		 */
		$notifications_controller = new SubscriptionsNotificationsController();

		/**
		 * Source.
		 */
		$source = 'test';

		/**
		 * Subscription.
		 */
		$subscription = new Subscription();

		$subscription->set_source( $source );
		$subscription->set_mode( 'test' );

		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTime( '-1 week midnight', new \DateTimeZone( 'GMT' ) ),
			new SubscriptionInterval( 'P1M' ),
			new Money( '10', 'EUR' )
		);

		$subscription->add_phase( $phase );

		$period = $subscription->new_period();

		$subscription->save();

		/**
		 * Test.
		 */
		$this->assertNotNull( $subscription->get_id() );

		/**
		 * Send subscription renewal notification.
		 */
		$notifications_controller->send_subscription_renewal_notification( $subscription );

		/**
		 * Test hook.
		 */
		$this->assertSame( 1, \did_action( 'pronamic_subscription_renewal_notice_' . $source ) );

		/**
		 * Test double occurrence.
		 */
		$notifications_controller->send_subscription_renewal_notification( $subscription );
		$notifications_controller->send_subscription_renewal_notification( $subscription );
		$notifications_controller->send_subscription_renewal_notification( $subscription );

		$this->assertSame( 1, \did_action( 'pronamic_subscription_renewal_notice_' . $source ) );
	}
}
