<?php
/**
 * Subscriptions Notifications Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use WP_Post;
use WP_Query;

/**
 * Subscriptions Notifications Controller
 */
class SubscriptionsNotificationsController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'maybe_schedule_actions' ] );

		\add_action( 'pronamic_pay_schedule_subscriptions_notification', [ $this, 'schedule_all' ] );

		\add_action( 'pronamic_pay_schedule_paged_subscriptions_notification', [ $this, 'schedule_paged' ] );

		\add_action( 'pronamic_pay_send_subscription_renewal_notification', [ $this, 'action_send_subscription_renewal_notification' ] );
	}

	/**
	 * Maybe schedule actions.
	 *
	 * @link https://actionscheduler.org/
	 * @return void
	 */
	public function maybe_schedule_actions() {
		if ( false === \as_next_scheduled_action( 'pronamic_pay_schedule_subscriptions_notification', [], 'pronamic-pay' ) ) {
			\as_schedule_cron_action( \time(), '0 0 * * *', 'pronamic_pay_schedule_subscriptions_notification', [], 'pronamic-pay' );
		}
	}

	/**
	 * Schedule all.
	 *
	 * @return void
	 */
	public function schedule_all() {
		if ( $this->is_processing_disabled() ) {
			return;
		}

		$query = $this->get_subscriptions_wp_query_that_require_notification();

		if ( 0 === $query->max_num_pages ) {
			return;
		}

		$pages = \range( $query->max_num_pages, 1 );

		foreach ( $pages as $page ) {
			$this->schedule_page( $page );
		}
	}

	/**
	 * Schedule page.
	 *
	 * @param int $page Page.
	 * @return int
	 */
	private function schedule_page( $page ) {
		return \as_enqueue_async_action(
			'pronamic_pay_schedule_paged_subscriptions_notification',
			[
				'page' => $page,
			],
			'pronamic-pay'
		);
	}

	/**
	 * Schedule paged.
	 *
	 * @param int $page Page.
	 * @return void
	 */
	public function schedule_paged( $page ) {
		$query = $this->get_subscriptions_wp_query_that_require_notification(
			[
				'paged' => $page,
			]
		);

		$posts = \array_filter(
			$query->posts,
			function ( $post ) {
				return ( $post instanceof WP_Post );
			}
		);

		$subscriptions = [];

		foreach ( $posts as $post ) {
			$subscription = \get_pronamic_subscription( $post->ID );

			if ( null !== $subscription ) {
				$subscriptions[] = $subscription;
			}
		}

		foreach ( $subscriptions as $subscription ) {
			$this->schedule_subscription_notification( $subscription );
		}
	}

	/**
	 * Test if the subscription meets the notification requirements.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return bool True if meets requirements, false otherwise.
	 */
	private function meets_notification_requirements( Subscription $subscription ) {
		/**
		 * If a subscription does not have a next payment date, it makes no sense to
		 * send a notification.
		 */
		$next_payment_date = $subscription->get_next_payment_date();

		if ( null === $next_payment_date ) {
			return false;
		}

		/**
		 * If the current date is greater than the next payment date, it no longer makes
		 * sense to send a notification.
		 */
		$date = new \DateTimeImmutable();

		if ( $date > $next_payment_date ) {
			return false;
		}

		/**
		 * If a notification has already been sent in the past week, it no longer makes
		 * sense to send a notification.
		 */
		$notification_date_string = $subscription->get_meta( 'notification_date_1_week' );

		if ( $notification_date_string ) {
			$notification_date = new DateTime( $notification_date_string );

			$threshold_date = new DateTime( 'midnight -1 week' );

			if ( $notification_date > $threshold_date ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Schedule subscription follow-up payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return int|null
	 */
	private function schedule_subscription_notification( Subscription $subscription ) {
		if ( ! $this->meets_notification_requirements( $subscription ) ) {
			return null;
		}

		$action_id = $subscription->get_meta( 'send_subscription_renewal_notification_action_id' );

		if ( ! empty( $action_id ) ) {
			return $action_id;
		}

		$actions_args = [
			'subscription_id' => $subscription->get_id(),
		];

		if ( false !== \as_next_scheduled_action( 'pronamic_pay_send_subscription_renewal_notification', $actions_args, 'pronamic-pay' ) ) {
			return null;
		}

		$action_id = \as_enqueue_async_action(
			'pronamic_pay_send_subscription_renewal_notification',
			$actions_args,
			'pronamic-pay'
		);

		$subscription->set_meta( 'send_subscription_renewal_notification_action_id', $action_id );

		$subscription->save();

		return $action_id;
	}

	/**
	 * Action send subscription renewal notification.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return void
	 * @throws \Exception Throws exception when unable to load subscription.
	 */
	public function action_send_subscription_renewal_notification( $subscription_id ) {
		// Check subscription.
		$subscription = \get_pronamic_subscription( (int) $subscription_id );

		if ( null === $subscription ) {
			throw new \Exception(
				\sprintf(
					'Unable to load subscription from post ID: %s.',
					\esc_html( (string) $subscription_id )
				)
			);
		}

		$this->send_subscription_renewal_notification( $subscription );

		$subscription->set_meta( 'send_subscription_renewal_notification_action_id', null );

		$subscription->save();
	}

	/**
	 * Send subscription renewal notification.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 * @throws \Exception Throws exception when gateway not found.
	 */
	public function send_subscription_renewal_notification( Subscription $subscription ) {
		if ( ! $this->meets_notification_requirements( $subscription ) ) {
			return;
		}

		$source = $subscription->get_source();

		/**
		 * Send renewal notice for source.
		 *
		 * [`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
		 *
		 * @param Subscription $subscription Subscription.
		 */
		\do_action( 'pronamic_subscription_renewal_notice_' . $source, $subscription );

		$subscription->set_meta( 'notification_date_1_week', \gmdate( DATE_ATOM ) );
	}

	/**
	 * Get WordPress query for subscriptions that require a notification.
	 *
	 * @param array $args Arguments.
	 * @return WP_Query
	 */
	private function get_subscriptions_wp_query_that_require_notification( $args = [] ) {
		$start_date = new \DateTimeImmutable( 'midnight +1 week', new \DateTimeZone( 'GMT' ) );
		$end_date   = new \DateTimeImmutable( 'tomorrow +1 week', new \DateTimeZone( 'GMT' ) );

		$query_args = [
			'post_type'      => 'pronamic_pay_subscr',
			/**
			 * Posts per page is set to 100, higher could result in performance issues.
			 *
			 * @link https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#wp-postsperpage-post-limit
			 */
			'posts_per_page' => 100,
			'post_status'    => [
				'subscr_active',
			],
			'meta_query'     => [
				[
					[
						'key'     => '_pronamic_subscription_next_payment',
						'compare' => 'BETWEEN',
						'value'   => [
							$start_date->format( 'Y-m-d H:i:s' ),
							$end_date->format( 'Y-m-d H:i:s' ),
						],
						'type'    => 'DATETIME',
					],
				],
			],
			'order'          => 'DESC',
			'orderby'        => 'ID',
		];

		if ( \array_key_exists( 'paged', $args ) ) {
			$query_args['paged']         = $args['paged'];
			$query_args['no_found_rows'] = true;
		}

		$query = new WP_Query( $query_args );

		return $query;
	}

	/**
	 * Is subscriptions processing disabled.
	 *
	 * @return bool True if processing recurring payment is disabled, false otherwise.
	 */
	private function is_processing_disabled() {
		return (bool) \get_option( 'pronamic_pay_subscriptions_processing_disabled', false );
	}
}
