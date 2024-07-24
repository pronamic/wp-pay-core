<?php
/**
 * Payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use InvalidArgumentException;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\Refunds\Refund;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPeriod;

/**
 * Payment
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   1.0.0
 */
class Payment extends PaymentInfo {
	/**
	 * The total amount of this payment.
	 *
	 * @var Money
	 */
	private $total_amount;

	/**
	 * Refunded amount.
	 *
	 * @var Money
	 */
	private $refunded_amount;

	/**
	 * Charged back amount.
	 *
	 * @var Money|null
	 */
	private $charged_back_amount;

	/**
	 * The status of this payment.
	 *
	 * @var string|null
	 */
	public $status;

	/**
	 * Failure reason.
	 *
	 * @var FailureReason|null
	 */
	public $failure_reason;

	/**
	 * The email of the user who started this payment.
	 *
	 * @var string|null
	 */
	public $email;

	/**
	 * The action URL for this payment.
	 *
	 * @var string|null
	 */
	private $action_url;

	/**
	 * The date this payment expires.
	 *
	 * @var DateTime|null
	 */
	private $expiry_date;

	/**
	 * Subscriptions.
	 *
	 * @var Subscription[]
	 */
	private $subscriptions;

	/**
	 * Subscription periods.
	 *
	 * @since 2.5.0
	 * @var SubscriptionPeriod[]|null
	 */
	private $periods;

	/**
	 * Customer.
	 *
	 * @var Customer|null
	 */
	public $customer;

	/**
	 * Billing address.
	 *
	 * @var Address|null
	 */
	public $billing_address;

	/**
	 * Shipping address.
	 *
	 * @var Address|null
	 */
	public $shipping_address;

	/**
	 * Payment lines.
	 *
	 * @var PaymentLines|null
	 */
	public $lines;

	/**
	 * Refunds.
	 *
	 * @var Refund[]
	 */
	public $refunds = [];

	/**
	 * Slug.
	 *
	 * @link https://github.com/pronamic/wp-pay-core/issues/146
	 * @var string
	 */
	private $slug = '';

	/**
	 * Construct and initialize payment object.
	 *
	 * @param integer $post_id A payment post ID or null.
	 */
	public function __construct( $post_id = null ) {
		parent::__construct( $post_id );

		$this->meta_key_prefix = '_pronamic_payment_';
		$this->subscriptions   = [];

		$this->set_status( PaymentStatus::OPEN );

		$this->set_total_amount( new Money() );

		$this->refunded_amount = new Money();

		if ( null !== $post_id ) {
			pronamic_pay_plugin()->payments_data_store->read( $this );
		}
	}

	/**
	 * Save payment.
	 *
	 * @return void
	 */
	public function save() {
		pronamic_pay_plugin()->payments_data_store->save( $this );
	}

	/**
	 * Add a note to this payment.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_insert_comment/
	 * @param string $note The note to add.
	 * @return int The new comment's ID.
	 * @throws \Exception Throws exception when adding note fails.
	 */
	public function add_note( $note ) {
		global $wpdb;

		if ( null === $this->id ) {
			throw new \Exception(
				\sprintf(
					'Could not add note "%s" to payment without ID.',
					\esc_html( $note )
				)
			);
		}

		$commentdata = [
			'comment_post_ID' => $this->id,
			'comment_content' => $note,
			'comment_type'    => 'payment_note',
			'user_id'         => \get_current_user_id(),
		];

		$result = \wp_insert_comment( $commentdata );

		if ( false === $result ) {
			/**
			 * Should we throw an exception or handle this in some other way?
			 *
			 * @link https://github.com/pronamic/wp-pronamic-pay/issues/337
			 * @todo
			 */
			throw new \Exception(
				\sprintf(
					'Could not add note "%s" to payment with ID "%s", last database error: "%s".',
					\esc_html( $note ),
					\esc_html( (string) $this->id ),
					\esc_html( $wpdb->last_error )
				)
			);
		}

		return $result;
	}

	/**
	 * Set the transaction ID.
	 *
	 * @param string|null $transaction_id Transaction ID.
	 * @return void
	 */
	public function set_transaction_id( $transaction_id ) {
		$this->transaction_id = $transaction_id;
	}

	/**
	 * Get the payment transaction ID.
	 *
	 * @return string|null
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/**
	 * Get total amount.
	 *
	 * @return Money
	 */
	public function get_total_amount() {
		return $this->total_amount;
	}

	/**
	 * Set total amount.
	 *
	 * @param Money $total_amount Total amount.
	 * @return void
	 */
	public function set_total_amount( Money $total_amount ) {
		$this->total_amount = $total_amount;
	}

	/**
	 * Get refunded amount.
	 *
	 * @return Money
	 */
	public function get_refunded_amount() {
		return $this->refunded_amount;
	}

