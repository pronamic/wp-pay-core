<?php
/**
 * Logger.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

class Logger {
	/**
	 * Log exception.
	 */
	public static function log( $error_code, $message, $data, $trace ) {
		$log = self::get_log();

		// Append error.
		$errors = $log->errors;

		$log->errors = array_merge(
			array(
				array(
					'date'       => current_time( 'mysql', true ),
					'error_code' => $error_code,
					'message'    => $message,
					'data'       => $data,
					'trace'      => $trace,
				),
			),
			$errors
		);

		// Only keep last 50 error messages.
		// @todo keep messages for last 30 days.
		$log->errors = array_slice( $log->errors, 0, 10 );

		// Save JSON encoded error log.
		update_option( 'pronamic_pay_log', wp_json_encode( $log ) );
	}

	/**
	 * Log exception.
	 *
	 * @param PayException $exception Pay exception.
	 */
	public static function log_exception( PayException $exception ) {
		self::log( $exception->get_error_code(), $exception->get_message(), $exception->get_data(), $exception->getTraceAsString() );
	}

	/**
	 * Get log.
	 *
	 * @return array|mixed|object|void
	 */
	public static function get_log() {
		// Retrieve current error log.
		$log = get_option( 'pronamic_pay_log' );

		$log = json_decode( $log );

		if ( null === $log ) {
			$log = (object) array(
				'errors' => array(),
			);
		}

		return $log;
	}
}
