<?php
/**
 * Payment info helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\TaxedMoneyJsonTransformer;

/**
 * Payment info helper
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class PaymentInfoHelper {
	/**
	 * Convert payment info to JSON.
	 *
	 * @param PaymentInfo $payment_info Payment info.
	 * @return object
	 */
	public static function to_json( PaymentInfo $payment_info ) {
		$object = (object) array();

		if ( null !== $payment_info->get_id() ) {
			$object->id = $payment_info->get_id();
		}

		$object->total_amount = TaxedMoneyJsonTransformer::to_json( $payment_info->get_total_amount() );

		$shipping_amount = $payment_info->get_shipping_amount();

		if ( null !== $shipping_amount ) {
			$object->shipping_amount = MoneyJsonTransformer::to_json( $shipping_amount );
		}

		$customer = $payment_info->get_customer();

		if ( null !== $customer ) {
			$object->customer = $customer->get_json();
		}

		$billing_address = $payment_info->get_billing_address();

		if ( null !== $billing_address ) {
			$object->billing_address = $billing_address->get_json();
		}

		$shipping_address = $payment_info->get_shipping_address();

		if ( null !== $shipping_address ) {
			$object->shipping_address = $shipping_address->get_json();
		}

		$lines = $payment_info->get_lines();

		if ( null !== $lines ) {
			$object->lines = $lines->get_json();
		}

		$mode = $payment_info->get_mode();

		if ( null !== $mode ) {
			$object->mode = $mode;
		}

		if ( $payment_info->is_anonymized() ) {
			$object->anonymized = $payment_info->is_anonymized();
		}

		return $object;
	}

	/**
	 * Convert JSON to payment info object.
	 *
	 * @param object      $json         JSON.
	 * @param PaymentInfo $payment_info Payment info object.
	 * @return PaymentInfo
	 */
	public static function from_json( $json, PaymentInfo $payment_info ) {
		if ( isset( $json->id ) ) {
			$payment_info->set_id( $json->id );
		}

		if ( isset( $json->total_amount ) ) {
			$payment_info->set_total_amount( TaxedMoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( isset( $json->shipping_amount ) ) {
			$payment_info->set_shipping_amount( MoneyJsonTransformer::from_json( $json->shipping_amount ) );
		}

		if ( isset( $json->customer ) ) {
			$payment_info->set_customer( Customer::from_json( $json->customer ) );
		}

		if ( isset( $json->billing_address ) ) {
			$payment_info->set_billing_address( Address::from_json( $json->billing_address ) );
		}

		if ( isset( $json->shipping_address ) ) {
			$payment_info->set_shipping_address( Address::from_json( $json->shipping_address ) );
		}

		if ( isset( $json->lines ) ) {
			$payment_info->set_lines( PaymentLines::from_json( $json->lines ) );
		}

		if ( isset( $json->mode ) ) {
			$payment_info->set_mode( $json->mode );
		}

		if ( isset( $json->anonymized ) ) {
			$payment_info->set_anonymized( $json->anonymized );
		}

		return $payment_info;
	}
}
