<?php
/**
 * Subscriptions Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Subscriptions module
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @link https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.0.1
 */
class SubscriptionsModule {
	/**
	 * Plugin.
	 *
	 * @var Plugin $plugin
	 */
	public $plugin;

	/**
	 * Privacy.
	 *
	 * @var SubscriptionsPrivacy
	 */
	public $privacy;

	/**
	 * Construct and initialize a subscriptions module object.
	 *
	 * @param Plugin $plugin The plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Subscriptions privacy exporters and erasers.
		$this->privacy = new SubscriptionsPrivacy();

		// Actions.
		\add_action( 'wp_loaded', [ $this, 'maybe_handle_subscription_action' ] );

		\add_action( 'init', [ $this, 'maybe_schedule_subscription_events' ] );

		// Exclude subscription notes.
		\add_filter( 'comments_clauses', [ $this, 'exclude_subscription_comment_notes' ], 10, 2 );

		\add_action( 'pronamic_pay_pre_create_subscription', [ SubscriptionHelper::class, 'complement_subscription' ], 10, 1 );
		\add_action( 'pronamic_pay_pre_create_payment', [ $this, 'complement_subscription_by_payment' ], 10, 1 );

		// Payment source filters.
		\add_filter( 'pronamic_payment_source_text_subscription_payment_method_change', [ $this, 'source_text_subscription_payment_method_change' ] );
		\add_filter( 'pronamic_payment_source_description_subscription_payment_method_change', [ $this, 'source_description_subscription_payment_method_change' ] );

		// Listen to payment status changes so we can update related subscriptions.
		\add_action( 'pronamic_payment_status_update', [ $this, 'payment_status_update' ] );

		// Listen to subscription status changes so we can log these in a note.
		\add_action( 'pronamic_subscription_status_update', [ $this, 'log_subscription_status_update' ], 10, 4 );

		// REST API.
		\add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );

		// Follow-up payments.
		$follow_up_payments_controller = new SubscriptionsFollowUpPaymentsController();

		$follow_up_payments_controller->setup();

		// Notifications.
		$notifications_controller = new SubscriptionsNotificationsController();

		$notifications_controller->setup();

		// Completion.
		$completion_controller = new SubscriptionsCompletionController();

		$completion_controller->setup();
	}

	/**
	 * Comments clauses.
	 *
	 * @param array             $clauses The database query clauses.
	 * @param \WP_Comment_Query $query   The WordPress comment query object.
	 * @return array
	 */
	public function exclude_subscription_comment_notes( $clauses, $query ) {
		$type = $query->query_vars['type'];

		// Ignore subscription notes comments if it's not specifically requested.
		if ( 'subscription_note' !== $type ) {
			$clauses['where'] .= " AND comment_type != 'subscription_note'";
		}

		return $clauses;
	}

	/**
	 * Complement subscription by payment.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function complement_subscription_by_payment( $payment ) {
		foreach ( $payment->get_subscriptions() as $subscription ) {
			if ( ! $subscription->is_first_payment( $payment ) ) {
				continue;
			}

			// Complement subscription.
			SubscriptionHelper::complement_subscription_by_payment( $subscription, $payment );
		}
	}

	/**
	 * Payment status update.
	 *
	 * @param Payment $payment The status updated payment.
	 * @return void
	 */
	public function payment_status_update( $payment ) {
		// Payment method changes do not affect the subscription status.
		if (
			'subscription_payment_method_change' === $payment->get_source()
				||
			true === $payment->get_meta( 'woocommerce_subscription_change_payment_method' )
		) {
			return;
		}

		foreach ( $payment->get_subscriptions() as $subscription ) {
			// Status.
			$status_before = $subscription->get_status();
			$status_update = $status_before;

			switch ( $payment->get_status() ) {
				case PaymentStatus::OPEN:
					// @todo
					break;
				case PaymentStatus::SUCCESS:
					$status_update = SubscriptionStatus::ACTIVE;

					break;
				case PaymentStatus::FAILURE:
					/**
					 * Subscription status for failed payment.
					 *
					 * @todo Determine update status based on reason of failed payment. Use `failure` for now as that is usually the desired status.
					 * @link https://www.europeanpaymentscouncil.eu/document-library/guidance-documents/guidance-reason-codes-sepa-direct-debit-r-transactions
					 * @link https://github.com/pronamic/wp-pronamic-ideal/commit/48449417eac49eb6a93480e3b523a396c7db9b3d#diff-6712c698c6b38adfa7190a4be983a093
					 */
					$status_update = SubscriptionStatus::ON_HOLD;

					break;
				case PaymentStatus::CANCELLED:
				case PaymentStatus::EXPIRED:
					// Set subscription status to 'On Hold' only if the subscription is not already active when processing the first payment.
					if ( ! ( $subscription->is_first_payment( $payment ) && SubscriptionStatus::ACTIVE === $subscription->get_status() ) ) {
						$status_update = SubscriptionStatus::ON_HOLD;
					}

					break;
			}

			/*
			 * The status of canceled or completed subscriptions will not be changed automatically,
			 * unless the cancelled subscription is manually being renewed.
			 */
			$is_renewal = false;

			if ( true === $payment->get_meta( 'manual_subscription_renewal' ) && SubscriptionStatus::CANCELLED === $status_before && SubscriptionStatus::ACTIVE === $status_update ) {
				$is_renewal = true;
			}

			if ( $is_renewal || ! in_array( $status_before, [ SubscriptionStatus::CANCELLED, SubscriptionStatus::COMPLETED, SubscriptionStatus::ON_HOLD ], true ) ) {
				$subscription->set_status( $status_update );

				// Update.
				if ( $status_before !== $status_update ) {
					$subscription->save();
				}
			}
		}
	}

