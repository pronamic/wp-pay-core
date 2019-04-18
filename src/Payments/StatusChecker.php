<?php
/**
 * Status Checker
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Status Checker
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   1.0.0
 */
class StatusChecker {
	/**
	 * Construct a status checker.
	 */
	public function __construct() {
		// Payment status check events are scheduled when payments are started.
		add_action( 'pronamic_pay_payment_status_check', array( $this, 'check_status' ), 10, 2 );

		// Deprecated `pronamic_ideal_check_transaction_status` hooks got scheduled to request the payment status.
		add_action( 'pronamic_ideal_check_transaction_status', array( $this, 'check_transaction_status' ), 10, 3 );

		// Clear scheduled status check once payment reaches a final status.
		add_action( 'pronamic_payment_status_update', array( $this, 'maybe_clear_scheduled_status_check' ), 10, 1 );
	}

	/**
	 * Schedule event.
	 *
	 * @param Payment $payment The payment to schedule the status check event.
	 *
	 * @return void
	 */
	public static function schedule_event( $payment ) {
		/*
		 * Schedule status requests
		 * http://pronamic.nl/wp-content/uploads/2011/12/iDEAL_Advanced_PHP_EN_V2.2.pdf (page 19)
		 *
		 * @todo
		 * Considering the number of status requests per transaction:
		 * - Maximum of five times per transaction;
		 * - Maximum of two times during the expirationPeriod;
		 * - After the expirationPeriod not more often than once per 60 minutes;
		 * - No status request after a final status has been received for a transaction;
		 * - No status request for transactions older than 7 days.
		 */

		/*
		 * The function `wp_schedule_single_event` uses the arguments array as an key for the event,
		 * that's why we also add the time to this array, besides that it's also much clearer on
		 * the Cron View (http://wordpress.org/extend/plugins/cron-view/) page.
		 */

		// Bail if payment already has a final status (e.g. failed payments).
		$status = $payment->get_status();

		if ( ! empty( $status ) && Statuses::OPEN !== $status ) {
			return;
		}

		// Get delay seconds for first status check.
		$delay = self::get_delay_seconds( 1, $payment->get_recurring() );

		wp_schedule_single_event(
			time() + $delay,
			'pronamic_pay_payment_status_check',
			array(
				'payment_id' => $payment->get_id(),
				'try'        => 1,
			)
		);
	}

	/**
	 * Get the delay seconds for the specified try.
	 *
	 * @param int       $try       Which try/round to get the delay seconds for.
	 * @param bool|null $recurring Whether or not to use the delay scheme for recurring payments.
	 *
	 * @return int
	 */
	private static function get_delay_seconds( $try, $recurring = false ) {
		// Delays for recurring payments.
		if ( $recurring ) {
			switch ( $try ) {
				case 1:
					return 15 * MINUTE_IN_SECONDS;

				case 2:
					return 5 * DAY_IN_SECONDS;

				case 3:
					return 10 * DAY_IN_SECONDS;

				case 4:
				default:
					return 14 * DAY_IN_SECONDS;
			}
		}

		// Delays for regular payments.
		switch ( $try ) {
			case 1:
				return 15 * MINUTE_IN_SECONDS;

			case 2:
				return 30 * MINUTE_IN_SECONDS;

			case 3:
				return HOUR_IN_SECONDS;

			case 4:
			default:
				return DAY_IN_SECONDS;
		}
	}

	/**
	 * Backwards compatible transaction status check.
	 *
	 * @param integer $payment_id   The ID of a payment to check.
	 * @param integer $seconds      The number of seconds this status check was delayed.
	 * @param integer $number_tries The number of status check tries.
	 *
	 * @return void
	 *
	 * @deprecated 2.1.6 In favor of event `pronamic_pay_payment_status_check`.
	 */
	public function check_transaction_status( $payment_id = null, $seconds = null, $number_tries = 1 ) {
		$this->check_status( $payment_id, $number_tries );
	}

	/**
	 * Check status of the specified payment.
	 *
	 * @param integer $payment_id The payment ID to check.
	 * @param integer $try        The try number for this status check.
	 *
	 * @return void
	 */
	public function check_status( $payment_id = null, $try = 1 ) {
		$payment = get_pronamic_payment( $payment_id );

		// No payment found, unable to check status.
		if ( null === $payment ) {
			return;
		}

		// http://pronamic.nl/wp-content/uploads/2011/12/iDEAL_Advanced_PHP_EN_V2.2.pdf (page 19)
		// - No status request after a final status has been received for a transaction.
		if ( ! empty( $payment->status ) && Statuses::OPEN !== $payment->status ) {
			return;
		}

		// Add note.
		$note = sprintf(
			/* translators: %s: Pronamic Pay */
			__( 'Payment status check at gateway by %s.', 'pronamic_ideal' ),
			__( 'Pronamic Pay', 'pronamic_ideal' )
		);

		$payment->add_note( $note );

		// Update payment.
		Plugin::update_payment( $payment );

		// Limit number of tries.
		if ( 4 === $try ) {
			return;
		}

		// Schedule check if no final status has been received.
		$status = $payment->get_status();

		if ( empty( $status ) || Statuses::OPEN === $status ) {
			$next_try = ( $try + 1 );

			// Get delay seconds for next status check.
			$delay = self::get_delay_seconds( $next_try, $payment->get_recurring() );

			wp_schedule_single_event(
				time() + $delay,
				'pronamic_pay_payment_status_check',
				array(
					'payment_id' => $payment->get_id(),
					'try'        => $next_try,
				)
			);
		}
	}

	/**
	 * Maybe clear scheduled status check.
	 *
	 * @param Payment $payment Payment to maybe clear scheduled status checks for.
	 *
	 * @return void
	 */
	public function maybe_clear_scheduled_status_check( $payment ) {
		$status = $payment->get_status();

		// Bail if payment does not have a final payment status.
		if ( empty( $status ) || Statuses::OPEN === $status ) {
			return;
		}

		// Clear scheduled hooks for all 4 tries.
		$args = array(
			'payment_id' => $payment->get_id(),
		);

		foreach ( range( 1, 4 ) as $try ) {
			$args['try'] = $try;

			wp_clear_scheduled_hook( 'pronamic_pay_payment_status_check', $args );
		}
	}
}
