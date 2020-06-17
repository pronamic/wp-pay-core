<?php
/**
 * Subscription Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscription Helper
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class SubscriptionHelper {
	/**
	 * Calculate end date of subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return \DateTimeInterface|null
	 */
	public static function calculate_end_date( Subscription $subscription ) {
		$start_date = $subscription->get_start_date();

		if ( null === $start_date ) {
			return null;
		}

		$date_interval = $subscription->get_date_interval();

		if ( null === $date_interval ) {
			return null;
		}

		if ( null === $subscription->frequency ) {
			return null;
		}

		// @link https://stackoverflow.com/a/10818981/6411283
		$period = new \DatePeriod( $start_date, $date_interval, $subscription->frequency );

		$dates = iterator_to_array( $period );

		$end_date = end( $dates );

		if ( 'last' === $subscription->get_interval_date() ) {
			$end_date->modify( 'last day of ' . $end_date->format( 'F Y' ) );
		}

		return $end_date;
	}

	/**
	 * Calculate expirty date.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return \DateTimeInterface|null
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
	 * @return \DateTimeInterface|null
	 */
	public static function calculate_next_payment_date( Subscription $subscription ) {
		$start_date = $subscription->get_start_date();

		if ( null === $start_date ) {
			throw new \InvalidArgumentException( 'Can not calculate next payment date of subscription without start date.' );
		}

		$date_interval = $subscription->get_date_interval();

		if ( null === $date_interval ) {
			throw new \InvalidArgumentException( 'Can not calculate next payment date of subscription without date interval.' );
		}

		$next_date = clone $start_date;

		$next_date->add( $date_interval );

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
					$day = $next_date->format( 'd' );

					if ( \is_numeric( $interval_date ) ) {
						$day = $interval_date;
					}

					$next_date->setDate(
						intval( $next_date->format( 'Y' ) ),
						intval( $interval_date_month ),
						intval( $day )
					);

					$next_date->setTime( 0, 0 );

					if ( 'last' === $interval_date ) {
						$next_date->modify( 'last day of ' . $next_date->format( 'F Y' ) );
					}
				}

				break;
		}

		return $next_date;
	}

	/**
	 * Calculate next payment delivery date.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return \DateTimeInterface
	 */
	public static function calculate_next_payment_delivery_date( $subscription ) {
		$next_payment_date = $subscription->get_next_payment_date();

		if ( null === $next_payment_date ) {
			throw new \InvalidArgumentException( 'Can not calculate next payment delivery date of subscription without next payment date.' );
		}

		$next_payment_delivery_date = clone $next_payment_date;

		$next_payment_delivery_date = \apply_filters( 'pronamic_pay_subscription_next_payment_delivery_date', $next_payment_delivery_date, $subscription );

		return $next_payment_delivery_date;
	}
}
