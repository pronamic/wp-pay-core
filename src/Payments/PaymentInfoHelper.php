<?php
/**
 * Payment info helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Payment info helper
 *
 * @author  Remco Tolsma
 * @version 2.5.0
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
		$object = (object) [];

		$id = $payment_info->get_id();

		if ( null !== $id ) {
			$object->id = $id;
		}

		if ( null !== $payment_info->order_id ) {
			$object->order_id = $payment_info->order_id;
		}

		$key = $payment_info->key;

		if ( null !== $key ) {
			$object->key = $key;
		}

		$description = $payment_info->get_description();

		if ( null !== $description ) {
			$object->description = $description;
		}

		$payment_method = $payment_info->get_payment_method();

		if ( null !== $payment_method ) {
			$object->payment_method = $payment_method;
		}

		$origin_id = $payment_info->get_origin_id();

		if ( null !== $origin_id ) {
			$object->origin_id = $origin_id;
		}

		$shipping_amount = $payment_info->get_shipping_amount();

		if ( null !== $shipping_amount ) {
			$object->shipping_amount = $shipping_amount->jsonSerialize();
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

		// Consumer bank details.
		$consumer_bank_details = $payment_info->get_consumer_bank_details();

		if ( null !== $consumer_bank_details ) {
			$object->consumer_bank_details = $consumer_bank_details->get_json();
		}

		// Bank transfer recipient details.
		$bank_transfer_recipient_details = $payment_info->get_bank_transfer_recipient_details();

		if ( null !== $bank_transfer_recipient_details ) {
			$object->bank_transfer_recipient_details = $bank_transfer_recipient_details->get_json();
		}

		$mode = $payment_info->get_mode();

		if ( null !== $mode ) {
			$object->mode = $mode;
		}

		if ( $payment_info->is_anonymized() ) {
			$object->anonymized = $payment_info->is_anonymized();
		}

		$version = $payment_info->get_version();

		if ( null !== $version ) {
			$object->version = $version;
		}

		$meta = $payment_info->meta;

		if ( ! empty( $meta ) ) {
			$object->meta = (object) $meta;
		}

		$source_key   = $payment_info->get_source();
		$source_value = $payment_info->get_source_id();

		if ( null !== $source_key || null !== $source_value ) {
			$object->source = (object) [
				'key'   => $source_key,
				'value' => $source_value,
			];
		}

		$config_id = $payment_info->get_config_id();

		if ( null !== $config_id ) {
			$object->gateway = (object) [
				'$ref'       => \rest_url(
					\sprintf(
						'/%s/%s/%d',
						'pronamic-pay/v1',
						'gateways',
						$config_id
					)
				),
				'post_id'    => $config_id,
				'gateway_id' => \get_post_meta( $config_id, '_pronamic_gateway_id', true ),
			];
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

		if ( isset( $json->order_id ) ) {
			$payment_info->order_id = $json->order_id;
		}

		if ( isset( $json->description ) ) {
			$payment_info->set_description( $json->description );
		}

		if ( isset( $json->payment_method ) ) {
			$payment_info->set_payment_method( $json->payment_method );
		}

		if ( isset( $json->origin_id ) ) {
			$payment_info->set_origin_id( $json->origin_id );
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

		if ( isset( $json->consumer_bank_details ) ) {
			$payment_info->set_consumer_bank_details( BankAccountDetails::from_json( $json->consumer_bank_details ) );
		}

		if ( isset( $json->bank_transfer_recipient_details ) ) {
			$payment_info->set_bank_transfer_recipient_details( BankTransferDetails::from_json( $json->bank_transfer_recipient_details ) );
		}

		if ( isset( $json->lines ) ) {
			$payment_info->set_lines( PaymentLines::from_json( $json->lines, $payment_info ) );
		}

		if ( isset( $json->mode ) ) {
			$payment_info->set_mode( $json->mode );
		}

		if ( isset( $json->anonymized ) ) {
			$payment_info->set_anonymized( $json->anonymized );
		}

		if ( isset( $json->version ) ) {
			$payment_info->set_version( $json->version );
		}

		if ( isset( $json->meta ) ) {
			foreach ( $json->meta as $key => $value ) {
				$payment_info->meta[ $key ] = $value;
			}
		}

		if ( isset( $json->source ) && \is_object( $json->source ) ) {
			if ( isset( $json->source->key ) ) {
				$payment_info->set_source( $json->source->key );
			}

			if ( isset( $json->source->value ) ) {
				$payment_info->set_source_id( $json->source->value );
			}
		}

		if ( isset( $json->gateway ) && \is_object( $json->gateway ) ) {
			if ( isset( $json->gateway->post_id ) ) {
				$payment_info->set_config_id( $json->gateway->post_id );
			}
		}

		if ( isset( $json->key ) ) {
			$payment_info->key = $json->key;
		}

		return $payment_info;
	}
}
