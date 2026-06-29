<?php
/**
 * Subscriptions notifications controller test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Subscriptions notifications controller test.
 */
class SubscriptionsNotificationsControllerTest extends TestCase {
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
			new \DateTime( '-2 weeks midnight', new \DateTimeZone( 'GMT' ) ),
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

	/**
	 * Test no notification in 2-week period.
	 *
	 * @dataProvider data_no_notification
	 */
	public function test_no_notification( $source, $meta_key ) {
		$notifications_controller = new SubscriptionsNotificationsController();

		$subscription = new Subscription();

		$subscription->set_source( $source );
		$subscription->set_mode( 'test' );

		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTime( '-3 weeks midnight', new \DateTimeZone( 'GMT' ) ),
			new SubscriptionInterval( 'P1M' ),
			new Money( '10', 'EUR' )
		);

		$subscription->add_phase( $phase );

		$subscription->set_meta(
			$meta_key,
			( new \DateTime( '-10 days', new \DateTimeZone( 'GMT' ) ) )->format( DATE_ATOM )
		);

		$subscription->save();

		$notifications_controller->send_subscription_renewal_notification( $subscription );

		$this->assertSame( 0, \did_action( 'pronamic_subscription_renewal_notice_' . $source ) );
	}

	/**
	 * Data provider for no-notification scenarios within 2 weeks.
	 */
	public static function data_no_notification() {
		return [
			[ 'test-within-2-weeks',        'notification_date_2_weeks' ],
			[ 'test-within-2-weeks-legacy', 'notification_date_1_week', ],
		];
	}
}
