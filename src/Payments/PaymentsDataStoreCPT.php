<?php
/**
 * Payments Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\AbstractDataStoreCPT;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\Statuses;

/**
 * Title: Payments data store CPT
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @see     https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @see     https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   3.7.0
 */
class PaymentsDataStoreCPT extends AbstractDataStoreCPT {
	/**
	 * Construct payments data store CPT object.
	 */
	public function __construct() {
		$this->meta_key_prefix = '_pronamic_payment_';

		$this->register_meta();
	}

	/**
	 * Create payment.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L47-L76
	 *
	 * @param Payment $payment The payment to create in this data store.
	 *
	 * @return bool
	 */
	public function create( Payment $payment ) {
		$title = $payment->title;

		if ( empty( $title ) ) {
			$title = sprintf(
				'Payment – %s',
				date_i18n( _x( 'M d, Y @ h:i A', 'Payment title date format parsed by `date_i18n`.', 'pronamic_ideal' ) )
			);
		}

		$post_status = $this->get_post_status( $payment->status );

		$result = wp_insert_post(
			array(
				'post_type'     => 'pronamic_payment',
				'post_date_gmt' => $this->get_mysql_utc_date( $payment->date ),
				'post_title'    => $title,
				'post_status'   => empty( $post_status ) ? 'payment_pending' : null,
				'post_author'   => $payment->user_id,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$payment->set_id( $result );
		$payment->post = get_post( $result );

		$this->update_post_meta( $payment );

		do_action( 'pronamic_pay_new_payment', $payment );

		return true;
	}

	/**
	 * Read payment.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-order.php#L85-L111
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L78-L111
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L81-L136
	 * @see https://developer.wordpress.org/reference/functions/get_post/
	 * @see https://developer.wordpress.org/reference/classes/wp_post/
	 *
	 * @param Payment $payment The payment to read from this data store.
	 */
	public function read( Payment $payment ) {
		$payment->post    = get_post( $payment->get_id() );
		$payment->title   = get_the_title( $payment->get_id() );
		$payment->date    = new DateTime( get_post_field( 'post_date_gmt', $payment->get_id(), 'raw' ), new DateTimeZone( 'UTC' ) );
		$payment->user_id = get_post_field( 'post_author', $payment->get_id(), 'raw' );

		$this->read_post_meta( $payment );
	}

	/**
	 * Update payment.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to update in this data store.
	 */
	public function update( Payment $payment ) {
		$data = array(
			'ID' => $payment->get_id(),
		);

		$post_status = $this->get_post_status( $payment->status );

		if ( ! empty( $post_status ) ) {
			$data['post_status'] = $post_status;
		}

		wp_update_post( $data );

		$this->update_post_meta( $payment );
	}

	/**
	 * Get post status.
	 *
	 * @param string $meta_status The payment to get a WordPress post status for.
	 *
	 * @return string|null
	 */
	public function get_post_status( $meta_status ) {
		switch ( $meta_status ) {
			case Statuses::CANCELLED:
				return 'payment_cancelled';
			case Statuses::EXPIRED:
				return 'payment_expired';
			case Statuses::FAILURE:
				return 'payment_failed';
			case Statuses::SUCCESS:
				return 'payment_completed';
			case Statuses::OPEN:
				return 'payment_pending';
			default:
				return null;
		}
	}

	/**
	 * Get meta status label.
	 *
	 * @param string $meta_status The payment meta status to get the status label for.
	 * @return string|boolean
	 */
	public function get_meta_status_label( $meta_status ) {
		$post_status = $this->get_post_status( $meta_status );

		if ( empty( $post_status ) ) {
			return false;
		}

		$status_object = get_post_status_object( $post_status );

		if ( isset( $status_object, $status_object->label ) ) {
			return $status_object->label;
		}

		return false;
	}

	/**
	 * Register meta.
	 *
	 * @return void
	 */
	private function register_meta() {
		$this->register_meta_key(
			'config_id',
			array(
				'label' => __( 'Config ID', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'key',
			array(
				'label' => __( 'Key', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'method',
			array(
				'label'           => __( 'Method', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'currency',
			array(
				'label'          => __( 'Currency', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'amount',
			array(
				'label'          => __( 'Amount', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'issuer',
			array(
				'label'           => __( 'Issuer', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'order_id',
			array(
				'label'          => __( 'Order ID', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'transaction_id',
			array(
				'label' => __( 'Transaction ID', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'entrance_code',
			array(
				'label'           => __( 'Entrance Code', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'action_url',
			array(
				'label'           => __( 'Action URL', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'source',
			array(
				'label' => __( 'Source', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'source_id',
			array(
				'label' => __( 'Source ID', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'description',
			array(
				'label'           => __( 'Description', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'language',
			array(
				'label'           => __( 'Language', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'locale',
			array(
				'label'           => __( 'Locale', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'email',
			array(
				'label'           => __( 'Email', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'anonymize',
			)
		);

		$this->register_meta_key(
			'status',
			array(
				'label'          => __( 'Status', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'customer_name',
			array(
				'label'           => __( 'Customer Name', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'address',
			array(
				'label'           => __( 'Address', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'postal_code',
			array(
				'label'           => __( 'Postal Code', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'city',
			array(
				'label'           => __( 'City', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'country',
			array(
				'label'           => __( 'Country', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'telephone_number',
			array(
				'label'           => __( 'Telephone Number', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'consumer_name',
			array(
				'label'           => __( 'Consumer Name', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'consumer_account_number',
			array(
				'label'           => __( 'Consumer Account Number', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'consumer_iban',
			array(
				'label'           => __( 'Consumer IBAN', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'consumer_bic',
			array(
				'label'           => __( 'Consumer BIC', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'consumer_city',
			array(
				'label'           => __( 'Consumer City', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'analytics_client_id',
			array(
				'label'           => __( 'Analytics Client ID', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'subscription_id',
			array(
				'label'          => __( 'Subscription ID', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'recurring_type',
			array(
				'label'          => __( 'Recurring Type', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'recurring',
			array(
				'label' => __( 'Recurring', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'start_date',
			array(
				'label'          => __( 'Start Date', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'end_date',
			array(
				'label'          => __( 'End Date', 'pronamic_ideal' ),
				'privacy_export' => true,
			)
		);

		$this->register_meta_key(
			'user_agent',
			array(
				'label'           => __( 'User Agent', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'user_ip',
			array(
				'label'           => __( 'User IP', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);
	}

	/**
	 * Get payment customer.
	 *
	 * @param int $id Post ID.
	 * @return Customer|null
	 */
	private function get_customer( $id ) {
		$value = $this->get_meta( $id, 'customer' );

		if ( empty( $value ) ) {
			// Build customer from legacy meta data.
			$customer = new Customer();

			$contact_name = new ContactName();
			$contact_name->set_first_name( $this->get_meta( $id, 'first_name' ) );
			$contact_name->set_last_name( $this->get_meta( $id, 'last_name' ) );

			$customer->set_name( $contact_name );
			$customer->set_email( $this->get_meta( $id, 'email' ) );
			$customer->set_phone( $this->get_meta( $id, 'telephone_number' ) );
			$customer->set_ip_address( $this->get_meta( $id, 'user_ip' ) );
			$customer->set_user_agent( $this->get_meta( $id, 'user_agent' ) );
			$customer->set_language( $this->get_meta( $id, 'language' ) );
			$customer->set_locale( $this->get_meta( $id, 'locale' ) );

			return $customer;
		}

		$object = json_decode( $value );

		if ( is_object( $object ) ) {
			return Customer::from_json( $object );
		}

		return null;
	}

	/**
	 * Get payment billing address.
	 *
	 * @param int $id Post ID.
	 * @return Address|null
	 */
	private function get_billing_address( $id ) {
		$value = $this->get_meta( $id, 'billing_address' );

		if ( empty( $value ) ) {
			// Build address from legacy meta data.
			$name = new ContactName();
			$name->set_first_name( $this->get_meta( $id, 'first_name' ) );
			$name->set_last_name( $this->get_meta( $id, 'last_name' ) );

			$line_1      = $this->get_meta( $id, 'address' );
			$postal_code = $this->get_meta( $id, 'zip' );
			$city        = $this->get_meta( $id, 'city' );
			$country     = $this->get_meta( $id, 'country' );

			$parts = array(
				$line_1,
				$postal_code,
				$city,
				$country,
			);

			$parts = array_map( 'trim', $parts );

			if ( ! empty( $parts ) ) {
				$address = new Address();

				$address->set_name( $name );
				$address->set_email( $this->get_meta( $id, 'email' ) );
				$address->set_phone( $this->get_meta( $id, 'telephone_number' ) );
				$address->set_line_1( $line_1 );
				$address->set_postal_code( $postal_code );
				$address->set_city( $city );
				$address->set_country_name( $country );

				return $address;
			}
		}

		$object = json_decode( $value );

		if ( is_object( $object ) ) {
			return Address::from_json( $object );
		}

		return null;
	}

	/**
	 * Get payment shipping address.
	 *
	 * @param int $id Post ID.
	 * @return Address|null
	 */
	private function get_shipping_address( $id ) {
		$value = $this->get_meta( $id, 'shipping_address' );

		if ( empty( $value ) ) {
			return null;
		}

		$object = json_decode( $value );

		if ( is_object( $object ) ) {
			return Address::from_json( $object );
		}

		return null;
	}

	/**
	 * Get payment lines.
	 *
	 * @param int $id Post ID.
	 * @return PaymentLines|null
	 */
	private function get_payment_lines( $id ) {
		$value = $this->get_meta( $id, 'lines' );

		if ( empty( $value ) ) {
			return null;
		}

		$json = json_decode( $value );

		// @todo what if this fails? try catch or throw exception?
		$value = PaymentLines::from_json( $json );

		return $value;
	}

	/**
	 * Read post meta.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-data.php#L462-L507
	 * @param Payment $payment The payment to read.
	 */
	private function read_post_meta( $payment ) {
		$id = $payment->get_id();

		$payment->config_id           = $this->get_meta( $id, 'config_id' );
		$payment->key                 = $this->get_meta( $id, 'key' );
		$payment->method              = $this->get_meta( $id, 'method' );
		$payment->issuer              = $this->get_meta( $id, 'issuer' );
		$payment->order_id            = $this->get_meta( $id, 'order_id' );
		$payment->transaction_id      = $this->get_meta( $id, 'transaction_id' );
		$payment->entrance_code       = $this->get_meta( $id, 'entrance_code' );
		$payment->action_url          = $this->get_meta( $id, 'action_url' );
		$payment->source              = $this->get_meta( $id, 'source' );
		$payment->source_id           = $this->get_meta( $id, 'source_id' );
		$payment->description         = $this->get_meta( $id, 'description' );
		$payment->email               = $this->get_meta( $id, 'email' );
		$payment->status              = $this->get_meta( $id, 'status' );
		$payment->analytics_client_id = $this->get_meta( $id, 'analytics_client_id' );
		$payment->subscription_id     = $this->get_meta( $id, 'subscription_id' );
		$payment->recurring_type      = $this->get_meta( $id, 'recurring_type' );
		$payment->recurring           = $this->get_meta( $id, 'recurring' );
		$payment->start_date          = $this->get_meta_date( $id, 'start_date' );
		$payment->end_date            = $this->get_meta_date( $id, 'end_date' );
		$payment->customer            = $this->get_customer( $id );
		$payment->billing_address     = $this->get_billing_address( $id );
		$payment->shipping_address    = $this->get_shipping_address( $id );
		$payment->lines               = $this->get_payment_lines( $id );

		$payment->set_version( $this->get_meta( $id, 'version' ) );

		if ( null !== $payment->lines ) {
			foreach ( $payment->lines as $line ) {
				PaymentLineHelper::complement_payment_line( $line );
			}
		}

		// Deprecated properties, use `get_customer()` or `get_billing_address()` instead.
		// @todo remove?
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$payment->language   = $customer->get_language();
			$payment->locale     = $customer->get_locale();
			$payment->user_agent = $customer->get_user_agent();
			$payment->user_ip    = $customer->get_ip_address();

			$name = $customer->get_name();

			if ( null !== $name ) {
				$payment->customer_name = strval( $name );
				$payment->first_name    = $name->get_first_name();
				$payment->last_name     = $name->get_last_name();
			}
		}

		$billing_address = $payment->get_billing_address();

		if ( null !== $billing_address ) {
			$payment->address          = $billing_address->get_line_1();
			$payment->zip              = $billing_address->get_postal_code();
			$payment->city             = $billing_address->get_city();
			$payment->country          = $billing_address->get_country_name();
			$payment->telephone_number = $billing_address->get_phone();
		}

		// Gravity Forms country fix.
		if ( ! empty( $payment->country ) && 'gravityformsideal' === $payment->source && method_exists( 'GFCommon', 'get_country_code' ) ) {
			$payment->country = \GFCommon::get_country_code( $payment->country );
		}

		// Amount.
		$payment->set_amount(
			new Money(
				$this->get_meta( $id, 'amount' ),
				$this->get_meta( $id, 'currency' )
			)
		);
	}

	/**
	 * Update payment post meta.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to update.
	 */
	private function update_post_meta( $payment ) {
		$meta = array(
			'config_id'               => $payment->config_id,
			'key'                     => $payment->key,
			'order_id'                => $payment->order_id,
			'currency'                => $payment->get_currency(),
			'amount'                  => $payment->get_amount()->get_amount(),
			'method'                  => $payment->method,
			'issuer'                  => $payment->issuer,
			'expiration_period'       => null,
			'entrance_code'           => $payment->entrance_code,
			'description'             => $payment->description,
			'consumer_name'           => $payment->consumer_name,
			'consumer_account_number' => $payment->consumer_account_number,
			'consumer_iban'           => $payment->consumer_iban,
			'consumer_bic'            => $payment->consumer_bic,
			'consumer_city'           => $payment->consumer_city,
			'source'                  => $payment->source,
			'source_id'               => $payment->source_id,
			'email'                   => $payment->get_email(),
			'analytics_client_id'     => $payment->analytics_client_id,
			'subscription_id'         => $payment->subscription_id,
			'recurring_type'          => $payment->recurring_type,
			'recurring'               => $payment->recurring,
			'transaction_id'          => $payment->get_transaction_id(),
			'action_url'              => $payment->get_action_url(),
			'start_date'              => $payment->start_date,
			'end_date'                => $payment->end_date,
			'version'                 => $payment->get_version(),
		);

		// Customer.
		$customer = $payment->get_customer();

		if ( null !== $customer ) {
			$meta['customer'] = wp_json_encode( $customer->get_json() );

			// Deprecated meta values.
			$meta['language']   = $customer->get_language();
			$meta['locale']     = $customer->get_locale();
			$meta['user_agent'] = $customer->get_user_agent();
			$meta['user_ip']    = $customer->get_ip_address();

			$name = $customer->get_name();

			if ( null !== $name ) {
				$meta['customer_name'] = (string) $name;
				$meta['first_name']    = $name->get_first_name();
				$meta['last_name']     = $name->get_last_name();
			}
		}

		$billing_address = $payment->get_billing_address();

		if ( null !== $billing_address ) {
			$meta['billing_address'] = wp_json_encode( $billing_address->get_json() );

			// Deprecated meta values.
			$meta['address']          = $billing_address->get_line_1();
			$meta['zip']              = $billing_address->get_postal_code();
			$meta['city']             = $billing_address->get_city();
			$meta['country']          = $billing_address->get_country_name();
			$meta['telephone_number'] = $billing_address->get_phone();
		}

		// Shipping address.
		$shipping_address = $payment->get_shipping_address();

		if ( null !== $shipping_address ) {
			$meta['shipping_address'] = wp_json_encode( $shipping_address->get_json() );
		}

		// Lines.
		$lines = $payment->get_lines();

		if ( null !== $lines ) {
			$meta['lines'] = wp_json_encode( $lines->get_json() );
		}

		// Update meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			$this->update_meta( $payment->get_id(), $meta_key, $meta_value );
		}

		$this->update_meta_status( $payment );
	}

	/**
	 * Update meta status.
	 *
	 * @param Payment $payment The payment to update the status for.
	 */
	public function update_meta_status( $payment ) {
		$id = $payment->get_id();

		$previous_status = $this->get_meta( $id, 'status' );

		$this->update_meta( $id, 'status', $payment->status );

		if ( $previous_status !== $payment->status ) {
			$old = $previous_status;
			$old = strtolower( $old );
			$old = empty( $old ) ? 'unknown' : $old;

			$new = $payment->status;
			$new = strtolower( $new );
			$new = empty( $new ) ? 'unknown' : $new;

			$can_redirect = false;

			do_action( 'pronamic_payment_status_update_' . $payment->source . '_' . $old . '_to_' . $new, $payment, $can_redirect, $previous_status, $payment->status );
			do_action( 'pronamic_payment_status_update_' . $payment->source, $payment, $can_redirect, $previous_status, $payment->status );
			do_action( 'pronamic_payment_status_update', $payment, $can_redirect, $previous_status, $payment->status );
		}
	}
}
