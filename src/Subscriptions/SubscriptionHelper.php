<?php
/**
 * Subscription Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
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

			$subscription->set_customer( $customer );
		}

		// Origin.
		if ( null === $subscription->get_origin_id() ) {
			$subscription->set_origin_id( $payment->get_origin_id() );
		}

		// Source.
		if ( empty( $subscription->source ) && empty( $subscription->source_id ) ) {
			$subscription->source    = $payment->source;
			$subscription->source_id = $payment->subscription_source_id;
		}

		// Description.
		if ( null === $subscription->description ) {
			$subscription->description = $payment->description;
		}

		// Email.
		$email = $customer->get_email();

		if ( null === $email ) {
			$customer->set_email( $payment->email );

			$subscription->set_customer( $customer );
		}

		// Payment method.
		if ( null === $subscription->payment_method ) {
			$subscription->payment_method = $payment->method;
		}

		// Start date.
		if ( null === $subscription->start_date ) {
			$subscription->start_date = clone $payment->date;
		}
	}

	/**
	 * Complement subscription dates.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 */
	public static function complement_subscription_dates( Subscription $subscription ) {
		// End date.
		if ( null === $subscription->end_date ) {
			$subscription->set_end_date( self::calculate_end_date( $subscription ) );
		}

		// Expiry date.
		if ( null === $subscription->expiry_date ) {
			$subscription->set_expiry_date( self::calculate_expiry_date( $subscription ) );
		}

		// Next payment date.
		if ( null === $subscription->next_payment_date ) {
			$subscription->set_next_payment_date( self::calculate_next_payment_date( $subscription ) );
		}

		// Next payment delivery date.
		if ( null === $subscription->next_payment_delivery_date ) {
			$subscription->set_next_payment_delivery_date( self::calculate_next_payment_delivery_date( $subscription ) );
		}
	}

	/**
	 * Calculate end date of subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return DateTime|null
	 */
	public static function calculate_end_date( Subscription $subscription ) {
		$end_date = null;

		$phase = $subscription->get_current_phase();

		if ( null !== $phase ) {
			$phase_end = $phase->get_end_date();

			if ( null !== $phase_end ) {
				$end_date = new DateTime( $phase_end->format( \DATE_ATOM ) );
			}
		}

		return $end_date;
	}

	/**
	 * Calculate expirty date.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return DateTime|null
	 */
	public static function calculate_expiry_date( Subscription $subscription ) {
		$start_date = $subscription->get_start_date();

		if ( null === $start_date ) {
			return null;
		}

		$expiry_date = clone $start_date;

		return $expiry_date;
	}

	/**
	 * Calculate next payment date.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return DateTime|null
	 * @throws \InvalidArgumentException Throws invalid argument exception if no phases are defined.
	 */
	public static function calculate_next_payment_date( Subscription $subscription ) {
		$phases = $subscription->get_phases();

		if ( empty( $phases ) ) {
			throw new \InvalidArgumentException( 'Can not calculate next payment date of subscription without phases.' );
		}

		// Get current phase.
		$phase = $subscription->get_current_phase();

		if ( null === $phase ) {
			return null;
		}

		// Get next payment date.
		$next_date = $phase->get_next_date();

		if ( null === $next_date ) {
			return null;
		}

		$next_date = new DateTime( $next_date->format( \DATE_ATOM ) );

		return $next_date;
	}

	/**
	 * Calculate next payment delivery date.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return DateTime|null
	 */
	public static function calculate_next_payment_delivery_date( $subscription ) {
		$next_payment_date = $subscription->get_next_payment_date();

		// Check if there is next payment date.
		if ( null === $next_payment_date ) {
			return null;
		}

		$next_payment_delivery_date = clone $next_payment_date;

		/**
		 * Filters the subscription next payment delivery date.
		 *
		 * @since unreleased
		 *
		 * @param DateTime     $next_payment_delivery_date Next payment delivery date.
		 * @param Subscription $subscription               Subscription.
		 */
		$next_payment_delivery_date = \apply_filters( 'pronamic_pay_subscription_next_payment_delivery_date', $next_payment_delivery_date, $subscription );

		return $next_payment_delivery_date;
	}
}