	/**
	 * Set refunded amount.
	 *
	 * @param Money $refunded_amount Refunded amount.
	 * @return void
	 */
	public function set_refunded_amount( $refunded_amount ) {
		$this->refunded_amount = $refunded_amount;
	}

	/**
	 * Get charged back amount.
	 *
	 * @return Money|null
	 */
	public function get_charged_back_amount(): ?Money {
		return $this->charged_back_amount;
	}

	/**
	 * Set charged back amount.
	 *
	 * @param Money|null $charged_back_amount Charged back amount.
	 * @return void
	 */
	public function set_charged_back_amount( ?Money $charged_back_amount ) {
		$this->charged_back_amount = $charged_back_amount;
	}

	/**
	 * Get the payment status.
	 *
	 * @todo Constant?
	 * @return string|null
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get payment status label.
	 *
	 * @return string|null
	 */
	public function get_status_label() {
		return pronamic_pay_plugin()->payments_data_store->get_meta_status_label( $this->status );
	}

	/**
	 * Set the payment status.
	 *
	 * @param string|null $status Status.
	 * @return void
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Get failure reason.
	 *
	 * @return FailureReason|null
	 */
	public function get_failure_reason() {
		return $this->failure_reason;
	}

	/**
	 * Set failure reason.
	 *
	 * @param FailureReason|null $failure_reason Failure reason.
	 * @return void
	 */
	public function set_failure_reason( FailureReason $failure_reason = null ) {
		$this->failure_reason = $failure_reason;
	}

	/**
	 * Get the pay redirect URL.
	 *
	 * @return string
	 */
	public function get_pay_redirect_url() {
		$url = add_query_arg(
			[
				'payment_redirect' => $this->id,
				'key'              => $this->key,
			],
			home_url( '/' )
		);

		return $url;
	}

	/**
	 * Get the return URL for this payment. This URL is passed to the payment providers / gateways
	 * so they know where they should return users to.
	 *
	 * @return string
	 */
	public function get_return_url() {
		$home_url = home_url( '/' );

		/**
		 * Polylang compatibility.
		 *
		 * @link https://github.com/polylang/polylang/blob/2.6.8/include/api.php#L97-L111
		 */
		if ( \function_exists( '\pll_home_url' ) ) {
			$home_url = \pll_home_url();
		}

		$url = add_query_arg(
			[
				'payment' => $this->id,
				'key'     => $this->key,
			],
			$home_url
		);

		return $url;
	}

	/**
	 * Get action URL.
	 *
	 * @return string|null
	 */
	public function get_action_url() {
		return $this->action_url;
	}

	/**
	 * Set the action URL.
	 *
	 * @param string|null $action_url Action URL.
	 * @return void
	 */
	public function set_action_url( $action_url ) {
		$this->action_url = $action_url;
	}

	/**
	 * Get expiry date.
	 *
	 * @return DateTime|null
	 */
	public function get_expiry_date() {
		return $this->expiry_date;
	}

	/**
	 * Set expiry date.
	 *
	 * @param DateTime|null $expiry_date Expiry date.
	 * @return void
	 */
	public function set_expiry_date( $expiry_date ) {
		$this->expiry_date = $expiry_date;
	}

	/**
	 * Get the return redirect URL for this payment. This URL is used after a user is returned
	 * from a payment provider / gateway to WordPress. It allows WordPress payment extensions
	 * to redirect users to the correct URL.
	 *
	 * @return string
	 */
	public function get_return_redirect_url() {
		$url = home_url( '/' );

		$payment = $this;

		/**
		 * Filters the payment return redirect URL.
		 *
		 * @param string  $text    Redirect URL.
		 * @param Payment $payment Payment.
		 */
		$url = apply_filters( 'pronamic_payment_redirect_url', $url, $payment );

		return $url;
	}

	/**
	 * Get edit payment URL.
	 *
	 * @link https://docs.woocommerce.com/wc-apidocs/source-class-WC_Order.html#1538-1546
	 *
	 * @return string
	 */
	public function get_edit_payment_url() {
		$url = add_query_arg(
			[
				'action' => 'edit',
				'post'   => $this->get_id(),
			],
			admin_url( 'post.php' )
		);

		return $url;
	}

	/**
	 * Get the source text of this payment.
	 *
	 * @return string
	 */
	public function get_source_text() {
		$pieces = [
			\ucfirst( (string) $this->get_source() ),
			$this->get_source_id(),
		];

		$pieces = array_filter( $pieces );

		$text = implode( '<br />', $pieces );

		$source = $this->get_source();

		$payment = $this;

		if ( null !== $source ) {
			/**
			 * Filters the payment source text by plugin integration source.
			 *
			 * @param string  $text    Source text.
			 * @param Payment $payment Payment.
			 */
			$text = apply_filters( 'pronamic_payment_source_text_' . $source, $text, $payment );
		}

		/**
		 * Filters the payment source text.
		 *
		 * @param string  $text    Source text.
		 * @param Payment $payment Payment.
		 */
		$text = apply_filters( 'pronamic_payment_source_text', $text, $payment );

		return $text;
	}

