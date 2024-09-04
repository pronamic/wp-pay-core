<?php
/**
 * Subscription Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Subscription Helper
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.4.0
 */
class SubscriptionHelper {
	/**
	 * Complement subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 */
	public static function complement_subscription( Subscription $subscription ) {
		// Key.
		if ( null === $subscription->key ) {
			$subscription->key = uniqid( 'subscr_' );
		}

		// Status.
		if ( null === $subscription->status ) {
			$subscription->status = PaymentStatus::OPEN;
		}
	}

	/**
	 * Complement subscription by payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @param Payment      $payment      Payment.
	 * @return void
	 */
	public static function complement_subscription_by_payment( Subscription $subscription, Payment $payment ) {
		// Gateway configuration ID.
		if ( null === $subscription->config_id ) {
			$subscription->config_id = $payment->config_id;
		}

		// Title.
		if ( null === $subscription->title ) {
			$subscription->title = sprintf(
				/* translators: %s: payment title */
				__( 'Subscription for %s', 'pronamic_ideal' ),
				$payment->title
			);
		}

		$customer = $subscription->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();
		}

		// Customer.
		$payment_customer = $payment->get_customer();

		if ( null !== $payment_customer ) {
			// Contact name.
			$customer_name = $customer->get_name();

			if ( null === $customer_name ) {
				$customer->set_name( $payment_customer->get_name() );
			}

			// WordPress user ID.
			$user_id = $customer->get_user_id();

			if ( null === $user_id ) {
				$customer->set_user_id( $payment_customer->get_user_id() );
			}

			// Email.
			$email = $customer->get_email();

			if ( null === $email ) {
				$customer->set_email( $payment_customer->get_email() );
			}

			$subscription->set_customer( $customer );
		}

		// Origin.
		if ( null === $subscription->get_origin_id() ) {
			$subscription->set_origin_id( $payment->get_origin_id() );
		}

		// Source.
		if ( empty( $subscription->source ) ) {
			$subscription->source = $payment->source;
		}

		// Source ID.
		if ( empty( $subscription->source_id ) ) {
			$subscription->source_id = $payment->source_id;
		}

		// Description.
		$description = $subscription->get_description();

		if ( null === $description ) {
			$subscription->set_description( $payment->get_description() );
		}

		// Payment method.
		$payment_method = $subscription->get_payment_method();

		if ( null === $payment_method ) {
			$subscription->set_payment_method( $payment->get_payment_method() );
		}
	}
}
