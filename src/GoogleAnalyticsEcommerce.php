<?php
/**
 * Google Analytics E-Commerce
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\Server;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Pronamic Pay Google Analytics e-commerce
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.0.1
 */
class GoogleAnalyticsEcommerce {
	/**
	 * Google Analytics Measurement Protocol API endpoint URL.
	 *
	 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
	 * @var string
	 */
	const API_URL = 'https://www.google-analytics.com/collect';

	/**
	 * Measurement Protocol API version.
	 *
	 * @var int
	 */
	const API_VERSION = '1';

	/**
	 * Anonymous client ID.
	 *
	 * @var string|null
	 */
	private $client_id;

	/**
	 * Constructs an analytics e-commerce object.
	 */
	public function __construct() {
		// Actions.
		add_action( 'pronamic_payment_status_update', array( $this, 'maybe_send_transaction' ), 10 );
	}

	/**
	 * Maybe send transaction for the specified payment.
	 *
	 * @param Payment $payment Payment.
	 */
	public function maybe_send_transaction( $payment ) {
		// Ignore test mode payments.
		if ( Gateway::MODE_TEST === $payment->get_mode() ) {
			return;
		}

		$this->send_transaction( $payment );
	}

	/**
	 * Is this a valid payment to track?
	 *
	 * @param Payment $payment Payment to track.
	 *
	 * @return bool
	 */
	public function valid_payment( $payment ) {
		// Is payment already tracked?
		if ( $payment->get_ga_tracked() ) {
			return false;
		}

		// Check if Google Analytics property ID has been set.
		$property_id = get_option( 'pronamic_pay_google_analytics_property' );

		if ( empty( $property_id ) ) {
			return false;
		}

		// Only process successful payments.
		if ( Statuses::SUCCESS !== $payment->get_status() ) {
			return false;
		}

		return true;
	}

	/**
	 * Send transaction.
	 *
	 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#ecom
	 *
	 * Parameters:
	 * v=1              // Version.
	 * &tid=UA-XXXX-Y   // Tracking ID / Property ID.
	 * &cid=555         // Anonymous Client ID.
	 *
	 * &t=transaction   // Transaction hit type.
	 * &ti=12345        // transaction ID. Required.
	 * &ta=westernWear  // Transaction affiliation.
	 * &tr=50.00        // Transaction revenue.
	 * &ts=32.00        // Transaction shipping.
	 * &tt=12.00        // Transaction tax.
	 * &cu=EUR          // Currency code.
	 *
	 * @param Payment $payment Payment.
	 */
	public function send_transaction( $payment ) {
		if ( ! $this->valid_payment( $payment ) ) {
			return;
		}

		$defaults = array(
			'v'   => self::API_VERSION,
			'tid' => get_option( 'pronamic_pay_google_analytics_property' ),
			'cid' => $this->get_client_id( $payment ),
			'ti'  => strval( $payment->get_id() ),
			'ni'  => 1,
		);

		// Transaction Hit.
		$transaction = wp_parse_args(
			array(
				't'  => 'transaction',
				'tr' => sprintf( '%F', $payment->get_total_amount()->get_value() ),
			),
			$defaults
		);

		// Currency.
		if ( null !== $payment->get_total_amount()->get_currency()->get_alphabetic_code() ) {
			/*
			 * Currency Code
			 * Optional.
			 * When present indicates the local currency for all transaction currency values. Value should be a valid ISO 4217 currency code.
			 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#cu
			 */
			$transaction['cu'] = $payment->get_total_amount()->get_currency()->get_alphabetic_code();
		}

		// Shipping.
		$shipping_amount = $payment->get_shipping_amount();

		if ( null !== $shipping_amount ) {
			/*
			 * Transaction Shipping
			 * Optional.
			 * Specifies the total shipping cost of the transaction.
			 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ts
			 */
			$transaction['ts'] = sprintf( '%F', $shipping_amount->get_value() );
		}

		// Tax.
		if ( $payment->get_total_amount()->has_tax() ) {
			/*
			 * Transaction Tax
			 * Optional.
			 * Specifies the total tax of the transaction.
			 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#tt
			 */
			$transaction['tt'] = sprintf( '%F', $payment->get_total_amount()->get_tax_value() );
		}

		wp_remote_post(
			self::API_URL,
			array(
				'user-agent' => Server::get( 'HTTP_USER_AGENT' ),
				'body'       => $transaction,
				'blocking'   => false,
			)
		);

		// Mark payment as tracked.
		$payment->set_ga_tracked( true );
		$payment->save();

		// Item Hit.
		$lines = $payment->get_lines();

		if ( ! empty( $lines ) ) {
			foreach ( $lines as $line ) {
				$item = $defaults;

				/*
				 * Hit - Hit type - Required for all hit types.
				 * The type of hit. Must be one of 'pageview', 'screenview', 'event', 'transaction', 'item', 'social', 'exception', 'timing'.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#t
				 */
				$item['t'] = 'item';

				/*
				 * Item Name - Required for item hit type. - Specifies the item name.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#in
				 */
				$item['in'] = $line->get_name();

				/*
				 * Item Price - Optional. - Specifies the price for a single item / unit.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ip
				 */
				$unit_price = $line->get_unit_price();

				if ( null !== $unit_price ) {
					$item['ip'] = sprintf( '%F', $unit_price->get_amount() );
				}

				/*
				 * Item Quantity - Optional. - Specifies the number of items purchased.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#iq
				 */
				if ( null !== $line->get_quantity() ) {
					$item['iq'] = $line->get_quantity();
				}

				/*
				 * Item Code - Optional. - Specifies the SKU or item code.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#ic
				 */
				if ( null !== $line->get_id() ) {
					$item['ic'] = $line->get_id();
				}

				if ( null !== $line->get_sku() ) {
					$item['ic'] = $line->get_sku();
				}

				/*
				 * Item Category - Optional. - Specifies the category that the item belongs to.
				 * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#iv
				 */
				if ( null !== $line->get_product_category() ) {
					$item['iv'] = $line->get_product_category();
				}

				wp_remote_post(
					self::API_URL,
					array(
						'user-agent' => Server::get( 'HTTP_USER_AGENT' ),
						'body'       => $item,
						'blocking'   => false,
					)
				);
			}
		}
	}