	/**
	 * Get subscription status update note.
	 *
	 * @param string|null $old_status   Old meta status.
	 * @param string      $new_status   New meta status.
	 * @return string
	 */
	private function get_subscription_status_update_note( $old_status, $new_status ) {
		$old_label = $this->plugin->subscriptions_data_store->get_meta_status_label( $old_status );
		$new_label = $this->plugin->subscriptions_data_store->get_meta_status_label( $new_status );

		if ( null === $old_status ) {
			return sprintf(
			/* translators: 1: new status */
				__( 'Subscription created with status "%1$s".', 'pronamic_ideal' ),
				esc_html( empty( $new_label ) ? $new_status : $new_label )
			);
		}

		return sprintf(
		/* translators: 1: old status, 2: new status */
			__( 'Subscription status changed from "%1$s" to "%2$s".', 'pronamic_ideal' ),
			esc_html( empty( $old_label ) ? $old_status : $old_label ),
			esc_html( empty( $new_label ) ? $new_status : $new_label )
		);
	}

	/**
	 * Subscription status update.
	 *
	 * @param Subscription $subscription The status updated subscription.
	 * @param bool         $can_redirect Whether or not redirects should be performed.
	 * @param string|null  $old_status   Old meta status.
	 * @param string       $new_status   New meta status.
	 *
	 * @return void
	 */
	public function log_subscription_status_update( $subscription, $can_redirect, $old_status, $new_status ) {
		$note = $this->get_subscription_status_update_note( $old_status, $new_status );

		try {
			$subscription->add_note( $note );
		} catch ( \Exception $e ) {
			return;
		}
	}

