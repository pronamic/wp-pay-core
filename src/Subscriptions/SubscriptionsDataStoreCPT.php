<?php
/**
 * Subscriptions Data Store CPT
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\Payments\LegacyPaymentsDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Title: Subscriptions data store CPT
 *
 * @link https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.0.1
 */
class SubscriptionsDataStoreCPT extends LegacyPaymentsDataStoreCPT {
	/**
	 * Subscriptions.
	 *
	 * @var array
	 */
	private $subscriptions;

	/**
	 * Status map.
	 *
	 * @var array
	 */
	private $status_map;

	/**
	 * Construct subscriptions data store CPT object.
	 */
	public function __construct() {
		$this->meta_key_prefix = '_pronamic_subscription_';

		$this->register_meta();

		$this->subscriptions = [];

		$this->status_map = [
			SubscriptionStatus::CANCELLED => 'subscr_cancelled',
			SubscriptionStatus::EXPIRED   => 'subscr_expired',
			SubscriptionStatus::FAILURE   => 'subscr_failed',
			SubscriptionStatus::ACTIVE    => 'subscr_active',
			SubscriptionStatus::ON_HOLD   => 'subscr_on_hold',
			SubscriptionStatus::OPEN      => 'subscr_pending',
			SubscriptionStatus::COMPLETED => 'subscr_completed',
			// Map payment status `Success` for backwards compatibility.
			PaymentStatus::SUCCESS        => 'subscr_active',
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

		if ( 'pronamic_pay_subscr' !== $data['post_type'] ) {
			return $data;
		}

		if ( ! \array_key_exists( 'post_content', $unsanitized_postarr ) ) {
			return $data;
		}

		$data['post_content'] = $unsanitized_postarr['post_content'];

		return $data;
	}

	/**
	 * Get subscription by ID.
	 *
	 * @param int $id Payment ID.
	 * @return Subscription|null
	 */
	public function get_subscription( $id ) {
		if ( \array_key_exists( $id, $this->subscriptions ) ) {
			return $this->subscriptions[ $id ];
		}

		if ( empty( $id ) ) {
			return null;
		}

		$id = (int) $id;

		$post_type = \get_post_type( $id );

		if ( 'pronamic_pay_subscr' !== $post_type ) {
			return null;
		}

		$subscription = new Subscription();

		$subscription->set_id( $id );

		$this->subscriptions[ $id ] = $subscription;

		$this->read( $subscription );

		return $this->subscriptions[ $id ];
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
	 * @param Subscription $subscription Payment.
	 * @param array        $data         Post data.
	 * @return array
	 * @throws \Exception Throws an exception if an error occurs while encoding the payment to JSON.
	 */
	private function get_post_data( Subscription $subscription, $data ) {
		$json_string = \wp_json_encode( $subscription->get_json() );

		if ( false === $json_string ) {
			throw new \Exception( 'Error occurred while encoding the subscription to JSON.' );
		}

		$data['post_content']   = \wp_slash( $json_string );
		$data['post_mime_type'] = 'application/json';

		$status = $this->get_post_status_from_meta_status( $subscription->get_status() );

		if ( null !== $status ) {
			$data['post_status'] = $status;
		}

		return $data;
	}

	/**
	 * Create subscription.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L47-L76
	 *
	 * @param Subscription $subscription Create the specified subscription in this data store.
	 * @return bool
	 * @throws \Exception Throws exception when create fails.
	 */
	public function create( $subscription ) {
		/**
		 * Pre-create subscription.
		 *
		 * @param Subscription $subscription Subscription.
		 */
		\do_action( 'pronamic_pay_pre_create_subscription', $subscription );

		$customer = $subscription->get_customer();

		$customer_user_id = null === $customer ? 0 : $customer->get_user_id();

		$result = \wp_insert_post(
			$this->get_post_data(
				$subscription,
				[
					'post_type'     => 'pronamic_pay_subscr',
					'post_date_gmt' => $this->get_mysql_utc_date( $subscription->date ),
					'post_title'    => \sprintf(
						'Subscription %s',
						$subscription->get_key()
					),
					'post_author'   => null === $customer_user_id ? 0 : $customer_user_id,
				]
			),
			true
		);

		if ( \is_wp_error( $result ) ) {
			throw new \Exception(
				\sprintf(
					'Could not create subscription: "%s".',
					\esc_html( $result->get_error_message() )
				)
			);
		}

		$subscription->set_id( $result );
		$subscription->post = \get_post( $result );

		$this->subscriptions[ $result ] = $subscription;

		/**
		 * New subscription created.
		 *
		 * @param Subscription $subscription Subscription.
		 */
		do_action( 'pronamic_pay_new_subscription', $subscription );

		return true;
	}

	/**
	 * Update subscription.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 *
	 * @param Subscription $subscription The subscription to update in this data store.
	 * @return bool
	 * @throws \Exception Throws exception when update fails.
	 */
	public function update( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return false;
		}

		$result = \wp_update_post(
			$this->get_post_data(
				$subscription,
				[
					'ID' => $id,
				]
			),
			true
		);

		if ( \is_wp_error( $result ) ) {
			throw new \Exception(
				\sprintf(
					'Could not update subscription: "%s".',
					\esc_html( $result->get_error_message() )
				)
			);
		}

		$subscription->post = \get_post( $result );

		$this->subscriptions[ $result ] = $subscription;

		return true;
	}

	/**
	 * Save subscription.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L113-L154
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Subscription $subscription The subscription to save in this data store.
	 * @return boolean True if saved, false otherwise.
	 */
	public function save( $subscription ) {
		$id = $subscription->get_id();

		\add_filter( 'wp_insert_post_data', [ $this, 'preserve_post_content' ], 5, 3 );

		$result = empty( $id ) ? $this->create( $subscription ) : $this->update( $subscription );

		\remove_filter( 'wp_insert_post_data', [ $this, 'preserve_post_content' ], 5 );

		$this->update_post_meta( $subscription );

		return $result;
	}

	/**
	 * Read subscription.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L78-L111
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L81-L136
	 * @link https://developer.wordpress.org/reference/functions/get_post_field/
	 *
	 * @param Subscription $subscription The subscription to read the additional data for.
	 *
	 * @return void
	 * @throws \Exception Throws exception on invalid post date.
	 */
	public function read( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$subscription->post  = get_post( $id );
		$subscription->title = get_the_title( $id );
		$subscription->date  = new DateTime( get_post_field( 'post_date_gmt', $id, 'raw' ), new DateTimeZone( 'UTC' ) );

		$content = get_post_field( 'post_content', $id, 'raw' );

		$json = json_decode( $content );

		if ( is_object( $json ) ) {
			Subscription::from_json( $json, $subscription );
		}

		// Set user ID from `post_author` field if not set from subscription JSON.
		$customer = $subscription->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();

			$subscription->set_customer( $customer );
		}

		if ( null === $customer->get_user_id() ) {
			$post_author = intval( get_post_field( 'post_author', $id, 'raw' ) );

			if ( ! empty( $post_author ) ) {
				$customer->set_user_id( $post_author );
			}
		}

		$this->read_post_meta( $subscription );

		// Phases.
		if ( is_object( $json ) && ! property_exists( $json, 'phases' ) ) {
			// Amount.
			$amount = new Money(
				(string) $this->get_meta( $id, 'amount' ),
				(string) $this->get_meta_string( $id, 'currency' )
			);

			if ( \property_exists( $json, 'total_amount' ) ) {
				$amount = MoneyJsonTransformer::from_json( $json->total_amount );
			}

			// Phase.
			$start_date = $this->get_meta_date( $id, 'start_date' );

			if ( null === $start_date ) {
				$start_date = clone $subscription->get_date();
			}

			$interval_spec = 'P' . $this->get_meta_int( $id, 'interval' ) . $this->get_meta_string( $id, 'interval_period' );

			$phase = $subscription->new_phase(
				$start_date,
				$interval_spec,
				$amount
			);

			$phase->set_total_periods( $this->get_meta_int( $id, 'frequency' ) );
		}
	}

