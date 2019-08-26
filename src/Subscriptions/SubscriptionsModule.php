<?php
/**
 * Subscriptions Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateInterval;
use DatePeriod;
use Exception;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\Recurring;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use UnexpectedValueException;
use WP_CLI;
use WP_Error;
use WP_Query;

/**
 * Title: Subscriptions module
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @link https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.1.0
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

		add_action( 'plugins_loaded', array( $this, 'maybe_schedule_subscription_payments' ), 6 );

		// Exclude subscription notes.
		add_filter( 'comments_clauses', array( $this, 'exclude_subscription_comment_notes' ), 10, 2 );

		add_action( 'pronamic_pay_new_payment', array( $this, 'maybe_create_subscription' ) );

		// The 'pronamic_pay_update_subscription_payments' hook adds subscription payments and sends renewal notices.
		add_action( 'pronamic_pay_update_subscription_payments', array( $this, 'update_subscription_payments' ) );

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
	}

	/**
	 * Handle subscription actions.
	 *
	 * Extensions like Gravity Forms can send action links in for example
	 * email notifications so users can cancel or renew their subscription.
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
				return $this->handle_subscription_cancel( $subscription );
			case 'renew':
				return $this->handle_subscription_renew( $subscription );
		}
	}

	/**
	 * Handle cancel subscription action request.
	 *
	 * @param Subscription $subscription Subscription to cancel.
	 */
	private function handle_subscription_cancel( Subscription $subscription ) {
		if ( Statuses::CANCELLED !== $subscription->get_status() ) {
			$subscription->set_status( Statuses::CANCELLED );

			$subscription->save();
		}

		wp_safe_redirect( home_url() );

		exit;
	}

	/**
	 * Handle renew subscription action request.
	 *
	 * @param Subscription $subscription Subscription to renew.
	 */
	private function handle_subscription_renew( Subscription $subscription ) {
		$gateway = Plugin::get_gateway( $subscription->config_id );

		if ( empty( $gateway ) ) {
			require __DIR__ . '/../../views/subscription-renew-failed.php';

			exit;
		}

		if ( 'POST' === Server::get( 'REQUEST_METHOD' ) ) {
			$payment = $this->start_recurring( $subscription, $gateway, false );

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
	 * Create a new subscription payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return false|Payment
	 */
	public function new_subscription_payment( Subscription $subscription ) {
		// Unset the next payment date if next payment date is after the subscription end date.
		if ( isset( $subscription->end_date, $subscription->next_payment_date ) && $subscription->next_payment_date > $subscription->end_date ) {
			$subscription->next_payment_date = null;
		}

		// Set the subscription status to `completed` if there is no next payment date.
		if ( empty( $subscription->next_payment_date ) ) {
			$subscription->status                     = Statuses::COMPLETED;
			$subscription->expiry_date                = $subscription->end_date;
			$subscription->next_payment_delivery_date = null;

			$subscription->save();

			return false;
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

		$payment->set_total_amount( $subscription->get_total_amount() );
		$payment->set_customer( $subscription->get_customer() );
		$payment->set_billing_address( $subscription->get_billing_address() );
		$payment->set_shipping_address( $subscription->get_shipping_address() );
		$payment->set_lines( $subscription->get_lines() );

		return $payment;
	}

	/**
	 * Start a recurring payment at the specified gateway for the specified subscription.
	 *
	 * @param Subscription $subscription The subscription to start a recurring payment for.
	 * @param Gateway|null $gateway      The gateway to start the recurring payment at.
	 * @param bool         $recurring    Recurring.
	 * @return Payment|null
	 * @throws \Exception Throws an Exception on incorrect date interval.
	 */
	public function start_recurring( Subscription $subscription, $gateway = null, $recurring = true ) {
		$payment = $this->new_subscription_payment( $subscription );

		if ( empty( $payment ) ) {
			return null;
		}

		$payment->recurring = $recurring;

		// Make sure to only start payments for supported gateways.
		if ( null === $gateway ) {
			$gateway = Plugin::get_gateway( $payment->get_config_id() );
		}

		if ( false === $payment->get_recurring() && ( ! $gateway || ! $gateway->supports( 'recurring' ) ) ) {
			// @todo
			return null;
		}

		// Start payment.
		return $this->start_payment( $payment, $gateway );
	}

	/**
	 * Start payment.
	 *
	 * @param Payment      $payment Payment.
	 * @param Gateway|null $gateway Gateway to start the recurring payment at.
	 *
	 * @throws UnexpectedValueException Throw unexpected value exception when no subscription was found in payment.
	 *
	 * @return Payment
	 */
	public function start_payment( Payment $payment, $gateway = null ) {
		// Set recurring type.
		if ( $payment->get_recurring() ) {
			$payment->recurring_type = Recurring::RECURRING;
		}

		$subscription = $payment->get_subscription();

		if ( empty( $subscription ) ) {
			throw new UnexpectedValueException( 'No subscription object found in payment.' );
		}

		// Calculate payment start and end dates.
		$start_date = new DateTime();

		if ( ! empty( $subscription->next_payment_date ) ) {
			$start_date = clone $subscription->next_payment_date;
		}

		$interval = $subscription->get_date_interval();

		if ( null === $interval ) {
			throw new UnexpectedValueException( 'Cannot start a follow-up payment for payment because the subscription does not have a valid date interval.' );
		}

		$end_date = clone $start_date;
		$end_date->add( $interval );

		if ( 'last' === $subscription->get_interval_date() ) {
			$end_date->modify( 'last day of ' . $end_date->format( 'F Y' ) );
		}

		$subscription->next_payment_date          = $end_date;
		$subscription->next_payment_delivery_date = apply_filters( 'pronamic_pay_subscription_next_payment_delivery_date', clone $end_date, $payment );

		$payment->start_date = $start_date;
		$payment->end_date   = $end_date;

		// Start payment.
		$payment = Plugin::start_payment( $payment, $gateway );

		// Update subscription.
		$subscription->save();

		return $payment;
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
	public function maybe_schedule_subscription_payments() {
		if ( wp_next_scheduled( 'pronamic_pay_update_subscription_payments' ) ) {
			return;
		}

		wp_schedule_event( time(), 'hourly', 'pronamic_pay_update_subscription_payments' );
	}

	/**
	 * Maybe create subscription for the specified payment.
	 *
	 * @param Payment $payment The new payment.
	 *
	 * @return void
	 *
	 * @throws UnexpectedValueException Throw unexpected value exception if the subscription does not have a valid date interval.
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

		// Customer.
		$user_id       = null;
		$customer_name = null;

		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$user_id = $customer->get_user_id();
			$name    = $customer->get_name();

			if ( null !== $name ) {
				$customer_name = strval( $name );
			}
		}

		// Complement subscription.
		$subscription->config_id = $payment->config_id;
		$subscription->user_id   = $user_id;
		$subscription->title     = sprintf(
			/* translators: %s: payment title */
			__( 'Subscription for %s', 'pronamic_ideal' ),
			$payment->title
		);

		$subscription->key = uniqid( 'subscr_' );

		if ( empty( $subscription->source ) && empty( $subscription->source_id ) ) {
			$subscription->source    = $payment->source;
			$subscription->source_id = $payment->subscription_source_id;
		}

		$subscription->description    = $payment->description;
		$subscription->email          = $payment->email;
		$subscription->customer_name  = $customer_name;
		$subscription->payment_method = $payment->method;
		$subscription->status         = Statuses::OPEN;

		// @todo
		// Calculate dates
		// @link https://github.com/pronamic/wp-pronamic-ideal/blob/4.7.0/classes/Pronamic/WP/Pay/Plugin.php#L883-L964
		$interval = $subscription->get_date_interval();

		if ( null === $interval ) {
			throw new UnexpectedValueException( 'Cannot create a subscription for payment because the subscription does not have a valid date interval.' );
		}

		$start_date  = clone $payment->date;
		$expiry_date = clone $start_date;

		$next_date = clone $start_date;
		$next_date->add( $interval );

		$interval_date       = $subscription->get_interval_date();
		$interval_date_day   = $subscription->get_interval_date_day();
		$interval_date_month = $subscription->get_interval_date_month();

		switch ( $subscription->interval_period ) {
			case 'W':
				if ( is_numeric( $interval_date_day ) ) {
					$days_delta = (int) $interval_date_day - (int) $next_date->format( 'w' );

					$next_date->modify( sprintf( '+%s days', $days_delta ) );
					$next_date->setTime( 0, 0 );
				}

				break;
			case 'M':
				if ( is_numeric( $interval_date ) ) {
					$next_date->setDate(
						intval( $next_date->format( 'Y' ) ),
						intval( $next_date->format( 'm' ) ),
						intval( $interval_date )
					);

					$next_date->setTime( 0, 0 );
				} elseif ( 'last' === $interval_date ) {
					$next_date->modify( 'last day of ' . $next_date->format( 'F Y' ) );
					$next_date->setTime( 0, 0 );
				}

				break;
			case 'Y':
				if ( is_numeric( $interval_date_month ) ) {
					$next_date->setDate(
						intval( $next_date->format( 'Y' ) ),
						intval( $interval_date_month ),
						intval( $next_date->format( 'd' ) )
					);

					$next_date->setTime( 0, 0 );

					if ( 'last' === $interval_date ) {
						$next_date->modify( 'last day of ' . $next_date->format( 'F Y' ) );
					}
				}

				break;
		}

		$end_date = null;

		if ( null !== $subscription->frequency ) {
			// @link https://stackoverflow.com/a/10818981/6411283
			$period = new DatePeriod( $start_date, $interval, $subscription->frequency );

			$dates = iterator_to_array( $period );

			$end_date = end( $dates );

			if ( 'last' === $subscription->get_interval_date() ) {
				$end_date->modify( 'last day of ' . $end_date->format( 'F Y' ) );
			}
		}

		$subscription->start_date                 = $start_date;
		$subscription->end_date                   = $end_date;
		$subscription->expiry_date                = $expiry_date;
		$subscription->next_payment_date          = $next_date;
		$subscription->next_payment_delivery_date = apply_filters( 'pronamic_pay_subscription_next_payment_delivery_date', clone $next_date, $payment );

		// Create.
		$result = $this->plugin->subscriptions_data_store->create( $subscription );

		if ( $result ) {
			$payment->subscription    = $subscription;
			$payment->subscription_id = $subscription->get_id();

			$payment->recurring_type = Recurring::FIRST;
			$payment->start_date     = $start_date;
			$payment->end_date       = $next_date;

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
				'subscr_expired',
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
			case Statuses::OPEN:
				// @todo
				break;
			case Statuses::SUCCESS:
				$status_update = Statuses::ACTIVE;

				if ( isset( $subscription->expiry_date, $payment->end_date ) && $subscription->expiry_date < $payment->end_date ) {
					$subscription->expiry_date = clone $payment->end_date;
				}

				break;
			case Statuses::FAILURE:
				/**
				 * Subscription status for failed payment.
				 *
				 * @todo Determine update status based on reason of failed payment. Use `failure` for now as that is usually the desired status.
				 *
				 * @link https://www.europeanpaymentscouncil.eu/document-library/guidance-documents/guidance-reason-codes-sepa-direct-debit-r-transactions
				 * @link https://github.com/pronamic/wp-pronamic-ideal/commit/48449417eac49eb6a93480e3b523a396c7db9b3d#diff-6712c698c6b38adfa7190a4be983a093
				 */
				$status_update = Statuses::FAILURE;

				break;
			case Statuses::CANCELLED:
			case Statuses::EXPIRED:
				$status_update = Statuses::CANCELLED;

				break;
		}

		// The status of canceled or completed subscriptions will not be changed automatically.
		if ( ! in_array( $status_before, array( Statuses::CANCELLED, Statuses::COMPLETED ), true ) ) {
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

		$subscription->add_note( $note );
	}

	/**
	 * Send renewal notices.
	 *
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L652-L712
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/license-renewals.php#L715-L746
	 * @link https://github.com/wp-premium/edd-software-licensing/blob/3.5.23/includes/classes/class-sl-emails.php#L41-L126
	 *
	 * @return void
	 */
	public function send_subscription_renewal_notices() {
		$interval = new DateInterval( 'P1W' ); // 1 week

		$start_date = new DateTime( 'midnight', new DateTimeZone( 'UTC' ) );

		$end_date = clone $start_date;
		$end_date->add( $interval );

		$expiring_subscription_posts = $this->get_expiring_subscription_posts( $start_date, $end_date );

		foreach ( $expiring_subscription_posts as $post ) {
			$subscription = new Subscription( $post->ID );

			// If expirary date is null we continue, subscription is not expiring.
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
				'subscr_expired',
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
			$payment = $this->start_recurring( $subscription, $gateway );

			if ( is_object( $payment ) ) {
				// Update payment.
				Plugin::update_payment( $payment, false );
			}

			// Expire manual renewal subscriptions.
			if ( ! $gateway->supports( 'recurring' ) ) {
				$now = new DateTime();

				if ( Statuses::COMPLETED !== $subscription->status && isset( $subscription->expiry_date ) && $subscription->expiry_date <= $now ) {
					$subscription->status = Statuses::EXPIRED;

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
	 * CLI subscriptions test.
	 */
	public function cli_subscriptions_test() {
		$cli_test = true;

		$this->update_subscription_payments( $cli_test );

		WP_CLI::success( 'Pronamic Pay subscriptions test.' );
	}
}
