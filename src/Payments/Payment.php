<?php
/**
 * Payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use InvalidArgumentException;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\TaxedMoneyJsonTransformer;
use WP_Post;

/**
 * Payment
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   1.0.0
 */
class Payment extends LegacyPayment {
	/**
	 * The payment post object.
	 *
	 * @var WP_Post|array
	 */
	public $post;

	/**
	 * The date of this payment.
	 *
	 * @var DateTime
	 */
	public $date;

	/**
	 * The subscription.
	 *
	 * @var Subscription
	 */
	public $subscription;

	/**
	 * The unique ID of this payment.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * The title of this payment.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The configuration ID.
	 *
	 * @var integer
	 */
	public $config_id;

	/**
	 * The key of this payment, used in URL's for security.
	 *
	 * @var string
	 */
	public $key;

	/**
	 * Identifier for the source which started this payment.
	 * For example: 'woocommerce', 'gravityforms', 'easydigitaldownloads', etc.
	 *
	 * @var string
	 */
	public $source;

	/**
	 * Unique ID at the source which started this payment, for example:
	 * - WooCommerce order ID.
	 * - Easy Digital Downloads payment ID.
	 * - Gravity Forms entry ID.
	 *
	 * @var string
	 */
	public $source_id;

	/**
	 * The purchase ID.
	 *
	 * @todo Is this required/used?
	 * @var string
	 */
	public $purchase_id;

	/**
	 * The transaction ID of this payment.
	 *
	 * @var string
	 */
	public $transaction_id;

	/**
	 * The order ID of this payment.
	 *
	 * @todo Is this required/used?
	 * @var string
	 */
	public $order_id;

	/**
	 * The total amount of this payment.
	 *
	 * @var TaxedMoney
	 */
	private $total_amount;

	/**
	 * The shipping amount of this payment.
	 *
	 * @var Money|null
	 */
	private $shipping_amount;

	/**
	 * The expiration period of this payment.
	 *
	 * @todo Is this required/used?
	 * @var string
	 */
	public $expiration_period;

	/**
	 * The entrance code of this payment.
	 *
	 * @todo Is this required/used?
	 * @var string
	 */
	public $entrance_code;

	/**
	 * The description of this payment.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * The name of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string
	 */
	public $consumer_name;

	/**
	 * The account number of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string
	 */
	public $consumer_account_number;

	/**
	 * The IBAN of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string
	 */
	public $consumer_iban;

	/**
	 * The BIC of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string
	 */
	public $consumer_bic;

	/**
	 * The city of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string
	 */
	public $consumer_city;

	/**
	 * The Google Analytics client ID of the user who started this payment.
	 *
	 * @var string
	 */
	public $analytics_client_id;

	/**
	 * Google Analytics e-commerce tracked.
	 *
	 * @var bool
	 */
	public $ga_tracked;

	/**
	 * The status of this payment.
	 *
	 * @todo   Check constant?
	 * @var string
	 */
	public $status;

	/**
	 * The email of the user who started this payment.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * The action URL for this payment.
	 *
	 * @var string
	 */
	public $action_url;

	/**
	 * The payment method chosen by the user who started this payment.
	 *
	 * @var string|null
	 */
	public $method;

	/**
	 * The issuer chosen by the user who started this payment.
	 *
	 * @var string
	 */
	public $issuer;

	/**
	 * Subscription ID.
	 *
	 * @todo Is this required?
	 * @var int
	 */
	public $subscription_id;

	/**
	 * Subscription source ID.
	 *
	 * @var int
	 */
	public $subscription_source_id;

	/**
	 * Flag to indicate a recurring payment
	 *
	 * @todo Is this required?
	 * @var boolean
	 */
	public $recurring;

	/**
	 * The recurring type.
	 *
	 * @todo Improve documentation, is this used?
	 * @var string
	 */
	public $recurring_type;

	/**
	 * Meta.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Start date if the payment is related to a specific period.
	 *
	 * @var DateTime
	 */
	public $start_date;