	/**
	 * Handle subscription actions.
	 *
	 * Extensions like Gravity Forms can send action links in for example
	 * email notifications so users can cancel or renew their subscription.
	 *
	 * @return void
	 */
	public function maybe_handle_subscription_action() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['subscription'] ) || ! isset( $_GET['action'] ) || ! isset( $_GET['key'] ) ) {
			return;
		}

		Util::no_cache();

		$subscription_id = filter_input( INPUT_GET, 'subscription', \FILTER_SANITIZE_NUMBER_INT );

		if ( false === $subscription_id || null === $subscription_id ) {
			return;
		}

		$subscription = get_pronamic_subscription( (int) $subscription_id );

		// Check if subscription and key are valid.
		if ( ! $subscription || $_GET['key'] !== $subscription->get_key() ) {
			wp_safe_redirect( home_url() );

			exit;
		}

		// Handle action.
		switch ( $_GET['action'] ) {
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			case 'cancel':
				$this->handle_subscription_cancel( $subscription );

				break;
			case 'renew':
				$this->handle_subscription_renew( $subscription );

				break;
			case 'mandate':
				$this->handle_subscription_mandate( $subscription );

				exit;
		}
	}

	/**
	 * Handle cancel subscription action request.
	 *
	 * @param Subscription $subscription Subscription to cancel.
	 * @return void
	 */
	private function handle_subscription_cancel( Subscription $subscription ) {
		$this->maybe_cancel_subscription( $subscription );

		require __DIR__ . '/../../views/subscription-cancel.php';

		exit;
	}

	/**
	 * Maybe cancel subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 */
	private function maybe_cancel_subscription( Subscription $subscription ) {
		if ( SubscriptionStatus::CANCELLED === $subscription->get_status() ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_pay_cancel_subscription_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_key( $_POST['pronamic_pay_cancel_subscription_nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'pronamic_pay_cancel_subscription_' . $subscription->get_id() ) ) {
			return;
		}

		$subscription->set_status( SubscriptionStatus::CANCELLED );

		$subscription->save();

		$url = \home_url();

		$page_id = \pronamic_pay_get_page_id( 'subscription_canceled' );

		if ( $page_id > 0 ) {
			$page_url = \get_permalink( $page_id );

			if ( false !== $page_url ) {
				$url = $page_url;
			}
		}

		\wp_safe_redirect( $url );

		exit;
	}

	/**
	 * Check if subscription should be renewed.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return bool
	 */
	private function should_renew( Subscription $subscription ) {
		if ( ! \array_key_exists( 'pronamic_pay_renew_subscription_nonce', $_POST ) ) {
			return false;
		}

		$nonce = \sanitize_key( $_POST['pronamic_pay_renew_subscription_nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'pronamic_pay_renew_subscription_' . $subscription->get_id() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Handle renew subscription action request.
	 *
	 * @param Subscription $subscription Subscription to renew.
	 * @return void
	 * @throws \Exception Throws exception if unable to redirect (empty payment action URL).
	 */
	private function handle_subscription_renew( Subscription $subscription ) {
		// Check gateway.
		$gateway = $subscription->get_gateway();

		if ( null === $gateway ) {
			require __DIR__ . '/../../views/subscription-renew-failed.php';

			exit;
		}

		// Check current phase.
		$current_phase = $subscription->get_current_phase();

		if ( null === $current_phase ) {
			require __DIR__ . '/../../views/subscription-renew-failed.php';

			exit;
		}

		if ( $this->should_renew( $subscription ) ) {
			try {
				// Create payment.
				$payment = $subscription->new_payment();

				$payment->order_id = $subscription->get_order_id();

				/**
				 * We set the payment method to `null` so that users get the
				 * chance to choose a payment method themselves if possible.
				 *
				 * @link https://github.com/pronamic/wp-pronamic-pay-mollie/issues/23
				 * @link https://github.com/pronamic/wp-pay-core/pull/99
				 */
				$payment->set_payment_method( null );

				$payment->set_lines( $subscription->get_lines() );
				$payment->set_total_amount( $current_phase->get_amount() );

				// Maybe cancel current expired phase and add new phase.
				if ( SubscriptionStatus::CANCELLED === $subscription->get_status() && 'gravityformsideal' === $subscription->get_source() ) {
					$now = new DateTimeImmutable();

					if ( $current_phase->get_next_date() < $now ) {
						// Cancel current phase.
						$current_phase->set_canceled_at( $now );

						// Add new phase, starting now.
						$new_phase = new SubscriptionPhase( $subscription, $now, $current_phase->get_interval(), $current_phase->get_amount() );

						$subscription->add_phase( $new_phase );
					}
				}

				// Set payment period.
				$renewal_period = $subscription->get_renewal_period();

				if ( null !== $renewal_period ) {
					$payment->set_total_amount( $renewal_period->get_amount() );

					$payment->add_period( $renewal_period );
				}

				// Start payment.
				$payment = Plugin::start_payment( $payment );
			} catch ( \Exception $e ) {
				require __DIR__ . '/../../views/subscription-renew-failed.php';

				exit;
			}

			// Redirect.
			try {
				$gateway->redirect( $payment );
			} catch ( \Exception $e ) {
				Plugin::render_exception( $e );

				exit;
			}

			return;
		}

		require __DIR__ . '/../../views/subscription-renew.php';

		exit;
	}

	/**
	 * Handle subscription mandate update action request.
	 *
	 * @param Subscription $subscription Subscription to update mandate for.
	 * @return void
	 * @throws \Exception Throws exception if unable to redirect (empty payment action URL).
	 */
	private function handle_subscription_mandate( Subscription $subscription ) {
		$gateway = $subscription->get_gateway();

		if ( null === $gateway ) {
			require __DIR__ . '/../../views/subscription-mandate-failed.php';

			exit;
		}

		$nonce = array_key_exists( 'pronamic_pay_nonce', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_nonce'] ) ) : '';

		if ( \wp_verify_nonce( $nonce, 'pronamic_pay_update_subscription_mandate' ) ) {
			$mandate_id = null;

			if ( \array_key_exists( 'pronamic_pay_subscription_mandate', $_POST ) ) {
				$mandate_id = \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_subscription_mandate'] ) );
			}

			if ( ! empty( $mandate_id ) ) {
				try {
					if ( ! \is_callable( [ $gateway, 'update_subscription_mandate' ] ) ) {
						throw new \Exception( __( 'Gateway does not support subscription mandate updates.', 'pronamic_ideal' ) );
					}

					$gateway->update_subscription_mandate( $subscription, $mandate_id );

					require __DIR__ . '/../../views/subscription-mandate-updated.php';

					exit;
				} catch ( \Exception $e ) {
					require __DIR__ . '/../../views/subscription-mandate-failed.php';

					exit;
				}
			}

			// Start new first payment.
			try {
				$payment = $subscription->new_payment();

				// Set source.
				$payment->set_source( 'subscription_payment_method_change' );
				$payment->set_source_id( null );

				// Set payment method.
				if ( array_key_exists( 'pronamic_pay_subscription_payment_method', $_POST ) ) {
					$payment_method = \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_subscription_payment_method'] ) );

					if ( ! empty( $payment_method ) ) {
						$payment->set_payment_method( $payment_method );
					}
				}

				/*
				 * Use payment method minimum amount for verification payment.
				 *
				 * @link https://help.mollie.com/hc/en-us/articles/115000667365-What-are-the-minimum-and-maximum-amounts-per-payment-method-
				 */
				switch ( $payment->get_payment_method() ) {
					case PaymentMethods::DIRECT_DEBIT_BANCONTACT:
						$amount = 0.02;

						break;
					case PaymentMethods::DIRECT_DEBIT_SOFORT:
						$amount = 0.10;

						break;
					case PaymentMethods::APPLE_PAY:
					case PaymentMethods::CREDIT_CARD:
					case PaymentMethods::PAYPAL:
						$amount = 0.00;

						break;
					default:
						$amount = 0.01;
				}

				$total_amount = new Money(
					$amount,
					$payment->get_total_amount()->get_currency()
				);

				$payment->set_total_amount( $total_amount );

				// Make sure to only start payments for supported gateways.
				$gateway = $payment->get_gateway();

				if ( null === $gateway ) {
					require __DIR__ . '/../../views/subscription-mandate-failed.php';

					exit;
				}

				// Start payment.
				$payment = Plugin::start_payment( $payment );
			} catch ( \Exception $e ) {
				require __DIR__ . '/../../views/subscription-mandate-failed.php';

				exit;
			}

			$gateway->redirect( $payment );

			return;
		}

		\wp_register_script(
			'pronamic-pay-slick-carousel-script',
			plugins_url( 'assets/slick-carousel/slick.min.js', dirname( __DIR__ ) ),
			[
				'jquery',
			],
			'1.8.1',
			false
		);

		\wp_register_script(
			'pronamic-pay-subscription-mandate',
			plugins_url( 'js/dist/subscription-mandate.min.js', dirname( __DIR__ ) ),
			[
				'jquery',
				'pronamic-pay-slick-carousel-script',
			],
			$this->plugin->get_version(),
			false
		);

		\wp_register_style(
			'pronamic-pay-slick-carousel-style',
			plugins_url( 'assets/slick-carousel/slick.min.css', dirname( __DIR__ ) ),
			[],
			'1.8.1'
		);

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- No version for Google Fonts.
		\wp_register_style(
			'pronamic-pay-google-font-roboto-mono',
			'https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap',
			[],
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- No version for Google Fonts.
			null
		);

		\wp_register_style(
			'pronamic-pay-subscription-mandate',
			plugins_url( 'css/card-slider.css', dirname( __DIR__ ) ),
			[
				'pronamic-pay-redirect',
				'pronamic-pay-slick-carousel-style',
				'pronamic-pay-google-font-roboto-mono',
			],
			$this->plugin->get_version()
		);

		require __DIR__ . '/../../views/subscription-mandate.php';

		exit;
	}

	/**
	 * Can payment be retried.
	 *
	 * @param Payment $payment Payment to retry.
	 * @return bool
	 */
	public function can_retry_payment( Payment $payment ) {
		// Check status.
		if ( PaymentStatus::FAILURE !== $payment->get_status() ) {
			return false;
		}

		// Check periods.
		$periods = $payment->get_periods();

		if ( null === $periods ) {
			return false;
		}

		// Check for pending and successful child payments.
		$payments = \get_pronamic_payments_by_meta( '', '', [ 'post_parent' => $payment->get_id() ] );

		foreach ( $payments as $child_payment ) {
			if ( \in_array( $child_payment->get_status(), [ PaymentStatus::OPEN, PaymentStatus::SUCCESS ], true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Maybe schedule subscription payments.
	 *
	 * @return void
	 */
	public function maybe_schedule_subscription_events() {
		// Unschedule legacy WordPress Cron hook.
		\wp_clear_scheduled_hook( 'pronamic_pay_update_subscription_payments' );
		\wp_clear_scheduled_hook( 'pronamic_pay_complete_subscriptions' );
	}

	/**
	 * Is subscriptions processing disabled.
	 *
	 * @return bool True if processing recurring payment is disabled, false otherwise.
	 */
	public function is_processing_disabled() {
		return (bool) \get_option( 'pronamic_pay_subscriptions_processing_disabled', false );
	}

	/**
	 * REST API init.
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
	 *
	 * @return void
	 */
	public function rest_api_init() {
		\register_rest_route(
			'pronamic-pay/v1',
			'/subscriptions/(?P<subscription_id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_api_subscription' ],
				'permission_callback' => function () {
					return \current_user_can( 'edit_payments' );
				},
				'args'                => [
					'subscription_id' => [
						'description' => __( 'Subscription ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
				],
			]
		);

		\register_rest_route(
			'pronamic-pay/v1',
			'/subscriptions/(?P<subscription_id>\d+)/phases/(?P<sequence_number>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_api_subscription_phase' ],
				'permission_callback' => function () {
					return \current_user_can( 'edit_payments' );
				},
				'args'                => [
					'subscription_id' => [
						'description' => __( 'Subscription ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
					'sequence_number' => [
						'description' => __( 'Subscription phase sequence number.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
				],
			]
		);
	}

	/**
	 * REST API subscription.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_subscription( \WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'subscription_id' );

		$subscription = \get_pronamic_subscription( $subscription_id );

		if ( null === $subscription ) {
			return new \WP_Error(
				'pronamic-pay-subscription-not-found',
				\sprintf(
					/* translators: %s: Subscription ID */
					\__( 'Could not find subscription with ID `%s`.', 'pronamic_ideal' ),
					$subscription_id
				),
				$subscription_id
			);
		}

		return $subscription;
	}

	/**
	 * REST API subscription phase.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_subscription_phase( \WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'subscription_id' );

		$subscription = \get_pronamic_subscription( $subscription_id );

		if ( null === $subscription ) {
			return new \WP_Error(
				'pronamic-pay-subscription-not-found',
				\sprintf(
					/* translators: %s: Subscription ID */
					\__( 'Could not find subscription with ID `%s`.', 'pronamic_ideal' ),
					$subscription_id
				),
				$subscription_id
			);
		}

		$sequence_number = $request->get_param( 'sequence_number' );

		$phase = $subscription->get_phase_by_sequence_number( $sequence_number );

		if ( null === $phase ) {
			return new \WP_Error(
				'pronamic-pay-subscription-phase-not-found',
				\sprintf(
					/* translators: %s: Subscription ID */
					\__( 'Could not find subscription phase with sequence number `%s`.', 'pronamic_ideal' ),
					$sequence_number
				),
				$sequence_number
			);
		}

		return $phase;
	}

	/**
	 * Source text filter.
	 *
	 * @param string $text The source text to filter.
	 * @return string
	 */
	public function source_text_subscription_payment_method_change( $text ) {
		$text = \__( 'Subscription payment method change', 'pronamic_ideal' );

		return $text;
	}

	/**
	 * Source description filter.
	 *
	 * @param string $text The source text to filter.
	 * @return string
	 */
	public function source_description_subscription_payment_method_change( $text ) {
		$text = \__( 'subscription payment method change', 'pronamic_ideal' );

		return $text;
	}
}
