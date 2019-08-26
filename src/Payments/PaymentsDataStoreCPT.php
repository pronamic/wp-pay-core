<?php
/**
 * Payments Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Exception;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Customer;

/**
 * Title: Payments data store CPT
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @see     https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @see     https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   3.7.0
 */
class PaymentsDataStoreCPT extends LegacyPaymentsDataStoreCPT {
	/**
	 * Payment.
	 *
	 * @var Payment|null
	 */
	private $payment;

	/**
	 * Payments.
	 *
	 * @var array
	 */
	private $payments;

	/**
	 * Status map.
	 *
	 * @var array
	 */
	private $status_map;

	/**
	 * Construct payments data store CPT object.
	 */
	public function __construct() {
		$this->meta_key_prefix = '_pronamic_payment_';

		$this->register_meta();

		$this->payments = array();

		$this->status_map = array(
			Statuses::CANCELLED => 'payment_cancelled',
			Statuses::EXPIRED   => 'payment_expired',
			Statuses::FAILURE   => 'payment_failed',
			Statuses::REFUNDED  => 'payment_refunded',
			Statuses::RESERVED  => 'payment_reserved',
			Statuses::SUCCESS   => 'payment_completed',
			Statuses::OPEN      => 'payment_pending',
		);
	}

	/**
	 * Setup.
	 */
	public function setup() {
		add_filter( 'wp_insert_post_data', array( $this, 'insert_payment_post_data' ), 10, 2 );

		add_action( 'save_post_pronamic_payment', array( $this, 'save_post_meta' ), 100, 3 );
	}

	/**
	 * Get payment by ID.
	 *
	 * @param int $id Payment ID.
	 * @return Payment|null
	 */
	private function get_payment( $id ) {
		if ( ! isset( $this->payments[ $id ] ) ) {
			$this->payments[ $id ] = get_pronamic_payment( $id );
		}

		return $this->payments[ $id ];
	}

	/**
	 * Get post status from meta status.
	 *
	 * @param string|null $meta_status Meta status.
	 * @return string|null
	 */
	private function get_post_status_from_meta_status( $meta_status ) {
		if ( null === $meta_status ) {
			return null;
		}

		if ( isset( $this->status_map[ $meta_status ] ) ) {
			return $this->status_map[ $meta_status ];
		}

		return null;
	}

	/**
	 * Get meta status from post status.
	 *
	 * @param string $post_status Post status.
	 * @return string|null
	 */
	private function get_meta_status_from_post_status( $post_status ) {
		$key = array_search( $post_status, $this->status_map, true );

		if ( false !== $key ) {
			return $key;
		}

		return null;
	}

	/**
	 * Complement payment post data.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.0.3/wp-includes/post.php#L3515-L3523
	 * @link https://developer.wordpress.org/reference/functions/wp_json_encode/
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array
	 * @throws Exception When inserting payment post data JSON string fails.
	 */
	public function insert_payment_post_data( $data, $postarr ) {
		$this->payment = null;

		if ( isset( $postarr['pronamic_payment'] ) ) {
			$this->payment = $postarr['pronamic_payment'];
		} elseif ( isset( $postarr['ID'] ) ) {
			$post_id = $postarr['ID'];

			$this->payment = $this->get_payment( $post_id );
		}

		if ( $this->payment instanceof Payment ) {
			// Update subscription from post array.
			$this->update_payment_form_post_array( $this->payment, $postarr );

			if ( ! isset( $data['post_status'] ) || 'trash' !== $data['post_status'] ) {
				$data['post_status'] = $this->get_post_status_from_meta_status( $this->payment->get_status() );
			}

			// Data.
			$json_string = wp_json_encode( $this->payment->get_json() );

			if ( false === $json_string ) {
				throw new Exception( 'Error inserting payment post data as JSON.' );
			}

			$data['post_content']   = wp_slash( $json_string );
			$data['post_mime_type'] = 'application/json';
		}

		return $data;
	}

	/**
	 * Update payment from post array.
	 *
	 * @param Payment $payment Payment.
	 * @param array   $postarr Post data array.
	 */
	private function update_payment_form_post_array( $payment, $postarr ) {
		if ( isset( $postarr['pronamic_payment_post_status'] ) ) {
			$post_status = sanitize_text_field( stripslashes( $postarr['pronamic_payment_post_status'] ) );
			$meta_status = $this->get_meta_status_from_post_status( $post_status );

			if ( null !== $meta_status ) {
				$payment->set_status( $meta_status );
			}
		}
	}

	/**
	 * Save post meta.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.0.3/wp-includes/post.php#L3724-L3736
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public function save_post_meta( $post_id, $post, $update ) {
		if ( $this->payment instanceof Payment ) {
			$payment = $this->payment;

			if ( ! $update && null === $payment->get_id() ) {
				$payment->set_id( $post_id );
				$payment->post = $post;
			}

			$this->update_post_meta( $payment );

			do_action( 'pronamic_pay_update_payment', $payment );
		}

		$this->payment = null;
	}

	/**
	 * Create payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L47-L76
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

		$customer = $payment->get_customer();

		$result = wp_insert_post(
			array(
				'post_type'        => 'pronamic_payment',
				'post_date_gmt'    => $this->get_mysql_utc_date( $payment->date ),
				'post_title'       => $title,
				'post_author'      => null === $customer ? null : $customer->get_user_id(),
				'pronamic_payment' => $payment,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		do_action( 'pronamic_pay_new_payment', $payment );

		return true;
	}

	/**
	 * Update payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to update in this data store.
	 */
	public function update( Payment $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return false;
		}

