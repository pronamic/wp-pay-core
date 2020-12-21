<?php
/**
 * Subscriptions Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateInterval;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Recurring;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use WP_CLI;
use WP_Error;
use WP_Query;

/**
 * Title: Subscriptions module
 * Description:
 * Copyright: 2005-2020 Pronamic
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
		add_action( 'wp_loaded', array( $this, 'handle_subscription' ) );

		add_action( 'plugins_loaded', array( $this, 'maybe_schedule_subscription_events' ), 6 );

		// Exclude subscription notes.
		add_filter( 'comments_clauses', array( $this, 'exclude_subscription_comment_notes' ), 10, 2 );

		add_action( 'pronamic_pay_new_payment', array( $this, 'maybe_create_subscription' ) );

		// The 'pronamic_pay_update_subscription_payments' hook adds subscription payments and sends renewal notices.
		add_action( 'pronamic_pay_update_subscription_payments', array( $this, 'update_subscription_payments' ) );

		// The 'pronamic_pay_complete_subscriptions' hook completes active subscriptions.
		add_action( 'pronamic_pay_complete_subscriptions', array( $this, 'complete_subscriptions' ) );

		// Listen to payment status changes so we can update related subscriptions.
		add_action( 'pronamic_payment_status_update', array( $this, 'payment_status_update' ) );

		// Listen to subscription status changes so we can log these in a note.
		add_action( 'pronamic_subscription_status_update', array( $this, 'log_subscription_status_update' ), 10, 4 );

		// WordPress CLI.
		// @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-woocommerce.php#L365-L369.
		// @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-wc-cli.php.
		// @link https://make.wordpress.org/cli/handbook/commands-cookbook/.
		if ( Util::doing_cli() ) {
			WP_CLI::add_command( 'pay subscriptions test', array( $this, 'cli_subscriptions_test' ) );
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Handle subscription actions.
	 *
	 * Extensions like Gravity Forms can send action links in for example
	 * email notifications so users can cancel or renew their subscription.
	 *
	 * @return void
	 */
	public function handle_subscription() {
		if ( ! Util::input_has_vars( INPUT_GET, array( 'subscription', 'action', 'key' ) ) ) {
			return;
		}

		Util::no_cache();

		$subscription_id = filter_input( INPUT_GET, 'subscription', FILTER_SANITIZE_STRING );
		$action          = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$key             = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );

		$subscription = get_pronamic_subscription( $subscription_id );

		// Check if subscription and key are valid.
		if ( ! $subscription || $key !== $subscription->get_key() ) {
			wp_safe_redirect( home_url() );

			exit;
		}

		// Handle action.
		switch ( $action ) {
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
		if (
			'POST' === Server::get( 'REQUEST_METHOD' )
				&&
			SubscriptionStatus::CANCELLED !== $subscription->get_status()
		) {
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

		require __DIR__ . '/../../views/subscription-cancel.php';

		exit;
	}

	/**
	 * Handle renew subscription action request.
	 *
	 * @param Subscription $subscription Subscription to renew.
	 * @return void
	 * @throws \Exception Throws exception if unable to redirect (empty payment action URL).
	 */
	private function handle_subscription_renew( Subscription $subscription ) {
		$gateway = Plugin::get_gateway( $subscription->config_id );

		if ( empty( $gateway ) ) {
			require __DIR__ . '/../../views/subscription-renew-failed.php';

			exit;
		}

		if ( 'POST' === Server::get( 'REQUEST_METHOD' ) ) {
			try {
				$payment = $this->new_subscription_payment( $subscription );

				$payment->recurring = false;

				$payment = $this->start_payment( $payment );
			} catch ( \Exception $e ) {
				require __DIR__ . '/../../views/subscription-renew-failed.php';

				exit;
			}

			$error = $gateway->get_error();

			if ( $error instanceof WP_Error ) {
				Plugin::render_errors( $error );

				exit;
			}

			if ( $payment ) {
				$gateway->redirect( $payment );
			}

			return;
		}

		// Payment method input HTML.
		$gateway->set_payment_method( $subscription->payment_method );

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
		$gateway = Plugin::get_gateway( $subscription->config_id );

		if ( empty( $gateway ) ) {
			require __DIR__ . '/../../views/subscription-mandate-failed.php';

			exit;
		}

		$nonce = filter_input( \INPUT_POST, 'pronamic_pay_nonce', \FILTER_SANITIZE_STRING );

		if ( \wp_verify_nonce( $nonce, 'pronamic_pay_update_subscription_mandate' ) ) {
			$mandate_id = \filter_input( \INPUT_POST, 'pronamic_pay_subscription_mandate', \FILTER_SANITIZE_STRING );

			if ( ! empty( $mandate_id ) ) {
				try {
					if ( ! \is_callable( array( $gateway, 'update_subscription_mandate' ) ) ) {
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
				$payment = $this->new_subscription_payment( $subscription );

				$payment_method = \filter_input( \INPUT_POST, 'pronamic_pay_subscription_payment_method', \FILTER_SANITIZE_STRING );

				if ( null !== $payment && ! empty( $payment_method ) ) {
					$payment->method = $payment_method;
				}

				if ( null === $payment ) {
					require __DIR__ . '/../../views/subscription-mandate-failed.php';

					exit;
				}

				$payment->recurring = false;

				/*
				 * Use payment method minimum amount for verification payment.
				 *
				 * @link https://help.mollie.com/hc/en-us/articles/115000667365-What-are-the-minimum-and-maximum-amounts-per-payment-method-
				 */
				switch ( $payment->method ) {
					case PaymentMethods::DIRECT_DEBIT_BANCONTACT:
						$amount = 0.02;

						break;
					case PaymentMethods::DIRECT_DEBIT_SOFORT:
						$amount = 0.10;

						break;
					default:
						$amount = 0.01;
				}

				$payment->set_total_amount(
					new TaxedMoney(
						$amount,
						$payment->get_total_amount()->get_currency()
					)
				);

				// Make sure to only start payments for supported gateways.
				$gateway = Plugin::get_gateway( $payment->get_config_id() );

				if ( null === $gateway ) {
					require __DIR__ . '/../../views/subscription-mandate-failed.php';

					exit;
				}

				// Start payment.
				$payment = Plugin::start_payment( $payment, $gateway );
			} catch ( \Exception $e ) {
				require __DIR__ . '/../../views/subscription-mandate-failed.php';

				exit;
			}

			$error = $gateway->get_error();

			if ( $error instanceof WP_Error ) {
				Plugin::render_errors( $error );

				exit;
			}

			$gateway->redirect( $payment );

			return;
		}

		\wp_register_script(
			'pronamic-pay-subscription-mandate',
			'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			false
		);

		\wp_register_style(
			'pronamic-pay-card-slider-slick',
			'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css',
			array(),
			$this->plugin->get_version()
		);

		\wp_register_style(
			'pronamic-pay-card-slider-google-font',
			'https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap',
			array(),
			$this->plugin->get_version()
		);

		\wp_register_style(
			'pronamic-pay-subscription-mandate',
			plugins_url( 'css/card-slider.css', dirname( dirname( __FILE__ ) ) ),
			array( 'pronamic-pay-redirect', 'pronamic-pay-card-slider-slick', 'pronamic-pay-card-slider-google-font' ),
			$this->plugin->get_version()
		);

		require __DIR__ . '/../../views/subscription-mandate.php';

		exit;
	}

	/**
	 * Create a new subscription payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return null|Payment
	 */
	public function new_subscription_payment( Subscription $subscription ) {
		// Prevent creating a new subscription payment if next payment date is (later than) the subscription end date.
		if ( isset( $subscription->end_date, $subscription->next_payment_date ) && $subscription->next_payment_date >= $subscription->end_date ) {
			$subscription->next_payment_date          = null;
			$subscription->next_payment_delivery_date = null;

			// Delete next payment post meta.
			$subscription->set_meta( 'next_payment', null );
			$subscription->set_meta( 'next_payment_delivery_date', null );

			return null;
		}

		// Create payment.
		$payment = new Payment();

		$payment->config_id       = $subscription->get_config_id();
		$payment->order_id        = $subscription->get_order_id();
		$payment->description     = $subscription->description;
		$payment->source          = $subscription->get_source();
		$payment->source_id       = $subscription->get_source_id();
		$payment->email           = $subscription->get_email();
		$payment->method          = $subscription->payment_method;
		$payment->issuer          = $subscription->issuer;
		$payment->recurring       = true;
		$payment->subscription    = $subscription;
		$payment->subscription_id = $subscription->get_id();

		$payment->set_origin_id( $subscription->get_origin_id() );
		$payment->set_customer( $subscription->get_customer() );
		$payment->set_billing_address( $subscription->get_billing_address() );
		$payment->set_shipping_address( $subscription->get_shipping_address() );
		$payment->set_lines( $subscription->get_lines() );

		// Get amount from current subscription phase.
		$current_phase = $subscription->get_current_phase();

		if ( null === $current_phase ) {
			return null;
		}

		$payment->set_total_amount( $current_phase->get_amount() );

		return $payment;
	}

	/**
	 * Start payment.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @throws \UnexpectedValueException Throw unexpected value exception when no subscription was found in payment.
	 *
	 * @return Payment
	 */
	public function start_payment( Payment $payment ) {
		// Set recurring type.
		if ( $payment->get_recurring() ) {
			$payment->recurring_type = Recurring::RECURRING;
		}

		$subscription = $payment->get_subscription();

		if ( empty( $subscription ) ) {
			throw new \UnexpectedValueException( 'No subscription object found in payment.' );
		}

		// Calculate payment start and end dates.
		$periods = $payment->get_periods();

		if ( null === $periods ) {
			$period = $subscription->new_period();

			if ( null === $period ) {
				throw new \UnexpectedValueException( 'Can not create new period for subscription.' );
			}

			$payment->add_period( $period );
		}

		$periods = $payment->get_periods();

		if ( null === $periods ) {
			throw new \UnexpectedValueException( 'Can not create payment without period for subscription.' );
		}

		$period = reset( $periods );

		if ( false === $period ) {
			throw new \UnexpectedValueException( 'Can not create payment without period for subscription.' );
		}

		$start_date = $period->get_start_date();
		$end_date   = $period->get_end_date();

		$subscription->next_payment_date          = SubscriptionHelper::calculate_next_payment_date( $subscription );
		$subscription->next_payment_delivery_date = SubscriptionHelper::calculate_next_payment_delivery_date( $subscription );

		// Unset next payment date if this is the last payment according to subscription end date.
		if ( null !== $subscription->end_date && $subscription->next_payment_date >= $subscription->end_date ) {
			$subscription->next_payment_date = null;
		}

		// Delete next payment post meta if not set.
		if ( null === $subscription->next_payment_date ) {
			$subscription->next_payment_delivery_date = null;

			$subscription->set_meta( 'next_payment', null );
			$subscription->set_meta( 'next_payment_delivery_date', null );
		}

		$payment->start_date = $start_date;
		$payment->end_date   = $end_date;

		// Update subscription.
		$subscription->save();

		// Start payment.
		$payment = Plugin::start_payment( $payment );

		return $payment;
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

		// Check recurring.
		if ( ! $payment->get_recurring() ) {
			return false;
		}

		// Check source.
		if ( \in_array( $payment->get_source(), array( 'woocommerce' ), true ) ) {
			return false;
		}

		// Check periods.
		$periods = $payment->get_periods();

		if ( null === $periods ) {
			return false;
		}

		// Check for pending and successful child payments.
		$payments = \get_pronamic_payments_by_meta( '', '', array( 'post_parent' => $payment->get_id() ) );

		foreach ( $payments as $child_payment ) {
			if ( \in_array( $child_payment->get_status(), array( PaymentStatus::OPEN, PaymentStatus::SUCCESS ), true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * New payment based on period.
	 *
	 * @param SubscriptionPeriod $period Subscription period.
	 * @return Payment
	 * @throws \Exception Throws exception if gateway integration can not be found.
	 */
	public function new_period_payment( SubscriptionPeriod $period ) {
		$subscription = $period->get_phase()->get_subscription();

		$config_id = (int) $subscription->get_config_id();

		$integration_id = \get_post_meta( $config_id, '_pronamic_gateway_id', true );

		$integration = $this->plugin->gateway_integrations->get_integration( $integration_id );

		if ( null === $integration ) {
			throw new \Exception( 'Gateway integration could not be found while creating new subscription period payment.' );
		}

		$config = $integration->get_config( $config_id );

		if ( null === $config ) {
			throw new \Exception( 'Config could not be found while creating new subscription period payment.' );
		}

		$payment = new Payment();

		$payment->email           = $subscription->get_email();
		$payment->method          = $subscription->payment_method;
		$payment->issuer          = $subscription->get_issuer();
		$payment->recurring       = true;
		$payment->subscription    = $subscription;
		$payment->subscription_id = $subscription->get_id();

		$payment->set_description( $subscription->get_description() );
		$payment->set_config_id( $config_id );
		$payment->set_origin_id( $subscription->get_origin_id() );
		$payment->set_mode( $config->mode );

		$payment->set_source( $subscription->get_source() );
		$payment->set_source_id( $subscription->get_source_id() );

		$payment->set_customer( $subscription->get_customer() );
		$payment->set_billing_address( $subscription->get_billing_address() );
		$payment->set_shipping_address( $subscription->get_shipping_address() );

		$payment->add_period( $period );
		$payment->set_start_date( $period->get_start_date() );
		$payment->set_end_date( $period->get_end_date() );

		$payment->set_lines( $subscription->get_lines() );
		$payment->set_total_amount( $period->get_phase()->get_amount() );

		return $payment;
	}

	/**
	 * Start payment for next period.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return Payment|null
	 */
	public function start_next_period_payment( Subscription $subscription ) {
		$next_period = $subscription->new_period();

		if ( null === $next_period ) {
			return null;
		}

		// Start payment for next period.
		$payment = null;

		try {
			$payment = $this->new_period_payment( $next_period );

			$this->start_payment( $payment );
		} catch ( \Exception $e ) {
			Plugin::render_exception( $e );

			exit;
		}

		return $payment;
	}

	/**
	 * Retry a payment by starting a payment for each period of given payment.
	 *
	 * @param Payment $payment Payment.
	 * @return array<int, Payment>|null
	 */
	public function retry_payment( Payment $payment ) {
		// Check if payment can be retried.
		if ( ! $this->can_retry_payment( $payment ) ) {
			return null;
		}

		// Check periods.
		$periods = $payment->get_periods();

		if ( null === $periods ) {
			return null;
		}

		// Start new payment for period.
		$payments = array();

		foreach ( $periods as $period ) {
			try {
				$period_payment = $this->new_period_payment( $period );

				$period_payment->set_source( $payment->get_source() );
				$period_payment->set_source_id( $payment->get_source_id() );

				$period_payment = $this->start_payment( $period_payment );

				$payments[] = $period_payment;
			} catch ( \Exception $e ) {
				Plugin::render_exception( $e );

				exit;
			}
		}

		return $payments;
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
	 * Maybe schedule subscription payments.
	 *
	 * @return void
	 */
	public function maybe_schedule_subscription_events() {
		if ( ! wp_next_scheduled( 'pronamic_pay_update_subscription_payments' ) ) {
			wp_schedule_event( time(), 'hourly', 'pronamic_pay_update_subscription_payments' );
		}

		if ( ! wp_next_scheduled( 'pronamic_pay_complete_subscriptions' ) ) {
			wp_schedule_event( time(), 'hourly', 'pronamic_pay_complete_subscriptions' );
		}
	}

	/**
	 * Maybe create subscription for the specified payment.
	 *
	 * @param Payment $payment The new payment.
	 *
	 * @return void
	 *
	 * @throws \UnexpectedValueException Throw unexpected value exception if the subscription does not have a valid date interval.
	 */
	public function maybe_create_subscription( $payment ) {
		// Check if there is already subscription attached to the payment.
		$subscription_id = $payment->get_subscription_id();

		if ( ! empty( $subscription_id ) ) {
			// Subscription already created.
			return;
		}

		// Check if there is a subscription object attached to the payment.
		$subscription = $payment->subscription;

		if ( empty( $subscription ) ) {
			return;
		}

		// Complement subscription.
		SubscriptionHelper::complement_subscription( $subscription );
		SubscriptionHelper::complement_subscription_by_payment( $subscription, $payment );
		SubscriptionHelper::complement_subscription_dates( $subscription );

		// Create.
		$result = $this->plugin->subscriptions_data_store->create( $subscription );

		if ( $result ) {
			$payment->subscription    = $subscription;
			$payment->subscription_id = $subscription->get_id();

			$payment->recurring_type = Recurring::FIRST;

			$start_date = $subscription->get_start_date();
			$end_date   = $subscription->get_next_payment_date();

			$payment->start_date = ( null === $start_date ) ? null : clone $start_date;
			$payment->end_date   = ( null === $end_date ) ? null : clone $end_date;

			$payment->save();
		}
	}

	/**
	 * Get expiring subscriptions.
	 *
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L715-L746
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L652-L712
	 *
	 * @param DateTime $start_date The start date of the period to check for expiring subscriptions.
	 * @param DateTime $end_date   The end date of the period to check for expiring subscriptions.
	 * @return array
	 */
	public function get_expiring_subscription_posts( DateTime $start_date, DateTime $end_date ) {
		$args = array(
			'post_type'   => 'pronamic_pay_subscr',
			'nopaging'    => true,
			'orderby'     => 'post_date',
			'order'       => 'ASC',
			'post_status' => array(
				'subscr_pending',
				'subscr_failed',
				'subscr_active',
			),
			'meta_query'  => array(
				array(
					'key'     => '_pronamic_subscription_expiry_date',
					'value'   => array(
						$start_date->format( DateTime::MYSQL ),
						$end_date->format( DateTime::MYSQL ),
					),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				),
			),
		);

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Payment status update.
	 *
	 * @param Payment $payment The status updated payment.
	 * @return void
	 */
	public function payment_status_update( $payment ) {
		// Check if the payment is connected to a subscription.
		$subscription = $payment->get_subscription();

		if ( empty( $subscription ) || null === $subscription->get_id() ) {
			// Payment not connected to a subscription, nothing to do.
			return;
		}

		// Status.
		$status_before = $subscription->get_status();
		$status_update = $status_before;

		switch ( $payment->get_status() ) {
			case PaymentStatus::OPEN:
				// @todo
				break;
			case PaymentStatus::SUCCESS:
				$status_update = SubscriptionStatus::ACTIVE;

				if ( isset( $subscription->expiry_date, $payment->end_date ) && $subscription->expiry_date < $payment->end_date ) {
					$subscription->expiry_date = clone $payment->end_date;
				}

				break;
			case PaymentStatus::FAILURE:
				/**
				 * Subscription status for failed payment.
				 *
				 * @todo Determine update status based on reason of failed payment. Use `failure` for now as that is usually the desired status.
				 *
				 * @link https://www.europeanpaymentscouncil.eu/document-library/guidance-documents/guidance-reason-codes-sepa-direct-debit-r-transactions
				 * @link https://github.com/pronamic/wp-pronamic-ideal/commit/48449417eac49eb6a93480e3b523a396c7db9b3d#diff-6712c698c6b38adfa7190a4be983a093
				 */
				$status_update = SubscriptionStatus::FAILURE;

				break;
			case PaymentStatus::CANCELLED:
			case PaymentStatus::EXPIRED:
				$first_payment = $subscription->get_first_payment();

				// Set subscription status to 'On Hold' only if the subscription is not already active when processing the first payment.
				if ( ! ( null !== $first_payment && $first_payment->get_id() === $payment->get_id() && SubscriptionStatus::ACTIVE === $subscription->get_status() ) ) {
					$status_update = SubscriptionStatus::ON_HOLD;
				}

				break;
		}

		// The status of canceled or completed subscriptions will not be changed automatically.
		if ( ! in_array( $status_before, array( SubscriptionStatus::CANCELLED, SubscriptionStatus::COMPLETED, SubscriptionStatus::ON_HOLD ), true ) ) {
			$subscription->set_status( $status_update );
		}

		// Update.
		$subscription->save();
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
	 * Send renewal notices.
	 *
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L652-L712
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L715-L746
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/classes/class-sl-emails.php#L41-L126
	 *
	 * @return void
	 *
	 * @throws \Exception Throws exception on start date error.
	 */
	public function send_subscription_renewal_notices() {
		$interval = new DateInterval( 'P1W' ); // 1 week

		$start_date = new DateTime( 'midnight', new DateTimeZone( 'UTC' ) );

		$end_date = clone $start_date;
		$end_date->add( $interval );

		$expiring_subscription_posts = $this->get_expiring_subscription_posts( $start_date, $end_date );

		foreach ( $expiring_subscription_posts as $post ) {
			$subscription = new Subscription( $post->ID );

			// If expiry date is null we continue, subscription is not expiring.
			$expiry_date = $subscription->get_expiry_date();

			if ( null === $expiry_date ) {
				continue;
			}

			// Date interval.
			$date_interval = $subscription->get_date_interval();

			if ( null === $date_interval ) {
				continue;
			}

			$sent_date_string = get_post_meta( $post->ID, '_pronamic_subscription_renewal_sent_1week', true );

			if ( $sent_date_string ) {
				$first_date = clone $expiry_date;
				$first_date->sub( $date_interval );

				$sent_date = new DateTime( $sent_date_string, new DateTimeZone( 'UTC' ) );

				if ( $sent_date >= $first_date || $expiry_date < $subscription->get_next_payment_date() ) {
					// Prevent renewal notices from being sent more than once.
					continue;
				}

				delete_post_meta( $post->ID, '_pronamic_subscription_renewal_sent_1week' );
			}

			// Add renewal notice payment note.
			$note = sprintf(
				/* translators: %s: expiry date */
				__( 'Subscription renewal due on %s.', 'pronamic_ideal' ),
				$expiry_date->format_i18n()
			);

			$subscription->add_note( $note );

			// Send renewal notice.
			do_action( 'pronamic_subscription_renewal_notice_' . $subscription->get_source(), $subscription );

			// Update renewal notice sent date meta.
			$renewal_sent_date = clone $start_date;

			$renewal_sent_date->setTime(
				intval( $expiry_date->format( 'H' ) ),
				intval( $expiry_date->format( 'i' ) ),
				intval( $expiry_date->format( 's' ) )
			);

			update_post_meta( $post->ID, '_pronamic_subscription_renewal_sent_1week', $renewal_sent_date->format( DateTime::MYSQL ) );
		}
	}

	/**
	 * Update subscription payments.
	 *
	 * @param bool $cli_test Whether or not this a CLI test.
	 * @return void
	 */
	public function update_subscription_payments( $cli_test = false ) {
		$this->send_subscription_renewal_notices();

		$args = array(
			'post_type'   => 'pronamic_pay_subscr',
			'nopaging'    => true,
			'orderby'     => 'post_date',
			'order'       => 'ASC',
			'post_status' => array(
				'subscr_pending',
				'subscr_failed',
				'subscr_active',
			),
			'meta_query'  => array(
				array(
					'key'     => '_pronamic_subscription_source',
					'compare' => 'NOT IN',
					'value'   => array(
						// Don't create payments for sources which schedule payments.
						'woocommerce',
					),
				),
			),
		);

		if ( ! $cli_test ) {
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => '_pronamic_subscription_next_payment',
					'compare' => '<=',
					'value'   => current_time( 'mysql', true ),
					'type'    => 'DATETIME',
				),
				array(
					'key'     => '_pronamic_subscription_next_payment_delivery_date',
					'compare' => '<=',
					'value'   => current_time( 'mysql', true ),
					'type'    => 'DATETIME',
				),
			);
		}

		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			if ( $cli_test ) {
				WP_CLI::log( sprintf( 'Processing post `%d` - "%s"…', $post->ID, get_the_title( $post ) ) );
			}

			$subscription = new Subscription( $post->ID );

			$gateway = Plugin::get_gateway( $subscription->config_id );

			// If gateway is null we continue to next subscription.
			if ( null === $gateway ) {
				continue;
			}

			// Start payment.
			try {
				$payment = $this->new_subscription_payment( $subscription );

				$payment->recurring = true;

				$payment = $this->start_payment( $payment );
			} catch ( \Exception $e ) {
				if ( $cli_test ) {
					WP_CLI::error( $e->getMessage(), false );
				}

				continue;
			}

			if ( is_object( $payment ) ) {
				// Update payment.
				Plugin::update_payment( $payment, false );
			}

			// Expire manual renewal subscriptions.
			if ( ! $gateway->supports( 'recurring' ) ) {
				$now = new DateTime();

				if ( PaymentStatus::COMPLETED !== $subscription->status && isset( $subscription->expiry_date ) && $subscription->expiry_date <= $now ) {
					$subscription->status = PaymentStatus::EXPIRED;

					$subscription->save();

					// Delete next payment date so it won't get used as start date
					// of the new payment period when manually renewing and to keep
					// the subscription out of updating subscription payments (this method).
					$subscription->set_meta( 'next_payment', null );
				}
			}
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
			$args['meta_query'][1][] = array(
				'key'     => '_pronamic_subscription_end_date',
				'compare' => '<=',
				'value'   => current_time( 'mysql', true ),
				'type'    => 'DATETIME',
			);
		}

		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			if ( $cli_test ) {
				WP_CLI::log( sprintf( 'Processing post `%d` - "%s"…', $post->ID, get_the_title( $post ) ) );
			}

			// Complete subscription.
			try {
				$subscription = new Subscription( $post->ID );

				$subscription->status      = SubscriptionStatus::COMPLETED;
				$subscription->expiry_date = $subscription->end_date;

				$subscription->save();
			} catch ( \Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * CLI subscriptions test.
	 *
	 * @return void
	 */
	public function cli_subscriptions_test() {
		$cli_test = true;

		WP_CLI::log( 'Updating subscription payments…' );

		$this->update_subscription_payments( $cli_test );

		WP_CLI::log( 'Completing subscriptions…' );

		$this->complete_subscriptions( $cli_test );

		WP_CLI::success( 'Pronamic Pay subscriptions test.' );
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
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_subscription' ),
				'permission_callback' => function() {
					return \current_user_can( 'edit_payments' );
				},
				'args'                => array(
					'subscription_id' => array(
						'description' => __( 'Subscription ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					),
				),
			)
		);

		\register_rest_route(
			'pronamic-pay/v1',
			'/subscriptions/(?P<subscription_id>\d+)/phases/(?P<sequence_number>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_subscription_phase' ),
				'permission_callback' => function() {
					return \current_user_can( 'edit_payments' );
				},
				'args'                => array(
					'subscription_id' => array(
						'description' => __( 'Subscription ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					),
					'sequence_number' => array(
						'description' => __( 'Subscription phase sequence number.', 'pronamic_ideal' ),
						'type'        => 'integer',
					),
				),
			)
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
}
