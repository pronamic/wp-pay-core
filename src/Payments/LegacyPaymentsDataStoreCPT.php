<?php
/**
 * Legacy Payments Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\AbstractDataStoreCPT;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Payments data store CPT
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @see     https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @see     https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.1.0
 */
class LegacyPaymentsDataStoreCPT extends AbstractDataStoreCPT {
	/**
	 * Get contact name from legacy meta.
	 *
	 * @param PaymentInfo $payment The payment info to read.
	 * @return ContactName|null
	 */
	private function get_contact_name_from_legacy_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return null;
		}

		$data = [
			'full_name'  => $this->get_meta_string( $id, 'customer_name' ),
			'first_name' => $this->get_meta_string( $id, 'first_name' ),
			'last_name'  => $this->get_meta_string( $id, 'last_name' ),
		];

		$data = array_filter( $data );
		$data = array_map( 'trim', $data );
		$data = array_filter( $data );

		if ( empty( $data ) ) {
			// Bail out if there is no name data.
			return null;
		}

		$name = new ContactName();

		if ( isset( $data['full_name'] ) ) {
			$name->set_full_name( $data['full_name'] );
		}

		if ( isset( $data['first_name'] ) ) {
			$name->set_first_name( $data['first_name'] );
		}

		if ( isset( $data['last_name'] ) ) {
			$name->set_last_name( $data['last_name'] );
		}

		return $name;
	}

	/**
	 * Maybe create customer from legacy meta.
	 *
	 * @param PaymentInfo $payment The payment to read.
	 * @return void
	 */
	private function maybe_create_customer_from_legacy_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$data = [
			'full_name'  => $this->get_meta_string( $id, 'customer_name' ),
			'first_name' => $this->get_meta_string( $id, 'first_name' ),
			'last_name'  => $this->get_meta_string( $id, 'last_name' ),
			'email'      => $this->get_meta_string( $id, 'email' ),
			'phone'      => $this->get_meta_string( $id, 'telephone_number' ),
			'ip_address' => $this->get_meta_string( $id, 'user_ip' ),
			'user_agent' => $this->get_meta_string( $id, 'user_agent' ),
			'language'   => $this->get_meta_string( $id, 'language' ),
			'locale'     => $this->get_meta_string( $id, 'locale' ),
		];

		$data = array_filter( $data );
		$data = array_map( 'trim', $data );
		$data = array_filter( $data );

		if ( empty( $data ) ) {
			// Bail out if there is no customer data.
			return;
		}

		// Build customer from legacy meta data.
		$customer = $payment->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();
		}

		$payment->set_customer( $customer );

		// Customer name.
		if ( null === $customer->get_name() ) {
			$customer->set_name( $this->get_contact_name_from_legacy_meta( $payment ) );
		}

		if ( null === $customer->get_email() && isset( $data['email'] ) ) {
			$customer->set_email( $data['email'] );
		}

		if ( null === $customer->get_phone() && isset( $data['phone'] ) ) {
			$customer->set_phone( $data['phone'] );
		}

		if ( null === $customer->get_ip_address() && isset( $data['ip_address'] ) ) {
			$customer->set_ip_address( $data['ip_address'] );
		}

		if ( null === $customer->get_user_agent() && isset( $data['user_agent'] ) ) {
			$customer->set_user_agent( $data['user_agent'] );
		}

		if ( null === $customer->get_language() && isset( $data['language'] ) ) {
			$customer->set_language( $data['language'] );
		}

		if ( null === $customer->get_locale() && isset( $data['locale'] ) ) {
			$customer->set_locale( $data['locale'] );
		}
	}

	/**
	 * Maybe create billing address from legacy meta.
	 *
	 * @param PaymentInfo $payment The payment to read.
	 * @return void
	 */
	private function maybe_create_billing_address_from_legacy_meta( $payment ) {
		if ( null !== $payment->get_billing_address() ) {
			// Bail out if there is already a billing address.
			return;
		}

		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$data = [
			'line_1'      => $this->get_meta_string( $id, 'address' ),
			'postal_code' => $this->get_meta_string( $id, 'zip' ),
			'city'        => $this->get_meta_string( $id, 'city' ),
			'country'     => $this->get_meta_string( $id, 'country' ),
			'email'       => $this->get_meta_string( $id, 'email' ),
			'phone'       => $this->get_meta_string( $id, 'telephone_number' ),
		];

		$data = array_filter( $data );
		$data = array_map( 'trim', $data );
		$data = array_filter( $data );

		if ( empty( $data ) ) {
			// Bail out if there is no address data.
			return;
		}

		$address = new Address();

		$payment->set_billing_address( $address );

		$address->set_name( $this->get_contact_name_from_legacy_meta( $payment ) );

		if ( isset( $data['line_1'] ) ) {
			$address->set_line_1( $data['line_1'] );
		}

		if ( isset( $data['postal_code'] ) ) {
			$address->set_postal_code( $data['postal_code'] );
		}

		if ( isset( $data['city'] ) ) {
			$address->set_city( $data['city'] );
		}

		if ( isset( $data['country'] ) ) {
			if ( 2 === strlen( $data['country'] ) ) {
				$address->set_country_code( $data['country'] );
			} else {
				$address->set_country_name( $data['country'] );
			}
		}

		if ( isset( $data['email'] ) ) {
			$address->set_email( $data['email'] );
		}

		if ( isset( $data['phone'] ) ) {
			$address->set_phone( $data['phone'] );
		}
	}

	/**
	 * Maybe create consumer bank details from legacy meta.
	 *
	 * @param PaymentInfo $payment The payment to read.
	 * @return void
	 */
	private function maybe_create_consumer_bank_details_from_legacy_meta( $payment ) {
		if ( null !== $payment->get_consumer_bank_details() ) {
			// Bail out if there is already a billing consumer_bank_details.
			return;
		}

		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$data = [
			'consumer_name'           => $this->get_meta_string( $id, 'consumer_name' ),
			'consumer_account_number' => $this->get_meta_string( $id, 'consumer_account_number' ),
			'consumer_iban'           => $this->get_meta_string( $id, 'consumer_iban' ),
			'consumer_bic'            => $this->get_meta_string( $id, 'consumer_bic' ),
			'consumer_city'           => $this->get_meta_string( $id, 'consumer_city' ),
		];

		$data = array_filter( $data );
		$data = array_map( 'trim', $data );
		$data = array_filter( $data );

		if ( empty( $data ) ) {
			// Bail out if there is no consumer data.
			return;
		}

		$consumer_bank_details = new BankAccountDetails();

		$payment->set_consumer_bank_details( $consumer_bank_details );

		if ( isset( $data['consumer_name'] ) ) {
			$consumer_bank_details->set_name( $data['consumer_name'] );
		}

		if ( isset( $data['consumer_account_number'] ) ) {
			$consumer_bank_details->set_account_number( $data['consumer_account_number'] );
		}

		if ( isset( $data['consumer_iban'] ) ) {
			$consumer_bank_details->set_iban( $data['consumer_iban'] );
		}

		if ( isset( $data['consumer_bic'] ) ) {
			$consumer_bank_details->set_bic( $data['consumer_bic'] );
		}

		if ( isset( $data['consumer_city'] ) ) {
			$consumer_bank_details->set_city( $data['consumer_city'] );
		}
	}

	/**
	 * Read post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-data.php#L462-L507
	 * @param PaymentInfo $payment The payment to read.
	 * @return void
	 */
	protected function read_post_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		// General.
		$config_id = $this->get_meta_int( $id, 'config_id' );

		$payment->config_id = $config_id;
		$payment->source    = $this->get_meta_string( $id, 'source' );
		$payment->source_id = $this->get_meta_string( $id, 'source_id' );

		// Order ID.
		if ( empty( $payment->order_id ) ) {
			$payment->order_id = $this->get_meta_string( $id, 'order_id' );
		}

		// Key.
		if ( empty( $payment->key ) ) {
			$payment->key = $this->get_meta_string( $id, 'key' );
		}

		// Description.
		$description = $payment->get_description();

		if ( empty( $description ) ) {
			$description = $this->get_meta_string( $id, 'description' );

			$payment->set_description( $description );
		}

		// Payment method.
		$payment_method = $payment->get_payment_method();

		if ( empty( $payment_method ) ) {
			$payment_method = $this->get_meta_string( $id, 'method' );

			$payment->set_payment_method( $payment_method );
		}

		/**
		 * Clarify difference between afterpay.nl and afterpay.com.
		 *
		 * @link https://github.com/pronamic/wp-pronamic-pay/issues/282
		 */
		if ( PaymentMethods::AFTERPAY === $payment_method ) {
			$payment->set_payment_method( PaymentMethods::AFTERPAY_NL );
		}

		// Version.
		$meta_version = $this->get_meta_string( $id, 'version' );

		if ( ! empty( $meta_version ) ) {
			$payment->set_version( $meta_version );
		}

		// Other.
		$this->maybe_create_customer_from_legacy_meta( $payment );
		$this->maybe_create_billing_address_from_legacy_meta( $payment );
		$this->maybe_create_consumer_bank_details_from_legacy_meta( $payment );
	}
}