		$data = array(
			'ID'               => $id,
			'pronamic_payment' => $payment,
		);

		$result = wp_update_post( $data, true );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Save payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to save in this data store.
	 * @return boolean True if saved, false otherwise.
	 */
	public function save( $payment ) {
		$id = $payment->get_id();

		$result = empty( $id ) ? $this->create( $payment ) : $this->update( $payment );

		return $result;
	}

	/**
	 * Read payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-order.php#L85-L111
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L78-L111
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L81-L136
	 * @link https://developer.wordpress.org/reference/functions/get_post/
	 * @link https://developer.wordpress.org/reference/classes/wp_post/
	 *
	 * @param Payment $payment The payment to read from this data store.
	 */
	public function read( Payment $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$payment->post  = get_post( $id );
		$payment->title = get_the_title( $id );
		$payment->date  = new DateTime(
			get_post_field( 'post_date_gmt', $id, 'raw' ),
			new DateTimeZone( 'UTC' )
		);

		$content = get_post_field( 'post_content', $id, 'raw' );

		$json = json_decode( $content );

		if ( is_object( $json ) ) {
			Payment::from_json( $json, $payment );
		}

		// Set user ID from `post_author` field if not set from payment JSON.
		$customer = $payment->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();

			$payment->set_customer( $customer );
		}

		if ( null === $customer->get_user_id() ) {
			$post_author = get_post_field( 'post_author', $id, 'raw' );

			$customer->set_user_id( intval( $post_author ) );
		}

		$this->read_post_meta( $payment );
	}

	/**
	 * Get meta status label.
	 *
	 * @param string|null $meta_status The payment meta status to get the status label for.
	 * @return string|null
	 */
	public function get_meta_status_label( $meta_status ) {
		$post_status = $this->get_post_status_from_meta_status( $meta_status );

		if ( empty( $post_status ) ) {
			return null;
		}

		$status_object = get_post_status_object( $post_status );

		if ( isset( $status_object, $status_object->label ) ) {
			return $status_object->label;
		}

		return null;
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
	 * Read post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-data.php#L462-L507
	 * @param Payment $payment The payment to read.
	 */
	protected function read_post_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$payment->config_id           = $this->get_meta_int( $id, 'config_id' );
		$payment->key                 = $this->get_meta_string( $id, 'key' );
		$payment->method              = $this->get_meta_string( $id, 'method' );
		$payment->issuer              = $this->get_meta_string( $id, 'issuer' );
		$payment->order_id            = $this->get_meta_string( $id, 'order_id' );
		$payment->transaction_id      = $this->get_meta_string( $id, 'transaction_id' );
		$payment->entrance_code       = $this->get_meta_string( $id, 'entrance_code' );
		$payment->action_url          = $this->get_meta_string( $id, 'action_url' );
		$payment->source              = $this->get_meta_string( $id, 'source' );
		$payment->source_id           = $this->get_meta_string( $id, 'source_id' );
		$payment->description         = $this->get_meta_string( $id, 'description' );
		$payment->email               = $this->get_meta_string( $id, 'email' );
		$payment->status              = $this->get_meta_string( $id, 'status' );
		$payment->analytics_client_id = $this->get_meta_string( $id, 'analytics_client_id' );
		$payment->subscription_id     = $this->get_meta_int( $id, 'subscription_id' );
		$payment->recurring_type      = $this->get_meta_string( $id, 'recurring_type' );
		$payment->recurring           = $this->get_meta_bool( $id, 'recurring' );
		$payment->start_date          = $this->get_meta_date( $id, 'start_date' );
		$payment->end_date            = $this->get_meta_date( $id, 'end_date' );

		$payment->set_version( $this->get_meta_string( $id, 'version' ) );

		// Legacy.
		parent::read_post_meta( $payment );
	}

	/**
	 * Get update meta.
	 *
	 * @param Payment $payment The payment to update.
	 * @param array   $meta    Meta array.
	 *
	 * @return array
	 */
	protected function get_update_meta( $payment, $meta = array() ) {
		$customer = $payment->get_customer();

		$meta = array(
			'config_id'               => $payment->config_id,
			'key'                     => $payment->key,
			'order_id'                => $payment->order_id,
			'currency'                => $payment->get_total_amount()->get_currency()->get_alphabetic_code(),
			'amount'                  => $payment->get_total_amount()->format(),
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
			'email'                   => ( null === $customer ? null : $customer->get_email() ),
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

		$meta = parent::get_update_meta( $payment, $meta );

		return $meta;
	}

	/**
	 * Update payment post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to update.
	 */
	private function update_post_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$meta = $this->get_update_meta( $payment );

		foreach ( $meta as $meta_key => $meta_value ) {
			$this->update_meta( $id, $meta_key, $meta_value );
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

		if ( empty( $id ) ) {
			return;
		}

		$previous_status = $this->get_meta( $id, 'status' );

		$this->update_meta( $id, 'status', $payment->status );

		if ( $previous_status !== $payment->status ) {
			$old = $previous_status;
			$old = empty( $old ) ? 'unknown' : $old;
			$old = strtolower( $old );

			$new = $payment->status;
			$new = empty( $new ) ? 'unknown' : $new;
			$new = strtolower( $new );

			$can_redirect = false;

			do_action( 'pronamic_payment_status_update_' . $payment->source . '_' . $old . '_to_' . $new, $payment, $can_redirect, $previous_status, $payment->status );
			do_action( 'pronamic_payment_status_update_' . $payment->source, $payment, $can_redirect, $previous_status, $payment->status );
			do_action( 'pronamic_payment_status_update', $payment, $can_redirect, $previous_status, $payment->status );
		}
	}
}