	/**
	 * End date if the payment is related to a specific period.
	 *
	 * @var DateTime
	 */
	public $end_date;

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
	 * Version.
	 *
	 * @var string|null
	 */
	private $version;

	/**
	 * Mode.
	 *
	 * @var string|null
	 */
	private $mode;

	/**
	 * Construct and initialize payment object.
	 *
	 * @param integer $post_id A payment post ID or null.
	 */
	public function __construct( $post_id = null ) {
		$this->id   = $post_id;
		$this->date = new DateTime();
		$this->meta = array();

		$this->set_total_amount( new TaxedMoney() );
		$this->set_status( Statuses::OPEN );

		if ( null !== $post_id ) {
			pronamic_pay_plugin()->payments_data_store->read( $this );
		}
	}

	/**
	 * Get the ID of this payment.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the ID of this payment.
	 *
	 * @param int $id Unique ID.
	 */
	public function set_id( $id ) {
		$this->id = $id;
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
	 * @param string $note The note to add.
	 */
	public function add_note( $note ) {
		$commentdata = array(
			'comment_post_ID'  => $this->id,
			'comment_content'  => $note,
			'comment_type'     => 'payment_note',
			'user_id'          => get_current_user_id(),
			'comment_approved' => true,
		);

		$comment_id = wp_insert_comment( $commentdata );

		return $comment_id;
	}

	/**
	 * Get payment date.
	 *
	 * @return DateTime
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * Set payment date.
	 *
	 * @param DateTime $date Date.
	 */
	public function set_date( $date ) {
		$this->date = $date;
	}

	/**
	 * Get start date.
	 *
	 * @return DateTime
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Set start date.
	 *
	 * @param DateTime $start_date Start date.
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = $start_date;
	}

	/**
	 * Get end date.
	 *
	 * @return DateTime
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Set end date.
	 *
	 * @param DateTime $end_date End date.
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = $end_date;
	}

	/**
	 * Get the source identifier of this payment.
	 *
	 * @return string
	 */
	public function get_source() {
		return $this->source;
	}

	/**
	 * Set the source of this payment.
	 *
	 * @param string $source Source.
	 */
	public function set_source( $source ) {
		$this->source = $source;
	}

	/**
	 * Get the source ID of this payment.
	 *
	 * @return string
	 */
	public function get_source_id() {
		return $this->source_id;
	}

	/**
	 * Set the source ID of this payment.
	 *
	 * @param string|int $source_id Source ID.
	 */
	public function set_source_id( $source_id ) {
		$this->source_id = $source_id;
	}

	/**
	 * Get the config ID of this payment.
	 *
	 * @return string
	 */
	public function get_config_id() {
		return $this->config_id;
	}

	/**
	 * Set the config ID of this payment.
	 *
	 * @param string|int $config_id Config ID.
	 */
	public function set_config_id( $config_id ) {
		$this->config_id = $config_id;
	}

	/**
	 * Get the source text of this payment.
	 *
	 * @return string
	 */
	public function get_source_text() {
		$text = $this->get_source() . '<br />' . $this->get_source_id();

		$text = apply_filters( 'pronamic_payment_source_text_' . $this->get_source(), $text, $this );
		$text = apply_filters( 'pronamic_payment_source_text', $text, $this );

		return $text;
	}

	/**
	 * Get customer.
	 *
	 * @return Customer|null
	 */
	public function get_customer() {
		return $this->customer;
	}

	/**
	 * Set customer.
	 *
	 * @param Customer $customer Contact.
	 */
	public function set_customer( $customer ) {
		$this->customer = $customer;
	}

	/**
	 * Get billing address.
	 *
	 * @return Address|null
	 */
	public function get_billing_address() {
		return $this->billing_address;
	}

	/**
	 * Set billing address.
	 *
	 * @param Address $billing_address Billing address.
	 */
	public function set_billing_address( $billing_address ) {
		$this->billing_address = $billing_address;
	}

	/**
	 * Get shipping address.
	 *
	 * @return Address|null
	 */
	public function get_shipping_address() {
		return $this->shipping_address;
	}

	/**
	 * Set shipping address.
	 *
	 * @param Address $shipping_address Shipping address.
	 */
	public function set_shipping_address( $shipping_address ) {
		$this->shipping_address = $shipping_address;
	}

	/**
	 * Get payment lines.
	 *
	 * @return PaymentLines|null
	 */
	public function get_lines() {
		return $this->lines;
	}

	/**
	 * Set payment lines.
	 *
	 * @param PaymentLines $lines Payment lines.
	 */
	public function set_lines( PaymentLines $lines ) {
		$this->lines = $lines;
	}

	/**
	 * Get the order ID of this payment.
	 *
	 * @return string
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Get total amount.
	 *
	 * @return TaxedMoney
	 */
	public function get_total_amount() {
		return $this->total_amount;
	}

	/**
	 * Set total amount.
	 *
	 * @param TaxedMoney $total_amount Total amount.
	 */
	public function set_total_amount( TaxedMoney $total_amount ) {
		$this->total_amount = $total_amount;
	}

	/**
	 * Get the shipping amount.
	 *
	 * @return Money|null
	 */
	public function get_shipping_amount() {
		return $this->shipping_amount;
	}

	/**
	 * Set the shipping amount.
	 *
	 * @param Money|null $shipping_amount Money object.
	 */
	public function set_shipping_amount( Money $shipping_amount ) {
		$this->shipping_amount = $shipping_amount;
	}

	/**
	 * Get the payment method.
	 *
	 * @todo Constant?
	 * @return string|null
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * Get the payment issuer.
	 *
	 * @return string
	 */
	public function get_issuer() {
		return $this->issuer;
	}

	/**
	 * Get the payment description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set the transaction ID.
	 *
	 * @param string $transaction_id Transaction ID.
	 */
	public function set_transaction_id( $transaction_id ) {
		$this->transaction_id = $transaction_id;
	}

	/**
	 * Get the payment transaction ID.
	 *
	 * @return string
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/**
	 * Get the payment status.
	 *
	 * @todo Constant?
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the payment status.
	 *
	 * @param string $status Status.
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Is tracked in Google Analytics?
	 *
	 * @return bool
	 */
	public function get_ga_tracked() {
		return (bool) $this->ga_tracked;
	}

	/**
	 * Set if payment is tracked in Google Analytics.
	 *
	 * @param bool $tracked Tracked in Google Analytics.
	 */
	public function set_ga_tracked( $tracked ) {
		$this->ga_tracked = $tracked;
	}

	/**
	 * Get the meta value of this specified meta key.
	 *
	 * @param string $key Meta key.
	 * @return mixed
	 */
	public function get_meta( $key ) {
		$key = '_pronamic_payment_' . $key;

		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * Set meta data.
	 *
	 * @param  string $key   A meta key.
	 * @param  mixed  $value A meta value.
	 *
	 * @return boolean        True on successful update, false on failure.
	 */
	public function set_meta( $key, $value ) {
		$key = '_pronamic_payment_' . $key;

		if ( $value instanceof \DateTime ) {
			$value = $value->format( 'Y-m-d H:i:s' );
		}

		if ( empty( $value ) ) {
			return delete_post_meta( $this->id, $key );
		}

		$result = update_post_meta( $this->id, $key, $value );

		return ( false !== $result );
	}

	/**
	 * Get the pay redirect URL.
	 *
	 * @return string
	 */
	public function get_pay_redirect_url() {
		return add_query_arg( 'payment_redirect', $this->id, home_url( '/' ) );
	}

	/**
	 * Get the return URL for this payment. This URL is passed to the payment providers / gateways
	 * so they know where they should return users to.
	 *
	 * @return string
	 */
	public function get_return_url() {
		$url = add_query_arg(
			array(
				'payment' => $this->id,
				'key'     => $this->key,
			),
			home_url( '/' )
		);

		return $url;
	}

	/**
	 * Get action URL.
	 *
	 * @return string
	 */
	public function get_action_url() {
		$action_url = $this->action_url;

		$amount = $this->get_total_amount()->get_amount();

		if ( empty( $amount ) ) {
			$status = $this->get_status();

			$this->set_status( Statuses::SUCCESS );

			$action_url = $this->get_return_redirect_url();

			$this->set_status( $status );
		}

		return $action_url;
	}

	/**
	 * Set the action URL.
	 *
	 * @param string $action_url Action URL.
	 */
	public function set_action_url( $action_url ) {
		$this->action_url = $action_url;
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

		$url = apply_filters( 'pronamic_payment_redirect_url', $url, $this );
		$url = apply_filters( 'pronamic_payment_redirect_url_' . $this->source, $url, $this );

		return $url;
	}

	/**
	 * Get source description.
	 *
	 * @return string
	 */
	public function get_source_description() {
		$description = $this->source;

		$description = apply_filters( 'pronamic_payment_source_description', $description, $this );
		$description = apply_filters( 'pronamic_payment_source_description_' . $this->source, $description, $this );

		return $description;
	}

	/**
	 * Get the source link for this payment.
	 *
	 * @return string|null
	 */
	public function get_source_link() {
		$url = null;

		$url = apply_filters( 'pronamic_payment_source_url', $url, $this );
		$url = apply_filters( 'pronamic_payment_source_url_' . $this->source, $url, $this );

		return $url;
	}

	/**
	 * Get provider link for this payment.
	 *
	 * @return string
	 */
	public function get_provider_link() {
		$url = null;

		$config_id  = get_post_meta( $this->id, '_pronamic_payment_config_id', true );
		$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

		$url = apply_filters( 'pronamic_payment_provider_url', $url, $this );
		$url = apply_filters( 'pronamic_payment_provider_url_' . $gateway_id, $url, $this );

		return $url;
	}

	/**
	 * Get subscription.
	 *
	 * @return Subscription|bool
	 */
	public function get_subscription() {
		if ( is_object( $this->subscription ) ) {
			return $this->subscription;
		}

		if ( empty( $this->subscription_id ) ) {
			return false;
		}

		$this->subscription = new Subscription( $this->subscription_id );

		return $this->subscription;
	}

	/**
	 * Format string
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/v2.2.3/includes/abstracts/abstract-wc-email.php#L187-L195
	 *
	 * @param string $string The string to format.
	 * @return string
	 */
	public function format_string( $string ) {
		// Replacements definition.
		$replacements = array(
			'{order_id}'   => $this->get_order_id(),
			'{payment_id}' => $this->get_id(),
		);

		// Find and replace.
		$string = str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$string,
			$count
		);

		// Make sure there is an dynamic part in the order ID.
		// @link https://secure.ogone.com/ncol/param_cookbook.asp.
		if ( 0 === $count ) {
			$string .= $this->get_id();
		}

		return $string;
	}

	/**
	 * Set consumer name.
	 *
	 * @param string $name Name.
	 */
	public function set_consumer_name( $name ) {
		$this->consumer_name = $name;
	}

	/**
	 * Set consumer account number.
	 *
	 * @param string $account_number Account number.
	 */
	public function set_consumer_account_number( $account_number ) {
		$this->consumer_account_number = $account_number;
	}

	/**
	 * Set consumer IBAN.
	 *
	 * @param string $iban IBAN.
	 */
	public function set_consumer_iban( $iban ) {
		$this->consumer_iban = $iban;
	}

	/**
	 * Set consumer BIC.
	 *
	 * @param string $bic BIC.
	 */
	public function set_consumer_bic( $bic ) {
		$this->consumer_bic = $bic;
	}

	/**
	 * Set consumer city.
	 *
	 * @param string $city City.
	 */
	public function set_consumer_city( $city ) {
		$this->consumer_city = $city;
	}

	/**
	 * Get payment email.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get Google Analytics client ID.
	 *
	 * @return string
	 */
	public function get_analytics_client_id() {
		return $this->analytics_client_id;
	}

	/**
	 * Get entrance code.
	 *
	 * @return string
	 */
	public function get_entrance_code() {
		return $this->entrance_code;
	}

	/**
	 * Set the credit card to use for this payment.
	 *
	 * @param CreditCard $credit_card Credit Card.
	 */
	public function set_credit_card( $credit_card ) {
		$this->credit_card = $credit_card;
	}

	/**
	 * Get the credit card to use for this payment.
	 *
	 * @return CreditCard|null
	 */
	public function get_credit_card() {
		return $this->credit_card;
	}

	/**
	 * Get payment subscription ID.
	 *
	 * @return int
	 */
	public function get_subscription_id() {
		return $this->subscription_id;
	}

	/**
	 * Get reucrring.
	 *
	 * @return bool
	 */
	public function get_recurring() {
		return $this->recurring;
	}

	/**
	 * Set version.
	 *
	 * @param string|null $version Version.
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * Get version.
	 *
	 * @return string|null
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Set mode.
	 *
	 * @param string|null $mode Mode.
	 *
	 * @throws InvalidArgumentException Throws invalid argument exception when mode is not a string or not one of the mode constants.
	 */
	public function set_mode( $mode ) {
		if ( ! is_string( $mode ) ) {
			throw new InvalidArgumentException( 'Mode must be a string.' );
		}

		if ( ! in_array( $mode, array( Gateway::MODE_TEST, Gateway::MODE_LIVE ), true ) ) {
			throw new InvalidArgumentException( 'Invalid mode.' );
		}

		$this->mode = $mode;
	}

	/**
	 * Get mode.
	 *
	 * @return string|null
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Create payment from object.
	 *
	 * @param mixed        $json    JSON.
	 * @param Payment|null $payment Payment.
	 * @return Payment
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, self $payment = null ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		if ( null === $payment ) {
			$payment = new self();
		}

		if ( isset( $json->id ) ) {
			$payment->set_id( $json->id );
		}

		if ( isset( $json->total_amount ) ) {
			$payment->set_total_amount( TaxedMoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( isset( $json->shipping_amount ) ) {
			$payment->set_shipping_amount( MoneyJsonTransformer::from_json( $json->shipping_amount ) );
		}

		if ( isset( $json->customer ) ) {
			$payment->set_customer( Customer::from_json( $json->customer ) );
		}

		if ( isset( $json->billing_address ) ) {
			$payment->set_billing_address( Address::from_json( $json->billing_address ) );
		}

		if ( isset( $json->shipping_address ) ) {
			$payment->set_shipping_address( Address::from_json( $json->shipping_address ) );
		}

		if ( isset( $json->lines ) ) {
			$payment->set_lines( PaymentLines::from_json( $json->lines ) );
		}

		if ( isset( $json->ga_tracked ) ) {
			$payment->set_ga_tracked( $json->ga_tracked );
		}

		if ( isset( $json->mode ) ) {
			$payment->set_mode( $json->mode );
		}

		return $payment;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$object = (object) array();

		if ( null !== $this->get_id() ) {
			$object->id = $this->get_id();
		}

		$object->total_amount    = TaxedMoneyJsonTransformer::to_json( $this->get_total_amount() );
		$object->shipping_amount = MoneyJsonTransformer::to_json( $this->get_shipping_amount() );

		if ( null !== $this->get_customer() ) {
			$object->customer = $this->get_customer()->get_json();
		}

		if ( null !== $this->get_billing_address() ) {
			$object->billing_address = $this->get_billing_address()->get_json();
		}

		if ( null !== $this->get_shipping_address() ) {
			$object->shipping_address = $this->get_shipping_address()->get_json();
		}

		if ( null !== $this->get_lines() ) {
			$object->lines = $this->get_lines()->get_json();
		}

		if ( null !== $this->get_ga_tracked() ) {
			$object->ga_tracked = $this->get_ga_tracked();
		}

		if ( null !== $this->get_mode() ) {
			$object->mode = $this->get_mode();
		}

		return $object;
	}
}
