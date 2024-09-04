<?php
/**
 * Payment info
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Plugin;
use WP_Post;

/**
 * Payment info
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   1.0.0
 */
abstract class PaymentInfo {
	use \Pronamic\WordPress\Pay\Core\TimestampsTrait;

	use \Pronamic\WordPress\Pay\Core\VersionTrait;

	use \Pronamic\WordPress\Pay\Core\ModeTrait;

	use \Pronamic\WordPress\Pay\Privacy\AnonymizedTrait;

	use \Pronamic\WordPress\Pay\Payments\PaymentInfoTrait;

	use \Pronamic\WordPress\Pay\Payments\SourceTrait;

	/**
	 * The post object.
	 *
	 * @var WP_Post|array|null
	 */
	public $post;

	/**
	 * The date of this payment info.
	 *
	 * @var DateTime
	 */
	public $date;

	/**
	 * The unique ID of this payment info.
	 *
	 * @var int|null
	 */
	protected $id;

	/**
	 * The title of this payment info.
	 *
	 * @var string|null
	 */
	public $title;

	/**
	 * The configuration ID.
	 *
	 * @var int|null
	 */
	public $config_id;

	/**
	 * The key of this payment info, used in URL's for security.
	 *
	 * @var string|null
	 */
	public $key;

	/**
	 * Origin post ID.
	 *
	 * @var int|null
	 */
	private $origin_id;

	/**
	 * The order ID of this payment.
	 *
	 * @todo Is this required/used?
	 * @var string|null
	 */
	public $order_id;

	/**
	 * The transaction ID of this payment.
	 *
	 * @var string|null
	 */
	public $transaction_id;

	/**
	 * The shipping amount of this payment.
	 *
	 * @var Money|null
	 */
	private $shipping_amount;

	/**
	 * The description of this payment.
	 *
	 * @var string|null
	 */
	private $description;

	/**
	 * Bank transfer recipient details.
	 *
	 * @var BankTransferDetails|null
	 */
	private $bank_transfer_recipient_details;

	/**
	 * Consumer bank details.
	 *
	 * @var BankAccountDetails|null
	 */
	private $consumer_bank_details;

	/**
	 * Payment method.
	 *
	 * @var string|null
	 */
	private $payment_method;

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
	 * Credit card
	 *
	 * @deprecated
	 * @var CreditCard|null
	 */
	private $credit_card;

	/**
	 * Meta.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	public $meta_key_prefix = '_pronamic_pay_';

	/**
	 * Construct and initialize payment object.
	 *
	 * @param integer $post_id A payment post ID or null.
	 */
	public function __construct( $post_id = null ) {
		$this->id   = $post_id;
		$this->date = new DateTime();
		$this->meta = [];

		$this->touch();
	}

	/**
	 * Get the ID of this payment.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the ID of this payment.
	 *
	 * @param int $id Unique ID.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
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
	 * @return void
	 */
	public function set_date( $date ) {
		$this->date = $date;
	}

	/**
	 * Get origin post ID.
	 *
	 * @return int|null
	 */
	public function get_origin_id() {
		return $this->origin_id;
	}

	/**
	 * Set origin post ID.
	 *
	 * @param int|null $origin_id Origin post ID.
	 * @return void
	 */
	public function set_origin_id( $origin_id ) {
		$this->origin_id = $origin_id;
	}

	/**
	 * Get the config ID of this payment.
	 *
	 * @return int|null
	 */
	public function get_config_id() {
		return $this->config_id;
	}

	/**
	 * Set the config ID of this payment.
	 *
	 * @param int|null $config_id Config ID.
	 * @return void
	 */
	public function set_config_id( $config_id ) {
		$this->config_id = $config_id;
	}

	/**
	 * Get gateway.
	 *
	 * @return Gateway|null
	 */
	public function get_gateway() {
		$config_id = $this->get_config_id();

		if ( null === $config_id ) {
			return null;
		}

		return \pronamic_pay_plugin()->gateways_data_store->get_gateway( $config_id );
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
	 * @param Customer|null $customer Contact.
	 * @return void
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
	 * @param Address|null $billing_address Billing address.
	 * @return void
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
	 * @param Address|null $shipping_address Shipping address.
	 * @return void
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
	 * @param PaymentLines|null $lines Payment lines.
	 * @return void
	 */
	public function set_lines( PaymentLines $lines = null ) {
		$this->lines = $lines;
	}

	/**
	 * Get the order ID of this payment.
	 *
	 * @return string|null
	 */
	public function get_order_id() {
		return $this->order_id;
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
	 * @return void
	 */
	public function set_shipping_amount( Money $shipping_amount = null ) {
		$this->shipping_amount = $shipping_amount;
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
	 * Set the payment description.
	 *
	 * @param string|null $description Description.
	 * @return void
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Get the payment method.
	 *
	 * @return string|null
	 */
	public function get_payment_method() {
		return $this->payment_method;
	}

	/**
	 * Set the payment method.
	 *
	 * @param string|null $payment_method Payment method.
	 * @return void
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_method = $payment_method;
	}

	/**
	 * Get the meta value of this specified meta key.
	 *
	 * @param string $key Meta key.
	 * @return mixed
	 */
	public function get_meta( $key ) {
		if ( \array_key_exists( $key, $this->meta ) ) {
			return $this->meta[ $key ];
		}

		if ( null === $this->id ) {
			return null;
		}

		$key = $this->meta_key_prefix . $key;

		$meta_values = \get_post_meta( $this->id, $key, false );

		if ( \is_array( $meta_values ) && 0 === \count( $meta_values ) ) {
			return null;
		}

		return \get_post_meta( $this->id, $key, true );
	}

	/**
	 * Set meta data.
	 *
	 * @param  string $key   A meta key.
	 * @param  mixed  $value A meta value.
	 * @return void
	 */
	public function set_meta( $key, $value ) {
		$this->meta[ $key ] = $value;
	}

	/**
	 * Delete meta data.
	 *
	 * @param string $key Meta key.
	 * @return void
	 */
	public function delete_meta( $key ) {
		unset( $this->meta[ $key ] );
	}

	/**
	 * Get consumer bank details.
	 *
	 * @return BankAccountDetails|null
	 */
	public function get_consumer_bank_details() {
		return $this->consumer_bank_details;
	}

	/**
	 * Set consumer bank details.
	 *
	 * @param BankAccountDetails|null $bank_details Consumer bank details.
	 * @return void
	 */
	public function set_consumer_bank_details( $bank_details ) {
		$this->consumer_bank_details = $bank_details;
	}

	/**
	 * Get bank transfer details.
	 *
	 * @return BankTransferDetails|null
	 */
	public function get_bank_transfer_recipient_details() {
		return $this->bank_transfer_recipient_details;
	}

	/**
	 * Set bank transfer details.
	 *
	 * @param BankTransferDetails|null $bank_transfer Bank transfer details.
	 * @return void
	 */
	public function set_bank_transfer_recipient_details( $bank_transfer ) {
		$this->bank_transfer_recipient_details = $bank_transfer;
	}

	/**
	 * Set the credit card to use for this payment.
	 *
	 * @param CreditCard|null $credit_card Credit Card.
	 * @return void
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
	 * Get the unique key.
	 *
	 * @return string|null
	 */
	public function get_key() {
		return $this->key;
	}
}
