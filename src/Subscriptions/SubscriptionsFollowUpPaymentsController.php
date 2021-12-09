<?php
/**
 * Subscriptions follow-up payments controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
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
		\add_action( 'init', array( $this, 'maybe_schedule_actions' ) );

		\add_action( 'pronamic_pay_schedule_follow_up_payments', array( $this, 'schedule_all' ) );

		\add_action( 'pronamic_pay_schedule_subscriptions_follow_up_payment', array( $this, 'schedule_paged' ) );

		\add_action( 'pronamic_pay_create_subscription_follow_up_payment', array( $this, 'action_create_subscription_follow_up_payment' ) );

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
			function( $args, $assoc_args ) {
				WP_CLI::debug( 'Query subscriptions that require follow-up payment.' );

				$query = $this->get_subscriptions_wp_query_that_require_follow_up_payment();

				WP_CLI::debug( \sprintf( 'Query executed: `found_posts` = %s, `max_num_pages`: %s.', $query->found_posts, $query->max_num_pages ) );

				WP_CLI\Utils\format_items(
					'table',
					$query->posts,
					array(
						'ID',
						'post_title',
					)
				);
			}
		);

		WP_CLI::add_command(
			'pay subscription schedule',
			function( $args, $assoc_args ) {
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

						exit;
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
		if ( false === \as_next_scheduled_action( 'pronamic_pay_schedule_follow_up_payments', array(), 'pronamic-pay' ) ) {
			\as_schedule_cron_action( \time(), '0 * * * *', 'pronamic_pay_schedule_follow_up_payments', array(), 'pronamic-pay' );
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
			array(
				'page' => $page,
			),
			'pronamic-pay'
		);
	}

	/**
	 * Scheduel subscriptions follow-up payment.
	 *
	 * @param int $page Page.
	 * @return void
	 */
	public function schedule_paged( $page ) {
		$query = $this->get_subscriptions_wp_query_that_require_follow_up_payment(
			array(
				'paged' => $page,
			)
		);

		$posts = \array_filter(
			$query->posts,
			function( $post ) {
				return ( $post instanceof WP_Post );
			}
		);

		$subscriptions = array();

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

		$date = new \DateTimeImmutable();

		if ( $next_payment_date > $date && $next_payment_delivery_date > $date ) {
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

		$actions_args = array(
			'subscription_id' => $subscription->get_id(),
		);

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
					'Unable to load subscription from post ID: %d.',
					$subscription_id
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
	 * Get WordPress query for subscriptions that require a follow-up payment.
	 *
	 * @param array $args Arguments.
	 * @return WP_Query
	 */
	private function get_subscriptions_wp_query_that_require_follow_up_payment( $args = array() ) {
		$date = new \DateTimeImmutable( 'now', new \DateTimeZone( 'GMT' ) );

		$query_args = array(
			'post_type'      => 'pronamic_pay_subscr',
			/**
			 * Posts per page is set to 100, higher could result in performance issues.
			 *
			 * @link https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#wp-postsperpage-post-limit
			 */
			'posts_per_page' => 100,
			'post_status'    => array(
				'subscr_pending',
				'subscr_failed',
				'subscr_active',
			),
			'meta_query'     => array(
				array(
					'relation' => 'OR',
					array(
						'key'     => '_pronamic_subscription_next_payment',
						'compare' => '<=',
						'value'   => $date->format( 'Y-m-d H:i:s' ),
						'type'    => 'DATETIME',
					),
					array(
						'key'     => '_pronamic_subscription_next_payment_delivery_date',
						'compare' => '<=',
						'value'   => $date->format( 'Y-m-d H:i:s' ),
						'type'    => 'DATETIME',
					),
				),
			),
			'order'          => 'DESC',
			'orderby'        => 'ID',
		);

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