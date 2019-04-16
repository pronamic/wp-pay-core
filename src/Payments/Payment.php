<?php
/**
 * Payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
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
 * @version 2.1.6
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
	 * @var int|null
	 */
	public $config_id;

	/**
	 * The key of this payment, used in URL's for security.
	 *
	 * @var string|null
	 */
	public $key;

	/**
	 * Identifier for the source which started this payment.
	 * For example: 'woocommerce', 'gravityforms', 'easydigitaldownloads', etc.
	 *
	 * @var string|null
	 */
	public $source;

	/**
	 * Unique ID at the source which started this payment, for example:
	 * - WooCommerce order ID.
	 * - Easy Digital Downloads payment ID.
	 * - Gravity Forms entry ID.
	 *
	 * @var string|int|null
	 */
	public $source_id;

	/**
	 * The purchase ID.
	 *
	 * @todo Is this required/used?
	 * @var string|null
	 */
	public $purchase_id;

	/**
	 * The transaction ID of this payment.
	 *
	 * @var string|null
	 */
	public $transaction_id;

	/**
	 * The order ID of this payment.
	 *
	 * @todo Is this required/used?
	 * @var string|null
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
	 * @var string|null
	 */
	public $entrance_code;

	/**
	 * The description of this payment.
	 *
	 * @var string|null
	 */
	public $description;

	/**
	 * The name of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string|null
	 */
	public $consumer_name;

	/**
	 * The account number of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string|null
	 */
	public $consumer_account_number;

	/**
	 * The IBAN of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string|null
	 */
	public $consumer_iban;

	/**
	 * The BIC of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string|null
	 */
	public $consumer_bic;

	/**
	 * The city of the consumer of this payment.
	 *
	 * @todo Is this required and should we add the 'consumer' part?
	 * @var  string|null
	 */
	public $consumer_city;

	/**
	 * The Google Analytics client ID of the user who started this payment.
	 *
	 * @var string|null
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
	 * @var string|null
	 */
	public $email;

	/**
	 * The action URL for this payment.
	 *
	 * @var string|null
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
	 * @var string|null
	 */
	public $issuer;

	/**
	 * Subscription ID.
	 *
	 * @todo Is this required?
	 * @var int|null
	 */
	public $subscription_id;

	/**
	 * Subscription source ID.
	 *
	 * @var string|int|null
	 */
	public $subscription_source_id;

	/**
	 * Flag to indicate a recurring payment
	 *
	 * @todo Is this required?
	 * @var boolean|null
	 */
	public $recurring;

	/**
	 * The recurring type.
	 *
	 * @todo Improve documentation, is this used?
	 * @var string|null
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
	 * @var DateTime|null
	 */
	public $start_date;

	/**
	 * End date if the payment is related to a specific period.
	 *
	 * @var DateTime|null
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
	 * Is anonymized.
	 *
	 * @var bool|null
	 */
	private $anonymized;

	/**
	 * Construct and initialize payment object.
	 *
	 * @param integer $post_id A payment post ID or null.
	 */
	public function __construct( $post_id = null ) {
		parent::__construct( $post_id );

		$this->meta = array();

		$this->set_status( Statuses::OPEN );

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
	 * Get the payment description.
	 *
	 * @return string|null
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set the transaction ID.
	 *
	 * @param string|null $transaction_id Transaction ID.
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
	 * Get the payment status.
	 *
	 * @todo Constant?
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get payment status label.
	 *
	 * @return string|false
	 */
	public function get_status_label() {
		return pronamic_pay_plugin()->payments_data_store->get_meta_status_label( $this->status );
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
		$url = add_query_arg(
			array(
				'payment_redirect' => $this->id,
				'key'              => $this->key,
			),
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
	 * @return string|null
	 */
	public function get_action_url() {
		$action_url = $this->action_url;

		$amount = $this->get_total_amount()->get_value();

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
	 * Get edit payment URL.
	 *
	 * @link https://docs.woocommerce.com/wc-apidocs/source-class-WC_Order.html#1538-1546
	 *
	 * @return string
	 */
	public function get_edit_payment_url() {
		$url = add_query_arg(
			array(
				'action' => 'edit',
				'post'   => $this->get_id(),
			),
			admin_url( 'post.php' )
		);

		return $url;
	}

	/**
	 * Get source description.
	 *
	 * @return string|null
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
	 * @return Subscription|false
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
	 * Get payment email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get Google Analytics client ID.
	 *
	 * @return string|null
	 */
	public function get_analytics_client_id() {
		return $this->analytics_client_id;
	}

	/**
	 * Get entrance code.
	 *
	 * @return string|null
	 */
	public function get_entrance_code() {
		return $this->entrance_code;
	}

	/**
	 * Get payment subscription ID.
	 *
	 * @return int|null
	 */
	public function get_subscription_id() {
		return $this->subscription_id;
	}

	/**
	 * Get reucrring.
	 *
	 * @return bool|null
	 */
	public function get_recurring() {
		return $this->recurring;
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

		if ( ! $payment instanceof self ) {
			$payment = new self();
		}

		parent::from_json( $json, $payment );

		if ( isset( $json->ga_tracked ) ) {
			$payment->set_ga_tracked( $json->ga_tracked );
		}

		return $payment;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$object = parent::get_json();

		if ( null !== $this->get_ga_tracked() ) {
			$object->ga_tracked = $this->get_ga_tracked();
		}

		return $object;
	}
}
