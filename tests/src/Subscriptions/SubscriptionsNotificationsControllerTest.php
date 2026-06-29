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

	/**
	 * Test no notification in 2-week period.
	 */
	public function test_no_notification_within_2_weeks() {
		$notifications_controller = new SubscriptionsNotificationsController();

		$source = 'test-within-2-weeks';

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
			'notification_date_renewal',
			( new \DateTime( '-10 days', new \DateTimeZone( 'GMT' ) ) )->format( DATE_ATOM )
		);
		$subscription->save();

		$notifications_controller->send_subscription_renewal_notification( $subscription );

		$this->assertSame( 0, \did_action( 'pronamic_subscription_renewal_notice_' . $source ) );
	}

	/**
	 * Test no notification within 2 weeks for legacy meta key.
	 */
	public function test_no_notification_within_2_weeks_legacy_meta_key() {
		$notifications_controller = new SubscriptionsNotificationsController();

		$source = 'test-within-2-weeks-legacy';

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
			'notification_date_1_week',
			( new \DateTime( '-10 days', new \DateTimeZone( 'GMT' ) ) )->format( DATE_ATOM )
		);
		$subscription->save();

		$notifications_controller->send_subscription_renewal_notification( $subscription );

		$this->assertSame( 0, \did_action( 'pronamic_subscription_renewal_notice_' . $source ) );
	}
}
