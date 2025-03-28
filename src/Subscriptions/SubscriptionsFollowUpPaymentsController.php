<?php
/**
 * Subscriptions follow-up payments controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Pay\Plugin;
use WP_CLI;
use WP_Post;
use WP_Query;

/**
 * Subscriptions follow-up payments controller
 */
class SubscriptionsFollowUpPaymentsController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'maybe_schedule_actions' ] );

		\add_action( 'pronamic_pay_schedule_follow_up_payments', [ $this, 'schedule_all' ] );

		\add_action( 'pronamic_pay_schedule_subscriptions_follow_up_payment', [ $this, 'schedule_paged' ] );

		\add_action( 'pronamic_pay_create_subscription_follow_up_payment', [ $this, 'action_create_subscription_follow_up_payment' ] );

		$this->cli();
	}

	/**
	 * CLI.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-woocommerce.php#L365-L369
	 * @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-wc-cli.php
	 * @link https://make.wordpress.org/cli/handbook/commands-cookbook/
	 * @return void
	 */
	private function cli() {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		WP_CLI::add_command(
			'pay subscription list',
			function (): void {
				WP_CLI::debug( 'Query subscriptions that require follow-up payment.' );

				$query = $this->get_subscriptions_wp_query_that_require_follow_up_payment();

				WP_CLI::debug( \sprintf( 'Query executed: `found_posts` = %s, `max_num_pages`: %s.', $query->found_posts, $query->max_num_pages ) );

				WP_CLI\Utils\format_items(
					'table',
					$query->posts,
					[
						'ID',
						'post_title',
					]
				);
			}
		);

		WP_CLI::add_command(
			'pay subscription schedule',
			function ( $args, $assoc_args ): void {
				if ( $this->is_processing_disabled() ) {
					WP_CLI::error( 'Subscriptions processing is disabled.' );
				}

				/**
				 * Schedule all subscriptions pages.
				 */
				$all = \WP_CLI\Utils\get_flag_value( $assoc_args, 'all', false );

				if ( $all ) {
					WP_CLI::line( 'Schedule all subscriptions pages follow-up payments…' );

					$this->schedule_all();
				}

				/**
				 * Schedule one subscriptions page.
				 */
				$page = (int) \WP_CLI\Utils\get_flag_value( $assoc_args, 'page' );

				if ( $page > 0 ) {
					WP_CLI::line( \sprintf( 'Schedule subscriptions page %s follow-up payments…', $page ) );

					$action_id = $this->schedule_page( $page );

					WP_CLI::line( \sprintf( 'Action scheduled: %s', (string) $action_id ) );
				}

				/**
				 * Schedule specific subscriptions.
				 */
				foreach ( $args as $id ) {
					$subscription = \get_pronamic_subscription( $id );

					if ( null === $subscription ) {
						WP_CLI::error( \sprintf( 'Could not find a subscription with ID: %s', $id ) );
					}

					WP_CLI::line( \sprintf( 'Schedule subscription %s follow-up payment…', $id ) );

					$action_id = $this->schedule_subscription_follow_up_payment( $subscription );

					if ( null === $action_id ) {
						WP_CLI::error( 'Could not schedule action.' );
					}

					WP_CLI::line( \sprintf( 'Action scheduled: %s', (string) $action_id ) );
				}
			}
		);
	}

	/**
	 * Maybe schedule actions.
	 *
	 * @link https://actionscheduler.org/
	 * @return void
	 */
	public function maybe_schedule_actions() {
		if ( false === \as_next_scheduled_action( 'pronamic_pay_schedule_follow_up_payments', [], 'pronamic-pay' ) ) {
			\as_schedule_cron_action( \time(), '0 * * * *', 'pronamic_pay_schedule_follow_up_payments', [], 'pronamic-pay' );
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

		$query = $this->get_subscriptions_wp_query_that_require_follow_up_payment();

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
			'pronamic_pay_schedule_subscriptions_follow_up_payment',
			[
				'page' => $page,
			],
			'pronamic-pay'
		);
	}

	/**
	 * Schedule subscriptions follow-up payment.
	 *
	 * @param int $page Page.
	 * @return void
	 */
	public function schedule_paged( $page ) {
		$query = $this->get_subscriptions_wp_query_that_require_follow_up_payment(
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
			$this->schedule_subscription_follow_up_payment( $subscription );
		}
	}

	/**
	 * Test if the subscription meets the follow-up requirements.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return bool True if meets requirements, false otherwise.
	 */
	private function meets_follow_up_payment_requirements( Subscription $subscription ) {
		if ( 'woocommerce' === $subscription->get_source() ) {
			return false;
		}

		$next_payment_date = $subscription->get_next_payment_date();

		if ( null === $next_payment_date ) {
			return false;
		}

		$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

		if ( null === $next_payment_delivery_date ) {
			return false;
		}

		$query_start_date = $this->get_follow_up_payment_query_start_date();

		if ( $next_payment_date < $query_start_date && $next_payment_delivery_date < $query_start_date ) {
			return false;
		}

		$query_end_date = $this->get_follow_up_payment_query_end_date();

		if ( $next_payment_date > $query_end_date && $next_payment_delivery_date > $query_end_date ) {
			return false;
		}

		return true;
	}

	/**
	 * Schedule subscription follow-up payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return int|null
	 */
	private function schedule_subscription_follow_up_payment( Subscription $subscription ) {
		if ( ! $this->meets_follow_up_payment_requirements( $subscription ) ) {
			return null;
		}

		$action_id = $subscription->get_meta( 'create_follow_up_payment_action_id' );

		if ( ! empty( $action_id ) ) {
			return $action_id;
		}

		$actions_args = [
			'subscription_id' => $subscription->get_id(),
		];

		if ( false !== \as_next_scheduled_action( 'pronamic_pay_create_subscription_follow_up_payment', $actions_args, 'pronamic-pay' ) ) {
			return null;
		}

		$action_id = \as_enqueue_async_action(
			'pronamic_pay_create_subscription_follow_up_payment',
			$actions_args,
			'pronamic-pay'
		);

		$subscription->set_meta( 'create_follow_up_payment_action_id', $action_id );

		$subscription->save();

		return $action_id;
	}


	/**
	 * Action create subscription follow-up payment.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return void
	 * @throws \Exception Throws exception when unable to load subscription.
	 */
	public function action_create_subscription_follow_up_payment( $subscription_id ) {
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

		$subscription->set_meta( 'create_follow_up_payment_action_id', null );

		$this->create_subscription_follow_up_payment( $subscription );

		$subscription->save();
	}

	/**
	 * Create subscription follow-up payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 * @throws \Exception Throws exception when gateway not found.
	 */
	public function create_subscription_follow_up_payment( Subscription $subscription ) {
		if ( ! $this->meets_follow_up_payment_requirements( $subscription ) ) {
			return;
		}

		// Next period.
		$next_period = $subscription->get_next_period();

		if ( null === $next_period ) {
			return;
		}

		// New payment.
		$payment = $next_period->new_payment();

		$payment->set_lines( $subscription->get_lines() );

		$payment->set_meta( 'mollie_sequence_type', 'recurring' );

		// Start payment.
		$payment = Plugin::start_payment( $payment );

		// Update payment.
		Plugin::update_payment( $payment, false );
	}

	/**
	 * Get query start date for subscriptions that require a follow-up payment.
	 *
	 * @return \DateTimeImmutable
	 * @throws \Exception Throws exception in case of error.
	 */
	private function get_follow_up_payment_query_start_date() {
		return new \DateTimeImmutable( '-1 day', new \DateTimeZone( 'GMT' ) );
	}

	/**
	 * Get query end date for subscriptions that require a follow-up payment.
	 *
	 * @return \DateTimeImmutable
	 * @throws \Exception Throws exception in case of error.
	 */
	private function get_follow_up_payment_query_end_date() {
		return new \DateTimeImmutable( 'now', new \DateTimeZone( 'GMT' ) );
	}

	/**
	 * Get WordPress query for subscriptions that require a follow-up payment.
	 *
	 * @param array $args Arguments.
	 * @return WP_Query
	 */
	private function get_subscriptions_wp_query_that_require_follow_up_payment( $args = [] ) {
		$start_date = $this->get_follow_up_payment_query_start_date();
		$end_date   = $this->get_follow_up_payment_query_end_date();

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
					'relation' => 'OR',
					[
						'key'     => '_pronamic_subscription_next_payment',
						'compare' => 'BETWEEN',
						'value'   => [
							$start_date->format( 'Y-m-d H:i:s' ),
							$end_date->format( 'Y-m-d H:i:s' ),
						],
						'type'    => 'DATETIME',
					],
					[
						'key'     => '_pronamic_subscription_next_payment_delivery_date',
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