	/**
	 * Get source description.
	 *
	 * @return string
	 */
	public function get_source_description() {
		$payment = $this;

		$source = $payment->get_source();

		$description = (string) $source;

		/**
		 * Filters the payment source description.
		 *
		 * @param string  $description Source description.
		 * @param Payment $payment     Payment.
		 */
		$description = apply_filters( 'pronamic_payment_source_description', $description, $payment );

		if ( null !== $source ) {
			/**
			 * Filters the payment source description by plugin integration source.
			 *
			 * @param string  $description Source description.
			 * @param Payment $payment     Payment.
			 */
			$description = apply_filters( 'pronamic_payment_source_description_' . $source, $description, $payment );
		}

		return $description;
	}

	/**
	 * Get the source link for this payment.
	 *
	 * @return string|null
	 */
	public function get_source_link() {
		$url = null;

		$payment = $this;

		$source = $payment->get_source();

		/**
		 * Filters the payment source URL.
		 *
		 * @param null|string $url     Source URL.
		 * @param Payment     $payment Payment.
		 */
		$url = apply_filters( 'pronamic_payment_source_url', $url, $payment );

		if ( null !== $source ) {
			/**
			 * Filters the payment source URL by plugin integration source.
			 *
			 * @param null|string $url     Source URL.
			 * @param Payment     $payment Payment.
			 */
			$url = apply_filters( 'pronamic_payment_source_url_' . $source, $url, $payment );
		}

		return $url;
	}

	/**
	 * Get provider link for this payment.
	 *
	 * @return null|string
	 */
	public function get_provider_link() {
		$url = null;

		$payment = $this;

		/**
		 * Filters the payment provider URL.
		 *
		 * @param null|string $url     Provider URL.
		 * @param Payment     $payment Payment.
		 */
		$url = apply_filters( 'pronamic_payment_provider_url', $url, $payment );

		if ( null === $this->id ) {
			return $url;
		}

		$config_id = get_post_meta( $this->id, '_pronamic_payment_config_id', true );

		if ( empty( $config_id ) ) {
			return $url;
		}

		$gateway_id = get_post_meta( intval( $config_id ), '_pronamic_gateway_id', true );

		if ( ! empty( $gateway_id ) ) {
			/**
			 * Filters the payment provider URL by gateway identifier.
			 *
			 * @param null|string $url     Provider URL.
			 * @param Payment     $payment Payment.
			 */
			$url = apply_filters( 'pronamic_payment_provider_url_' . $gateway_id, $url, $payment );
		}

		return $url;
	}

	/**
	 * Get subscription.
	 *
	 * @deprecated Use `get_subscriptions()`.
	 * @return Subscription|null
	 */
	public function get_subscription() {
		$first = \reset( $this->subscriptions );

		if ( false === $first ) {
			return null;
		}

		return $first;
	}

	/**
	 * Get subscriptions.
	 *
	 * @return Subscription[]
	 */
	public function get_subscriptions() {
		return $this->subscriptions;
	}

	/**
	 * Connect subscription to this payment.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return void
	 */
	public function add_subscription( Subscription $subscription ) {
		if ( \in_array( $subscription, $this->subscriptions, true ) ) {
			return;
		}

		$this->subscriptions[] = $subscription;
	}

	/**
	 * Format string
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/v2.2.3/includes/abstracts/abstract-wc-email.php#L187-L195
	 *
	 * @param string $value The string to format.
	 * @return string
	 */
	public function format_string( $value ) {
		$merge_tags_controller = new PaymentMergeTagsController( $this );

		return $merge_tags_controller->format_string( $value );
	}

	/**
	 * Get payment email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get subscription periods.
	 *
	 * @since 2.5.0
	 * @return SubscriptionPeriod[]|null
	 */
	public function get_periods() {
		return $this->periods;
	}