	/**
	 * Get the Client ID.
	 *
	 * @param Payment $payment Payment.
	 * @return string
	 */
	private function get_client_id( Payment $payment ) {
		$client_id = $payment->get_analytics_client_id();

		if ( ! empty( $client_id ) ) {
			return $client_id;
		}

		if ( ! empty( $this->client_id ) ) {
			return $this->client_id;
		}

		// Check cookie `_ga` for Client ID.
		$this->client_id = self::get_cookie_client_id();

		if ( empty( $this->client_id ) ) {
			// Generate UUID.
			// Borrowed from https://github.com/ins0/google-measurement-php-client/blob/master/src/Racecore/GATracking/GATracking.php.
			$this->client_id = sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low".
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff ),
				// 16 bits for "time_mid".
				wp_rand( 0, 0xffff ),
				// 16 bits for "time_hi_and_version",.
				// four most significant bits holds version number 4.
				wp_rand( 0, 0x0fff ) | 0x4000,
				// 16 bits, 8 bits for "clk_seq_hi_res",.
				// 8 bits for "clk_seq_low",.
				// two most significant bits holds zero and one for variant DCE1.1.
				wp_rand( 0, 0x3fff ) | 0x8000,
				// 48 bits for "node".
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff ),
				wp_rand( 0, 0xffff )
			);
		}

		return $this->client_id;
	}

	/**
	 * Check if the specified UUID is valid.
	 *
	 * @link http://php.net/preg_match
	 *
	 * @param string $uuid String.
	 * @return boolean True if value is a valid UUID, false otherwise.
	 */
	public static function is_uuid( $uuid ) {
		$result = preg_match( '#^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$#i', $uuid );

		return 1 === $result;
	}

	/**
	 * Get cookie client ID.
	 *
	 * @return string|null
	 */
	public static function get_cookie_client_id() {
		$client_id = null;

		$ga_cookie = filter_input( INPUT_COOKIE, '_ga', FILTER_SANITIZE_STRING );

		if ( empty( $ga_cookie ) ) {
			// No `_ga` cookie available.
			return $client_id;
		}

		$ga = explode( '.', $ga_cookie );

		if ( isset( $ga[2] ) && self::is_uuid( $ga[2] ) ) {
			// Use UUID from cookie.
			$client_id = $ga[2];
		} elseif ( isset( $ga[2], $ga[3] ) ) {
			// Older Google Client ID.
			$client_id = sprintf( '%s.%s', $ga[2], $ga[3] );
		}

		return $client_id;
	}
}
