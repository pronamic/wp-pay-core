<?php
/**
 * Subscriptions completion controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use WP_CLI;
use WP_Post;
use WP_Query;

/**
 * Subscriptions completion controller
 */
class SubscriptionsCompletionController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'maybe_schedule_actions' ] );

		\add_action( 'pronamic_pay_schedule_subscriptions_completion', [ $this, 'schedule_all' ] );

		\add_action( 'pronamic_pay_schedule_paged_subscriptions_completion', [ $this, 'schedule_paged' ] );

		\add_action( 'pronamic_pay_complete_subscription', [ $this, 'action_complete_subscription' ] );
	}

	/**
	 * Maybe schedule actions.
	 *
	 * @link https://actionscheduler.org/
	 * @return void
	 */
	public function maybe_schedule_actions() {
		if ( false === \as_next_scheduled_action( 'pronamic_pay_schedule_subscriptions_completion', [], 'pronamic-pay' ) ) {
			\as_schedule_cron_action( \time(), '0 * * * *', 'pronamic_pay_schedule_subscriptions_completion', [], 'pronamic-pay' );
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

		$query = $this->get_subscriptions_wp_query_that_require_completion();

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
			'pronamic_pay_schedule_paged_subscriptions_completion',
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
		$query = $this->get_subscriptions_wp_query_that_require_completion(
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
			$this->schedule_subscription_completion( $subscription );
		}
	}


	/**
	 * Test if the subscription meets the notification requirements.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return bool True if meets requirements, false otherwise.
	 */
	private function meets_completion_requirements( Subscription $subscription ) {
		/**
		 * If a subscription does not have a end date, it makes no sense to complete.
		 */
		$end_date = $subscription->get_end_date();

		if ( null === $end_date ) {
			return false;
		}

		/**
		 * If the end date is in the future, it makes no sense to complete.
		 */
		$date = new \DateTimeImmutable();

		if ( $end_date > $date ) {
			return false;
		}

		return true;
	}

	/**
	 * Schedule subscription completion.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return int|null
	 */
	private function schedule_subscription_completion( Subscription $subscription ) {
		if ( ! $this->meets_completion_requirements( $subscription ) ) {
			return null;
		}

		$action_id = $subscription->get_meta( 'completion_action_id' );

		if ( ! empty( $action_id ) ) {
			return $action_id;
		}

		$actions_args = [
			'subscription_id' => $subscription->get_id(),
		];

		if ( false !== \as_next_scheduled_action( 'pronamic_pay_complete_subscription', $actions_args, 'pronamic-pay' ) ) {
			return null;
		}

		$action_id = \as_enqueue_async_action(
			'pronamic_pay_complete_subscription',
			$actions_args,
			'pronamic-pay'
		);

		$subscription->set_meta( 'completion_action_id', $action_id );

		$subscription->save();

		return $action_id;
	}

	/**
	 * Action complete subscription.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return void
	 * @throws \Exception Throws exception when unable to load subscription.
	 */
	public function action_complete_subscription( $subscription_id ) {
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

		$this->complete_subscription( $subscription );

		$subscription->set_meta( 'completion_action_id', null );

		$subscription->save();
	}

	/**
	 * Complete subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 */
	public function complete_subscription( Subscription $subscription ) {
		if ( ! $this->meets_completion_requirements( $subscription ) ) {
			return;
		}

		$subscription->status = SubscriptionStatus::COMPLETED;
	}

	/**
	 * Get WordPress query for subscriptions that require a notification.
	 *
	 * @param array $args Arguments.
	 * @return WP_Query
	 */
	private function get_subscriptions_wp_query_that_require_completion( $args = [] ) {
		$date = new \DateTimeImmutable( 'now', new \DateTimeZone( 'GMT' ) );

		$query_args = [
			'post_type'      => 'pronamic_pay_subscr',
			/**
			 * Posts per page is set to 100, higher could result in performance issues.
			 *
			 * @link https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#wp-postsperpage-post-limit
			 */
			'posts_per_page' => 100,
			'post_status'    => 'subscr_active',
			'meta_query'     => [
				[
					'key'     => '_pronamic_subscription_end_date',
					'compare' => '<=',
					'value'   => $date->format( 'Y-m-d H:i:s' ),
					'type'    => 'DATETIME',
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
