<?php
/**
 * Subscriptions Data Store CPT
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DatePeriod;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Payments\LegacyPaymentsDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\TaxedMoneyJsonTransformer;

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
	 * Subscription.
	 *
	 * @var Subscription|null
	 */
	private $subscription;

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

		$this->subscriptions = array();

		$this->status_map = array(
			SubscriptionStatus::CANCELLED => 'subscr_cancelled',
			SubscriptionStatus::EXPIRED   => 'subscr_expired',
			SubscriptionStatus::FAILURE   => 'subscr_failed',
			SubscriptionStatus::ACTIVE    => 'subscr_active',
			SubscriptionStatus::ON_HOLD   => 'subscr_on_hold',
			SubscriptionStatus::OPEN      => 'subscr_pending',
			SubscriptionStatus::COMPLETED => 'subscr_completed',
			// Map payment status `Success` for backwards compatibility.
			PaymentStatus::SUCCESS        => 'subscr_active',
		);
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		add_filter( 'wp_insert_post_data', array( $this, 'insert_subscription_post_data' ), 10, 2 );

		add_action( 'save_post_pronamic_pay_subscr', array( $this, 'save_post_meta' ), 100, 3 );
	}

	/**
	 * Get subscription by ID.
	 *
	 * @param int $id Payment ID.
	 * @return Subscription|null
	 */
	public function get_subscription( $id ) {
		if ( ! isset( $this->subscriptions[ $id ] ) ) {
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
		}

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
	 * Get meta status from post status.
	 *
	 * @param string $post_status Post status.
	 * @return string|null
	 */
	private function get_meta_status_from_post_status( $post_status ) {
		$key = array_search( $post_status, $this->status_map, true );

		if ( false !== $key ) {
			return \strval( $key );
		}

		return null;
	}

	/**
	 * Complement subscription post data.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.0.3/wp-includes/post.php#L3515-L3523
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array
	 * @throws \Exception When inserting subscription post data JSON string fails.
	 */
	public function insert_subscription_post_data( $data, $postarr ) {
		$this->subscription = null;

		if ( isset( $postarr['pronamic_subscription'] ) ) {
			$this->subscription = $postarr['pronamic_subscription'];
		} elseif ( isset( $postarr['ID'] ) ) {
			$post_id = $postarr['ID'];

			if ( 'pronamic_pay_subscr' === get_post_type( $post_id ) ) {
				$this->subscription = $this->get_subscription( $post_id );
			}
		}

		if ( $this->subscription instanceof Subscription ) {
			$subscription = $this->subscription;

			// Update subscription from post array.
			$this->update_subscription_form_post_array( $subscription, $postarr );

			if ( ! isset( $data['post_status'] ) || 'trash' !== $data['post_status'] ) {
				$data['post_status'] = $this->get_post_status_from_meta_status( $subscription->get_status() );
			}

			// Data.
			$json_string = wp_json_encode( $subscription->get_json() );

			if ( false === $json_string ) {
				throw new \Exception( 'Error inserting subscription post data as JSON.' );
			}

			$data['post_content']   = wp_slash( $json_string );
			$data['post_mime_type'] = 'application/json';
		}

		return $data;
	}

	/**
	 * Update subscription from post array.
	 *
	 * @param Subscription $subscription Subscription.
	 * @param array        $postarr      Post data array.
	 * @return void
	 * @throws \Exception Throws exception if amount could not be parsed to Money object.
	 */
	private function update_subscription_form_post_array( $subscription, $postarr ) {
		if ( isset( $postarr['pronamic_subscription_post_status'] ) ) {
			$post_status = sanitize_text_field( stripslashes( $postarr['pronamic_subscription_post_status'] ) );
			$meta_status = $this->get_meta_status_from_post_status( $post_status );

			if ( null !== $meta_status ) {
				$subscription->set_status( $meta_status );
			}
		}

		if ( ! isset( $postarr['pronamic_subscription_update_nonce'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'pronamic_subscription_update', 'pronamic_subscription_update_nonce' ) ) {
			return;
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
	 * @return void
	 */
	public function save_post_meta( $post_id, $post, $update ) {
		if ( $this->subscription instanceof Subscription ) {
			if ( ! $update && null === $this->subscription->get_id() ) {
				$this->subscription->set_id( $post_id );
				$this->subscription->post = $post;
			}

			$this->update_post_meta( $this->subscription );
		}

		$this->subscription = null;
	}

	/**
	 * Create subscription.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.2.6/includes/data-stores/abstract-wc-order-data-store-cpt.php#L47-L76
	 *
	 * @param Subscription $subscription Create the specified subscription in this data store.
	 * @return bool
	 */
	public function create( $subscription ) {
		$result = wp_insert_post(
			array(
				'post_type'             => 'pronamic_pay_subscr',
				'post_date_gmt'         => $this->get_mysql_utc_date( $subscription->date ),
				'post_title'            => sprintf(
					'Subscription – %s',
					date_i18n( _x( 'M d, Y @ h:i A', 'Subscription title date format parsed by `date_i18n`.', 'pronamic_ideal' ) )
				),
				'post_author'           => $subscription->user_id,
				'pronamic_subscription' => $subscription,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$this->update_post_meta( $subscription );

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
	 */
	public function update( $subscription ) {
		$id = $subscription->get_id();

		if ( empty( $id ) ) {
			return false;
		}

		$data = array(
			'ID'                    => $id,
			'pronamic_subscription' => $subscription,
		);

		$result = wp_update_post( $data, true );

		if ( is_wp_error( $result ) ) {
			return false;
		}

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

		$result = empty( $id ) ? $this->create( $subscription ) : $this->update( $subscription );

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
			$amount = new TaxedMoney(
				(string) $this->get_meta( $id, 'amount' ),
				(string) $this->get_meta_string( $id, 'currency' )
			);

			if ( \property_exists( $json, 'total_amount' ) ) {
				$amount = TaxedMoneyJsonTransformer::from_json( $json->total_amount );
			}

			// Phase.
			$start_date = $subscription->start_date;

			if ( null === $start_date ) {
				$start_date = clone $subscription->get_date();
			}

			$interval_spec = 'P' . $subscription->get_interval() . $subscription->get_interval_period();

			$phase = $subscription->new_phase(
				$start_date,
				$interval_spec,
				$amount
			);

			$phase->set_total_periods( $subscription->get_frequency() );

			// Set periods created.
			$end_date = $subscription->get_next_payment_date();

			if ( null === $end_date ) {
				$end_date = $subscription->get_expiry_date();
			}

			if ( null !== $end_date ) {
				$period = new DatePeriod( $start_date, new \DateInterval( $interval_spec ), $end_date );

				$phase->set_periods_created( \iterator_count( $period ) );
			}
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
			array(
				'label' => __( 'Config ID', 'pronamic_ideal' ),
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
			'currency',
			array(
				'label' => __( 'Currency', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'amount',
			array(
				'label' => __( 'Amount', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'frequency',
			array(
				'label' => __( 'Frequency', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'interval',
			array(
				'label' => __( 'Interval', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'interval_period',
			array(
				'label' => __( 'Interval Period', 'pronamic_ideal' ),
			)
		);

		$this->register_meta_key(
			'transaction_id',
			array(
				'label'           => __( 'Transaction ID', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
		);

		$this->register_meta_key(
			'status',
			array(
				'label' => __( 'Status', 'pronamic_ideal' ),
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
			'email',
			array(
				'label'           => __( 'Email', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'anonymize',
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
			'payment_method',
			array(
				'label'           => __( 'Payment Method', 'pronamic_ideal' ),
				'privacy_export'  => true,
				'privacy_erasure' => 'erase',
			)
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

		$subscription->config_id       = $this->get_meta_int( $id, 'config_id' );
		$subscription->key             = $this->get_meta_string( $id, 'key' );
		$subscription->source          = $this->get_meta_string( $id, 'source' );
		$subscription->source_id       = $this->get_meta_string( $id, 'source_id' );
		$subscription->frequency       = $this->get_meta_int( $id, 'frequency' );
		$subscription->interval        = $this->get_meta_int( $id, 'interval' );
		$subscription->interval_period = $this->get_meta_string( $id, 'interval_period' );
		$subscription->transaction_id  = $this->get_meta_string( $id, 'transaction_id' );
		$subscription->status          = $this->get_meta_string( $id, 'status' );
		$subscription->description     = $this->get_meta_string( $id, 'description' );
		$subscription->email           = $this->get_meta_string( $id, 'email' );
		$subscription->customer_name   = $this->get_meta_string( $id, 'customer_name' );
		$subscription->payment_method  = $this->get_meta_string( $id, 'payment_method' );

		// First Payment.
		$first_payment = $subscription->get_first_payment();

		if ( is_object( $first_payment ) ) {
			if ( empty( $subscription->config_id ) ) {
				$subscription->config_id = $first_payment->config_id;
			}

			if ( empty( $subscription->payment_method ) ) {
				$subscription->payment_method = $first_payment->method;
			}

			$customer = $subscription->get_customer();

			$first_customer = $first_payment->get_customer();

			if ( null !== $customer && null !== $first_customer ) {
				$user_id = $customer->get_user_id();

				$first_customer_user_id = $first_customer->get_user_id();

				if ( empty( $user_id ) && ! empty( $first_customer_user_id ) ) {
					$customer->set_user_id( $first_customer_user_id );
				}
			}
		}

		// Date interval.
		$date_interval = $subscription->get_date_interval();

		// Start Date.
		$start_date = $this->get_meta_date( $id, 'start_date' );

		if ( empty( $start_date ) ) {
			// If no meta start date is set, use subscription date.
			$start_date = clone $subscription->date;
		}

		$subscription->start_date = $start_date;

		// End Date.
		$end_date = $this->get_meta_date( $id, 'end_date' );

		if ( empty( $end_date ) && null !== $subscription->frequency && null !== $date_interval ) {
			// @link https://stackoverflow.com/a/10818981/6411283
			$period = new DatePeriod( $start_date, $date_interval, $subscription->frequency );

			$dates = iterator_to_array( $period );

			$end_date = end( $dates );
		}

		$subscription->end_date = $end_date;

		// Expiry Date.
		$expiry_date = $this->get_meta_date( $id, 'expiry_date' );

		if ( empty( $expiry_date ) && null !== $date_interval ) {
			// If no meta expiry date is set, use start date + 1 interval period.
			$expiry_date = clone $start_date;

			$expiry_date->add( $date_interval );
		}

		$subscription->expiry_date = $expiry_date;

		// Next Payment Date.
		$subscription->next_payment_date = $this->get_meta_date( $id, 'next_payment' );

		// Next Payment Delivery Date.
		$subscription->next_payment_delivery_date = $this->get_meta_date( $id, 'next_payment_delivery_date' );

		if ( empty( $subscription->next_payment_delivery_date ) && null !== $subscription->next_payment_date ) {
			$subscription->next_payment_delivery_date = clone $subscription->next_payment_date;
		}

		// Legacy.
		parent::read_post_meta( $subscription );
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
		$this->update_meta( $id, 'key', $subscription->key );
		$this->update_meta( $id, 'source', $subscription->source );
		$this->update_meta( $id, 'source_id', $subscription->source_id );
		$this->update_meta( $id, 'frequency', $subscription->frequency );
		$this->update_meta( $id, 'interval', $subscription->interval );
		$this->update_meta( $id, 'interval_period', $subscription->interval_period );
		$this->update_meta( $id, 'description', $subscription->description );
		$this->update_meta( $id, 'email', ( null === $customer ? null : $customer->get_email() ) );
		$this->update_meta( $id, 'customer_name', ( null === $customer ? null : strval( $customer->get_name() ) ) );
		$this->update_meta( $id, 'payment_method', $subscription->payment_method );
		$this->update_meta( $id, 'start_date', $subscription->start_date );
		$this->update_meta( $id, 'end_date', $subscription->end_date );
		$this->update_meta( $id, 'expiry_date', $subscription->expiry_date );
		$this->update_meta( $id, 'next_payment', $subscription->next_payment_date );
		$this->update_meta( $id, 'next_payment_delivery_date', $subscription->next_payment_delivery_date );

		$display_phase = $subscription->get_display_phase();

		if ( null !== $display_phase ) {
			$this->update_meta( $id, 'currency', $display_phase->get_amount()->get_currency()->get_alphabetic_code() );
			$this->update_meta( $id, 'amount', $display_phase->get_amount()->get_number()->get_value() );
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
			 * **Source**
			 *
			 * Plugin | Source
			 * ------ | ------
			 * Charitable | `charitable`
			 * Contact Form 7 | `contact-form-7`
			 * Event Espresso | `eventespresso`
			 * Event Espresso (legacy) | `event-espresso`
			 * Formidable Forms | `formidable-forms`
			 * Give | `give`
			 * Gravity Forms | `gravityformsideal`
			 * MemberPress | `memberpress`
			 * Ninja Forms | `ninja-forms`
			 * s2Member | `s2member`
			 * WooCommerce | `woocommerce`
			 * WP eCommerce | `wp-e-commerce`
			 *
			 * **Action status**
			 *
			 * Status | Value
			 * ------ | -----
			 * (empty) | `unknown`
			 * Active | `active`
			 * Cancelled | `cancelled`
			 * Completed | `completed`
			 * Expired | `expired`
			 * Failure | `failure`
			 * On Hold | `on_hold`
			 * Open | `open`
			 *
			 * **Subscription status**
			 *
			 * Status | Value
			 * ------ | -----
			 * Active | `Active`
			 * Cancelled | `Cancelled`
			 * Completed | `Completed`
			 * Expired | `Expired`
			 * Failure | `Failure`
			 * On Hold | `On Hold`
			 * Open | `Open`
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous subscription status.
			 * @param string       $updated_status  Updated subscription status.
			 */
			do_action( 'pronamic_subscription_status_update_' . $source . '_' . $old_status . '_to_' . $new_status, $subscription, $can_redirect, $previous_status, $updated_status );

			/**
			 * Subscription status updated for plugin integration source.
			 *
			 * **Source**
			 *
			 * Plugin | Source
			 * ------ | ------
			 * Charitable | `charitable`
			 * Contact Form 7 | `contact-form-7`
			 * Event Espresso | `eventespresso`
			 * Event Espresso (legacy) | `event-espresso`
			 * Formidable Forms | `formidable-forms`
			 * Give | `give`
			 * Gravity Forms | `gravityformsideal`
			 * MemberPress | `memberpress`
			 * Ninja Forms | `ninja-forms`
			 * s2Member | `s2member`
			 * WooCommerce | `woocommerce`
			 * WP eCommerce | `wp-e-commerce`
			 *
			 * **Action status**
			 *
			 * Status | Value
			 * ------ | -----
			 * (empty) | `unknown`
			 * Active | `active`
			 * Cancelled | `cancelled`
			 * Completed | `completed`
			 * Expired | `expired`
			 * Failure | `failure`
			 * On Hold | `on_hold`
			 * Open | `open`
			 *
			 * **Subscription status**
			 *
			 * Status | Value
			 * ------ | -----
			 * Active | `Active`
			 * Cancelled | `Cancelled`
			 * Completed | `Completed`
			 * Expired | `Expired`
			 * Failure | `Failure`
			 * On Hold | `On Hold`
			 * Open | `Open`
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous subscription status.
			 * @param string       $updated_status  Updated subscription status.
			 */
			do_action( 'pronamic_subscription_status_update_' . $source, $subscription, $can_redirect, $previous_status, $updated_status );

			/**
			 * Subscription status updated.
			 *
			 * **Subscription status**
			 *
			 * Status | Value
			 * ------ | -----
			 * Active | `Active`
			 * Cancelled | `Cancelled`
			 * Completed | `Completed`
			 * Expired | `Expired`
			 * Failure | `Failure`
			 * On Hold | `On Hold`
			 * Open | `Open`
			 *
			 * @param Subscription $subscription    Subscription.
			 * @param bool         $can_redirect    Flag to indicate if redirect is allowed after the subscription update.
			 * @param null|string  $previous_status Previous subscription status.
			 * @param string       $updated_status  Updated subscription status.
			 */
			do_action( 'pronamic_subscription_status_update', $subscription, $can_redirect, $previous_status, $updated_status );
		}
	}
}
