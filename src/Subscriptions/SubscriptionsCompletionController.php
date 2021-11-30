<?php
/**
 * Subscriptions completion controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscriptions completion controller
 */
class SubscriptionsCompletionController {
	/**
	 * Subscriptions module.
	 *
	 * @var SubscriptionsModule
	 */
	private $subscriptions_module;

	/**
	 * Construct subscriptions notifications controller.
	 *
	 * @param SubscriptionsModule $subscriptions_module Subscriptions module.
	 */
	public function __construct( SubscriptionsModule $subscriptions_module ) {
		$this->subscriptions_module = $subscriptions_module;
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', array( $this, 'maybe_schedule_actions' ) );

		// The 'pronamic_pay_complete_subscriptions' hook completes active subscriptions.
		\add_action( 'pronamic_pay_complete_subscriptions', array( $this, 'complete_subscriptions' ) );
	}

	/**
	 * Maybe schedule actions.
	 *
	 * @link https://actionscheduler.org/
	 * @return void
	 */
	public function maybe_schedule_actions() {
		if ( false === \as_next_scheduled_action( 'pronamic_pay_complete_subscriptions', array(), 'pronamic-pay' ) ) {
			\as_schedule_cron_action( \time(), '0 * * * *', 'pronamic_pay_complete_subscriptions', array(), 'pronamic-pay' );
		}
	}

	/**
	 * Complete subscriptions.
	 *
	 * @param bool $cli_test Whether or not this a CLI test.
	 * @return void
	 */
	public function complete_subscriptions( $cli_test = false ) {
		$args = array(
			'post_type'   => 'pronamic_pay_subscr',
			'nopaging'    => true,
			'orderby'     => 'post_date',
			'order'       => 'ASC',
			'post_status' => 'subscr_active',
			'meta_query'  => array(
				array(
					'key'     => '_pronamic_subscription_source',
					'compare' => 'NOT IN',
					'value'   => array(
						// Don't create payments for sources which schedule payments.
						'woocommerce',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => '_pronamic_subscription_next_payment',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		);

		if ( ! $cli_test ) {
			/**
			 * End date.
			 *
			 * @todo needs update, meta key `_pronamic_subscription_end_date` has been removed.
			 */
			$args['meta_query'][1][] = array(
				'key'     => '_pronamic_subscription_end_date',
				'compare' => '<=',
				'value'   => current_time( 'mysql', true ),
				'type'    => 'DATETIME',
			);
		}

		$query = new WP_Query( $args );

		$posts = \array_filter(
			$query->posts,
			function( $post ) {
				return ( $post instanceof \WP_Post );
			}
		);

		foreach ( $posts as $post ) {
			if ( $cli_test ) {
				WP_CLI::log( sprintf( 'Processing post `%d` - "%s"…', $post->ID, get_the_title( $post ) ) );
			}

			// Complete subscription.
			try {
				$subscription = \get_pronamic_subscription( $post->ID );

				if ( null !== $subscription ) {
					$subscription->status = SubscriptionStatus::COMPLETED;

					$subscription->save();
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}
	}
}
