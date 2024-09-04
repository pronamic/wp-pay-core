<?php
/**
 * Payments Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPeriod;

/**
 * Title: Payments data store CPT
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @see     https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @see     https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.7.1
 * @since   3.7.0
 */
class PaymentsDataStoreCPT extends LegacyPaymentsDataStoreCPT {
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

		$this->payments = [];

		$this->status_map = [
			PaymentStatus::CANCELLED  => 'payment_cancelled',
			PaymentStatus::EXPIRED    => 'payment_expired',
			PaymentStatus::FAILURE    => 'payment_failed',
			PaymentStatus::REFUNDED   => 'payment_refunded',
			PaymentStatus::SUCCESS    => 'payment_completed',
			PaymentStatus::OPEN       => 'payment_pending',
			PaymentStatus::ON_HOLD    => 'payment_on_hold',
			PaymentStatus::AUTHORIZED => 'payment_authorized',
		];
	}

	/**
	 * Preserves the initial JSON post_content passed to save into the post.
	 *
	 * This is needed to prevent KSES and other {@see 'content_save_pre'} filters
	 * from corrupting JSON data.
	 *
	 * @link https://github.com/pronamic/wp-pay-core/issues/160
	 * @link https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
	 * @param array $data                An array of slashed and processed post data.
	 * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as originally passed to wp_insert_post().
	 * @return array Filtered post data.
	 */
	public function preserve_post_content( $data, $postarr, $unsanitized_postarr ) {
		if ( ! \array_key_exists( 'post_type', $data ) ) {
			return $data;
		}

		if ( 'pronamic_payment' !== $data['post_type'] ) {
			return $data;
		}

		if ( ! \array_key_exists( 'post_content', $unsanitized_postarr ) ) {
			return $data;
		}

		$data['post_content'] = $unsanitized_postarr['post_content'];

		return $data;
	}

	/**
	 * Get payment by ID.
	 *
	 * @param int $id Payment ID.
	 * @return Payment|null
	 */
	public function get_payment( $id ) {
		if ( \array_key_exists( $id, $this->payments ) ) {
			return $this->payments[ $id ];
		}

		if ( empty( $id ) ) {
			return null;
		}

		$id = (int) $id;

		$post_type = \get_post_type( $id );

		if ( 'pronamic_payment' !== $post_type ) {
			return null;
		}

		$payment = new Payment();

		$payment->set_id( $id );

		$this->payments[ $id ] = $payment;

		$this->read( $payment );

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
	 * Get post data.
	 *
	 * @param Payment $payment Payment.
	 * @param array   $data    Post data.
	 * @return array
	 * @throws \Exception Throws an exception if an error occurs while encoding the payment to JSON.
	 */
	private function get_post_data( Payment $payment, $data ) {
		$json_string = \wp_json_encode( $payment->get_json() );

		if ( false === $json_string ) {
			throw new \Exception( 'Error occurred while encoding the payment to JSON.' );
		}

		$data['post_content']   = \wp_slash( $json_string );
		$data['post_mime_type'] = 'application/json';
		$data['post_name']      = $payment->get_slug();

		$status = $this->get_post_status_from_meta_status( $payment->get_status() );

		if ( null !== $status ) {
			$data['post_status'] = $status;
		}

		return $data;
	}

	/**
	 * Create payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L47-L76
	 *
	 * @param Payment $payment The payment to create in this data store.
	 * @return bool
	 * @throws \Exception Throws exception when create fails.
	 */
	public function create( Payment $payment ) {
		/**
		 * Pre-create payment.
		 *
		 * @param Payment $payment Payment.
		 */
		\do_action( 'pronamic_pay_pre_create_payment', $payment );

		$customer = $payment->get_customer();

		$customer_user_id = null === $customer ? 0 : $customer->get_user_id();

		$result = \wp_insert_post(
			$this->get_post_data(
				$payment,
				[
					'post_type'     => 'pronamic_payment',
					'post_date_gmt' => $this->get_mysql_utc_date( $payment->date ),
					'post_title'    => \sprintf(
						'Payment %s',
						$payment->get_key()
					),
					'post_author'   => null === $customer_user_id ? 0 : $customer_user_id,
				]
			),
			true
		);

		if ( \is_wp_error( $result ) ) {
			throw new \Exception( 'Could not create payment' );
		}

		$payment->set_id( $result );
		$payment->post = \get_post( $result );

		$this->payments[ $result ] = $payment;

		/**
		 * New payment created.
		 *
		 * @param Payment $payment Payment.
		 */
		do_action( 'pronamic_pay_new_payment', $payment );

		return true;
	}

	/**
	 * Update payment.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 *
	 * @param Payment $payment The payment to update in this data store.
	 * @return bool
	 * @throws \Exception Throws exception when update fails.
	 */
	public function update( Payment $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return false;
		}

		$result = \wp_update_post(
			$this->get_post_data(
				$payment,
				[
					'ID' => $id,
				]
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			throw new \Exception( 'Could not update payment' );
		}

		$payment->post = \get_post( $result );

		$this->payments[ $result ] = $payment;

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

		\add_filter( 'wp_insert_post_data', [ $this, 'preserve_post_content' ], 5, 3 );

		$result = empty( $id ) ? $this->create( $payment ) : $this->update( $payment );

		\remove_filter( 'wp_insert_post_data', [ $this, 'preserve_post_content' ], 5 );

		$this->update_post_meta( $payment );

		/**
		 * Payment updated.
		 *
		 * @param Payment $payment Payment.
		 */
		do_action( 'pronamic_pay_update_payment', $payment );

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
	 * @return void
	 * @throws \Exception Throws exception if payment date can not be set.
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

		$payment->set_slug( get_post_field( 'post_name', $id, 'raw' ) );

		// Set user ID from `post_author` field if not set from payment JSON.
		$customer = $payment->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();

			$payment->set_customer( $customer );
		}

		if ( null === $customer->get_user_id() ) {
			$post_author = intval( get_post_field( 'post_author', $id, 'raw' ) );

			if ( ! empty( $post_author ) ) {
				$customer->set_user_id( $post_author );
			}
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
			[
				'label' => __( 'Config ID', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'key',
			[
				'label' => __( 'Key', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'method',
			[
				'label'           => __( 'Method', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'currency',
			[
				'label'          => __( 'Currency', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'amount',
			[
				'label'          => __( 'Amount', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'issuer',
			[
				'label'           => __( 'Issuer', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'order_id',
			[
				'label'          => __( 'Order ID', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'transaction_id',
			[
				'label' => __( 'Transaction ID', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'entrance_code',
			[
				'label'           => __( 'Entrance Code', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'action_url',
			[
				'label'           => __( 'Action URL', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'source',
			[
				'label' => __( 'Source', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'source_id',
			[
				'label' => __( 'Source ID', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'description',
			[
				'label'           => __( 'Description', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'language',
			[
				'label'           => __( 'Language', 'pronamic_ideal' ),
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'locale',
			[
				'label'           => __( 'Locale', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'email',
			[
				'label'           => __( 'Email', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'anonymize',
			]
		);

		$this->register_meta_key(
			'status',
			[
				'label'          => __( 'Status', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'customer_name',
			[
				'label'           => __( 'Customer Name', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'address',
			[
				'label'           => __( 'Address', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'postal_code',
			[
				'label'           => __( 'Postal Code', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'city',
			[
				'label'           => __( 'City', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'country',
			[
				'label'           => __( 'Country', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'telephone_number',
			[
				'label'           => __( 'Telephone Number', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'consumer_name',
			[
				'label'           => __( 'Consumer Name', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'consumer_account_number',
			[
				'label'           => __( 'Consumer Account Number', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'consumer_iban',
			[
				'label'           => __( 'Consumer IBAN', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'consumer_bic',
			[
				'label'           => __( 'Consumer BIC', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'consumer_city',
			[
				'label'           => __( 'Consumer City', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'subscription_id',
			[
				'label'          => __( 'Subscription ID', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'recurring_type',
			[
				'label'          => __( 'Recurring Type', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'recurring',
			[
				'label' => __( 'Recurring', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'start_date',
			[
				'label'          => __( 'Start Date', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'end_date',
			[
				'label'          => __( 'End Date', 'pronamic_ideal' ),
				'privacy_export' => true,
			]
		);

		$this->register_meta_key(
			'user_agent',
			[
				'label'           => __( 'User Agent', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'user_ip',
			[
				'label'           => __( 'User IP', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);
	}

	/**
	 * Read post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-data.php#L462-L507
	 * @param Payment $payment The payment to read.
	 * @return void
	 */
	protected function read_post_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$payment->status = $this->get_meta_string( $id, 'status' );

		// Action URL.
		$action_url = $payment->get_action_url();

		if ( empty( $action_url ) ) {
			$action_url = $this->get_meta_string( $id, 'action_url' );

			$payment->set_action_url( $action_url );
		}

		// Legacy.
		parent::read_post_meta( $payment );

		// Transaction ID.
		if ( empty( $payment->transaction_id ) ) {
			$payment->transaction_id = $this->get_meta_string( $id, 'transaction_id' );
		}

		// Amount.
		$amount = $payment->get_meta( 'amount' );

		$amount_value = $payment->get_total_amount()->get_value();

		if ( empty( $amount_value ) && ! empty( $amount ) ) {
			$payment->set_total_amount(
				new Money(
					$amount,
					$payment->get_meta( 'currency' )
				)
			);
		}

		// Subscription.
		$subscription_id = $this->get_meta_int( $id, 'subscription_id' );

		if ( ! empty( $subscription_id ) ) {
			$subscription = \get_pronamic_subscription( $subscription_id );

			if ( null !== $subscription ) {
				$payment->add_subscription( $subscription );
			}
		}

		// Meta.
		$keys = [
			'_pronamic_payment_issuer' => 'issuer',
		];

		foreach ( $keys as $post_meta_key => $payment_meta_key ) {
			$payment_meta_value = $payment->get_meta( $payment_meta_key );
			$post_meta_value    = \get_post_meta( $id, $post_meta_key, true );

			if ( empty( $payment_meta_value ) && ! empty( $post_meta_value ) ) {
				$payment->set_meta( $payment_meta_key, $post_meta_value );
			}
		}

		// Legacy periods.
		$periods = $payment->get_periods();

		if ( null === $periods ) {
			$start_date = \get_post_meta( $id, '_pronamic_payment_start_date', true );
			$end_date   = \get_post_meta( $id, '_pronamic_payment_end_date', true );

			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$subscriptions = $payment->get_subscriptions();

				$subscription = reset( $subscriptions );

				if ( false !== $subscription ) {
					$phases = $subscription->get_phases();

					$phase = reset( $phases );

					if ( false !== $phase ) {
						$period = new SubscriptionPeriod(
							$phase,
							new DateTime( $start_date ),
							new DateTime( $end_date ),
							$payment->get_total_amount()
						);

						$payment->add_period( $period );
					}
				}
			}
		}
	}

	/**
	 * Update payment post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Payment $payment The payment to update.
	 * @return void
	 */
	private function update_post_meta( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$customer = $payment->get_customer();

		$this->update_meta( $id, 'config_id', $payment->config_id );
		$this->update_meta( $id, 'source', $payment->source );
		$this->update_meta( $id, 'source_id', $payment->source_id );
		$this->update_meta( $id, 'email', ( null === $customer ? null : $customer->get_email() ) );
		$this->update_meta( $id, 'purchase_id', $payment->get_meta( 'purchase_id' ) );
		$this->update_meta( $id, 'transaction_id', $payment->get_transaction_id() );
		$this->update_meta( $id, 'version', $payment->get_version() );

		// Subscriptions.
		$meta_key = $this->get_meta_key( 'subscription_id' );

		$subscriptions_ids = \get_post_meta( $id, $meta_key );

		foreach ( $payment->get_subscriptions() as $subscription ) {
			$subscription_id = $subscription->get_id();

			if ( ! in_array( $subscription_id, $subscriptions_ids, true ) ) {
				\add_post_meta( $id, $meta_key, $subscription_id, false );
			}
		}

		$this->update_meta_status( $payment );
	}

	/**
	 * Update meta status.
	 *
	 * @param Payment $payment The payment to update the status for.
	 * @return void
	 */
	public function update_meta_status( $payment ) {
		$id = $payment->get_id();

		if ( empty( $id ) ) {
			return;
		}

		// Clean post cache to prevent duplicate status updates.
		\clean_post_cache( $id );

		$previous_status = $this->get_meta( $id, 'status' );

		$this->update_meta( $id, 'status', $payment->status );

		if ( $previous_status !== $payment->status ) {
			if ( empty( $previous_status ) ) {
				$previous_status = null;
			}

			$can_redirect = false;

			$source = $payment->source;

			$updated_status = $payment->status;

			$old_status = empty( $previous_status ) ? 'unknown' : strtolower( $previous_status );

			$new_status = empty( $updated_status ) ? 'unknown' : strtolower( $updated_status );

			/**
			 * Payment status updated for plugin integration source from old to new status.
			 *
			 * [`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
			 * [`{$old_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)
			 * [`{$new_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)
			 *
			 * @param Payment     $payment         Payment.
			 * @param bool        $can_redirect    Flag to indicate if redirect is allowed after the payment update.
			 * @param null|string $previous_status Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
			 * @param null|string $updated_status  Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
			 */
			do_action( 'pronamic_payment_status_update_' . $source . '_' . $old_status . '_to_' . $new_status, $payment, $can_redirect, $previous_status, $updated_status );

			/**
			 * Payment status updated for plugin integration source.
			 *
			 * [`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
			 *
			 * @param Payment     $payment         Payment.
			 * @param bool        $can_redirect    Flag to indicate if redirect is allowed after the payment update.
			 * @param null|string $previous_status Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
			 * @param null|string $updated_status  Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)).
			 */
			do_action( 'pronamic_payment_status_update_' . $source, $payment, $can_redirect, $previous_status, $updated_status );

			/**
			 * Payment status updated.
			 *
			 * @param Payment     $payment         Payment.
			 * @param bool        $can_redirect    Flag to indicate if redirect is allowed after the payment update.
			 * @param null|string $previous_status Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
			 * @param null|string $updated_status  Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
			 */
			do_action( 'pronamic_payment_status_update', $payment, $can_redirect, $previous_status, $updated_status );
		}
	}
}