	/**
	 * Get meta status label.
	 *
	 * @param string|null $meta_status The subscription meta status to get the status label for.
	 * @return string|false
	 */
	public function get_meta_status_label( $meta_status ) {
		$post_status = $this->get_post_status_from_meta_status( $meta_status );

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
			[
				'label' => __( 'Config ID', 'pronamic_ideal' ),
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
			'currency',
			[
				'label' => __( 'Currency', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'amount',
			[
				'label' => __( 'Amount', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'frequency',
			[
				'label' => __( 'Frequency', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'interval',
			[
				'label' => __( 'Interval', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'interval_period',
			[
				'label' => __( 'Interval Period', 'pronamic_ideal' ),
			]
		);

		$this->register_meta_key(
			'transaction_id',
			[
				'label'           => __( 'Transaction ID', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);

		$this->register_meta_key(
			'status',
			[
				'label' => __( 'Status', 'pronamic_ideal' ),
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
			'email',
			[
				'label'           => __( 'Email', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'anonymize',
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
			'payment_method',
			[
				'label'           => __( 'Payment Method', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			]
		);
	}

	/**
	 * Read post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/abstracts/abstract-wc-data.php#L462-L507
	 *
	 * @param Subscription $subscription The subscription to read the post meta for.
	 * @return void
	 */
	protected function read_post_meta( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$subscription->transaction_id = $this->get_meta_string( $id, 'transaction_id' );
		$subscription->status         = $this->get_meta_string( $id, 'status' );

		// Payment method.
		$payment_method = $subscription->get_payment_method();

		if ( empty( $payment_method ) ) {
			$subscription->set_payment_method( $this->get_meta_string( $id, 'payment_method' ) );
		}

		// Set next date.
		$next_date = $this->get_meta_date( $id, 'next_payment' );

		$subscription->set_next_payment_date( $next_date );

		// Legacy.
		parent::read_post_meta( $subscription );

		// Read subscription data from first payment.
		$config_id = $subscription->get_config_id();

		$payment_method = $subscription->get_payment_method();

		if ( null === $config_id || null === $payment_method ) {
			$first_payment = $this->get_first_payment( $subscription );

			if ( is_object( $first_payment ) ) {
				// Gateway.
				if ( empty( $config_id ) ) {
					$subscription->set_config_id( $first_payment->get_config_id() );
				}

				// Payment method.
				if ( empty( $payment_method ) ) {
					$subscription->set_payment_method( $first_payment->get_payment_method() );
				}
			}
		}
	}

	/**
	 * Get first payment for subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return \Pronamic\WordPress\Pay\Payments\Payment|null
	 */
	private function get_first_payment( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return null;
		}

		$payments = get_pronamic_payments_by_meta(
			'_pronamic_payment_subscription_id',
			$id,
			[
				'posts_per_page' => 1,
				'orderby'        => 'post_date',
				'order'          => 'ASC',
			]
		);

		$payment = \reset( $payments );

		if ( false !== $payment ) {
			return $payment;
		}

		return null;
	}

	/**
	 * Update payment post meta.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/class-wc-order-data-store-cpt.php#L154-L257
	 * @param Subscription $subscription The subscription to update the post meta for.
	 * @return void
	 */
	private function update_post_meta( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$customer = $subscription->get_customer();

		$this->update_meta( $id, 'config_id', $subscription->config_id );
		$this->update_meta( $id, 'source', $subscription->source );
		$this->update_meta( $id, 'source_id', $subscription->source_id );
		$this->update_meta( $id, 'email', ( null === $customer ? null : $customer->get_email() ) );
		$this->update_meta( $id, 'end_date', $subscription->get_end_date() );
		$this->update_meta( $id, 'next_payment', $subscription->get_next_payment_date() );
		$this->update_meta( $id, 'next_payment_delivery_date', $subscription->get_next_payment_delivery_date() );
		$this->update_meta( $id, 'version', $subscription->get_version() );

		// Maybe delete next payment date post meta.
		if ( null === $subscription->get_next_payment_date() ) {
			\delete_post_meta( $id, $this->meta_key_prefix . 'next_payment' );
			\delete_post_meta( $id, $this->meta_key_prefix . 'next_payment_delivery_date' );
		}

		if ( null === $subscription->get_end_date() ) {
			\delete_post_meta( $id, $this->meta_key_prefix . 'end_date' );
		}

		$this->update_meta_status( $subscription );
	}

	/**
	 * Update meta status.
	 *
	 * @param Subscription $subscription The subscription to update the status for.
	 * @return void
	 */
	public function update_meta_status( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return;
		}

		$previous_status = $this->get_meta( $id, 'status' );

		$this->update_meta( $id, 'status', $subscription->status );

		if ( $previous_status !== $subscription->status ) {
			if ( empty( $previous_status ) ) {
				$previous_status = null;
			}

			$can_redirect = false;

			$source = $subscription->source;

			$updated_status = $subscription->status;

			$old_status = empty( $previous_status ) ? 'unknown' : strtolower( $previous_status );
			$old_status = \str_replace( ' ', '_', $old_status );

			$new_status = empty( $updated_status ) ? 'unknown' : strtolower( $updated_status );
			$new_status = \str_replace( ' ', '_', $new_status );

			/**
			 * Subscription status updated for plugin integration source from old to new status.
			 *
			 * [`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
			 * [`{$old_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status)
			 * [`{$new_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status)
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 * @param null|string  $updated_status  Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 */
			do_action( 'pronamic_subscription_status_update_' . $source . '_' . $old_status . '_to_' . $new_status, $subscription, $can_redirect, $previous_status, $updated_status );

			/**
			 * Subscription status updated for plugin integration source.
			 *
			 * [`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 * @param null|string  $updated_status  Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 */
			do_action( 'pronamic_subscription_status_update_' . $source, $subscription, $can_redirect, $previous_status, $updated_status );

			/**
			 * Subscription status updated.
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 * @param null|string  $updated_status  Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
			 */
			do_action( 'pronamic_subscription_status_update', $subscription, $can_redirect, $previous_status, $updated_status );
		}
	}
}