	/**
	 * Add subscription period.
	 *
	 * @since 2.5.0
	 * @param SubscriptionPeriod $period Subscription period.
	 * @return void
	 */
	public function add_period( SubscriptionPeriod $period ) {
		if ( null === $this->periods ) {
			$this->periods = [];
		}

		$this->add_subscription( $period->get_phase()->get_subscription() );

		$this->periods[] = $period;
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Set slug.
	 *
	 * @param string $slug Slug.
	 * @return void
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Create payment from object.
	 *
	 * @param mixed        $json    JSON.
	 * @param Payment|null $payment Payment.
	 * @return Payment
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, $payment = null ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( null === $payment ) {
			$payment = new self();
		}

		PaymentInfoHelper::from_json( $json, $payment );

		if ( isset( $json->slug ) ) {
			$payment->set_slug( $json->slug );
		}

		if ( isset( $json->action_url ) ) {
			$payment->set_action_url( $json->action_url );
		}

		if ( isset( $json->total_amount ) ) {
			$payment->set_total_amount( MoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( isset( $json->refunded_amount ) ) {
			$payment->set_refunded_amount( MoneyJsonTransformer::from_json( $json->refunded_amount ) );
		}

		if ( isset( $json->charged_back_amount ) ) {
			$payment->set_charged_back_amount( MoneyJsonTransformer::from_json( $json->charged_back_amount ) );
		}

		if ( isset( $json->expiry_date ) ) {
			$payment->set_expiry_date( new DateTime( $json->expiry_date ) );
		}

		if ( isset( $json->status ) ) {
			$payment->set_status( $json->status );
		}

		if ( isset( $json->periods ) ) {
			foreach ( $json->periods as $json_period ) {
				try {
					$payment->add_period( SubscriptionPeriod::from_json( $json_period ) );
				} catch ( \Exception $exception ) {
					// For now we temporarily ignore subscription period exception due to changes in the JSON schema.
					continue;
				}
			}
		}

		if ( isset( $json->subscriptions ) ) {
			foreach ( $json->subscriptions as $json_subscription ) {
				if ( \property_exists( $json_subscription, 'id' ) ) {
					$subscription = \get_pronamic_subscription( $json_subscription->id );

					if ( null !== $subscription ) {
						$payment->add_subscription( $subscription );
					}
				}
			}
		}

		if ( isset( $json->failure_reason ) ) {
			$payment->set_failure_reason( FailureReason::from_json( $json->failure_reason ) );
		}

		if ( isset( $json->origin_id ) ) {
			$payment->set_origin_id( $json->origin_id );
		}

		if ( isset( $json->transaction_id ) ) {
			$payment->set_transaction_id( $json->transaction_id );
		}

		if ( isset( $json->refunds ) ) {
			foreach ( $json->refunds as $json_refund ) {
				$payment->refunds[] = Refund::from_json( $json_refund, $payment );
			}
		}

		return $payment;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$object = PaymentInfoHelper::to_json( $this );

		$properties = (array) $object;

		$properties['slug'] = $this->slug;

		// Action URL.
		if ( null !== $this->action_url ) {
			$properties['action_url'] = $this->action_url;
		}

		// Expiry date.
		$expiry_date = $this->get_expiry_date();

		if ( null !== $expiry_date ) {
			$properties['expiry_date'] = $expiry_date->format( \DATE_ATOM );
		}

		// Total amount.
		$total_amount = $this->get_total_amount();

		if ( null !== $total_amount ) {
			$properties['total_amount'] = $total_amount->jsonSerialize();
		}

		// Refunded amount.
		if ( ! $this->refunded_amount->is_zero() ) {
			$properties['refunded_amount'] = $this->refunded_amount->jsonSerialize();
		}

		// Charged back amount.
		$charged_back_amount = $this->get_charged_back_amount();

		if ( null !== $charged_back_amount ) {
			$properties['charged_back_amount'] = $charged_back_amount->jsonSerialize();
		}

		// Subscriptions.
		$subscriptions = $this->get_subscriptions();

		if ( \count( $subscriptions ) > 0 ) {
			$properties['subscriptions'] = [];

			foreach ( $subscriptions as $subscription ) {
				$properties['subscriptions'][] = (object) [
					'$ref' => \rest_url(
						\sprintf(
							'/%s/%s/%d',
							'pronamic-pay/v1',
							'subscriptions',
							$subscription->get_id()
						)
					),
					'id'   => $subscription->get_id(),
				];
			}
		}

		// Periods.
		$periods = $this->get_periods();

		if ( null !== $periods ) {
			foreach ( $periods as $period ) {
				$properties['periods'][] = $period->to_json();
			}
		}

		// Status.
		if ( null !== $this->get_status() ) {
			$properties['status'] = $this->get_status();
		}

		// Failure reason.
		$failure_reason = $this->get_failure_reason();

		if ( null !== $failure_reason ) {
			$properties['failure_reason'] = $failure_reason->get_json();
		}

		// Origin ID.
		$origin_id = $this->get_origin_id();

		if ( null !== $origin_id ) {
			$properties['origin_id'] = $origin_id;
		}

		// Transaction ID.
		$transaction_id = $this->get_transaction_id();

		if ( null !== $transaction_id ) {
			$properties['transaction_id'] = $transaction_id;
		}

		// Refunds.
		if ( \count( $this->refunds ) > 0 ) {
			$properties['refunds'] = $this->refunds;
		}

		$object = (object) $properties;

		return $object;
	}
}
