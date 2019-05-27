<?php
/**
 * Abstract Payment Data
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;

/**
 * Abstract payment data class
 *
 * @author Remco Tolsma
 * @version 2.0.2
 * @since 1.4.0
 */
abstract class AbstractPaymentData implements PaymentDataInterface {
	/**
	 * Entrance code.
	 *
	 * @todo Is this used?
	 * @var string
	 */
	private $entrance_code;

	/**
	 * Recurring.
	 *
	 * @todo Is this used?
	 *
	 * @var bool|null
	 */
	protected $recurring;

	/**
	 * Construct and initialize abstract payment data object.
	 */
	public function __construct() {
		$this->entrance_code = uniqid();
	}

	/**
	 * Get user ID.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_current_user_id/
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-includes/class-wp-post.php#L31-L39
	 *
	 * @return int|string|null
	 */
	public function get_user_id() {
		return get_current_user_id();
	}

	/**
	 * Get source.
	 *
	 * @return string|null
	 */
	abstract public function get_source();

	/**
	 * Get source ID.
	 *
	 * @return string|int|null
	 */
	public function get_source_id() {
		return $this->get_order_id();
	}

	/**
	 * Get title.
	 *
	 * @return string|null
	 */
	public function get_title() {
		return $this->get_description();
	}

	/**
	 * Get description.
	 *
	 * @return string|null
	 */
	abstract public function get_description();

	/**
	 * Get order ID.
	 *
	 * @return string|int|null
	 */
	abstract public function get_order_id();

	/**
	 * Get items.
	 *
	 * @return Items
	 */
	abstract public function get_items();

	/**
	 * Get amount.
	 *
	 * @return TaxedMoney
	 */
	public function get_amount() {
		$currency_code = $this->get_currency_alphabetic_code();

		if ( null === $currency_code ) {
			$currency_code = 'EUR';
		}

		return new TaxedMoney(
			$this->get_items()->get_amount()->get_value(),
			$currency_code
		);
	}

	/**
	 * Get email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return null;
	}

	/**
	 * Get customer name.
	 *
	 * @deprecated deprecated since version 4.0.1, use get_customer_name() instead.
	 *
	 * @return string|null
	 */
	public function getCustomerName() {
		return $this->get_customer_name();
	}

	/**
	 * Get customer name.
	 *
	 * @return string|null
	 */
	public function get_customer_name() {
		return null;
	}

	/**
	 * Get owner address.
	 *
	 * @deprecated deprecated since version 4.0.1, use get_address() instead.
	 *
	 * @return string|null
	 */
	public function getOwnerAddress() {
		return $this->get_address();
	}

	/**
	 * Get address.
	 *
	 * @return string|null
	 */
	public function get_address() {
		return null;
	}

	/**
	 * Get owner city.
	 *
	 * @deprecated deprecated since version 4.0.1, use get_city() instead.
	 *
	 * @return string|null
	 */
	public function getOwnerCity() {
		return $this->get_city();
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function get_city() {
		return null;
	}

	/**
	 * Get owner zip.
	 *
	 * @deprecated deprecated since version 4.0.1, use get_zip() instead.
	 *
	 * @return string|null
	 */
	public function getOwnerZip() {
		return $this->get_zip();
	}

	/**
	 * Get ZIP.
	 *
	 * @return string|null
	 */
	public function get_zip() {
		return null;
	}

	/**
	 * Get country.
	 *
	 * @return string|null
	 */
	public function get_country() {
		return null;
	}

	/**
	 * Get telephone number.
	 *
	 * @return string|null
	 */
	public function get_telephone_number() {
		return null;
	}

	/**
	 * Get the curreny alphabetic code.
	 *
	 * @return string|null
	 */
	abstract public function get_currency_alphabetic_code();

	/**
	 * Get currency numeric code.
	 *
	 * @return string|null
	 */
	public function get_currency_numeric_code() {
		return $this->get_amount()->get_currency()->get_numeric_code();
	}

	/**
	 * Helper function to get the curreny alphabetic code.
	 *
	 * @return string|null
	 */
	public function get_currency() {
		return $this->get_amount()->get_currency()->get_alphabetic_code();
	}

	/**
	 * Get the language code (ISO639).
	 *
	 * @link http://www.w3.org/WAI/ER/IG/ert/iso639.htm
	 *
	 * @return string|null
	 */
	abstract public function get_language();

	/**
	 * Get the language (ISO639) and country (ISO3166) code.
	 *
	 * @link http://www.w3.org/WAI/ER/IG/ert/iso639.htm
	 * @link http://www.iso.org/iso/home/standards/country_codes.htm
	 *
	 * @return string|null
	 */
	abstract public function get_language_and_country();

	/**
	 * Get entrance code.
	 *
	 * @return string
	 */
	public function get_entrance_code() {
		return $this->entrance_code;
	}

	/**
	 * Get issuer of the specified payment method.
	 *
	 * @todo Constant?
	 *
	 * @param string $payment_method Payment method identifier.
	 * @return string|null
	 */
	public function get_issuer( $payment_method = null ) {
		if ( PaymentMethods::CREDIT_CARD === $payment_method ) {
			return $this->get_credit_card_issuer_id();
		}

		return $this->get_issuer_id();
	}

	/**
	 * Get issuer ID.
	 *
	 * @return string|null
	 */
	public function get_issuer_id() {
		return filter_input( INPUT_POST, 'pronamic_ideal_issuer_id', FILTER_SANITIZE_STRING );
	}

	/**
	 * Get credit card issuer ID.
	 *
	 * @return string|null
	 */
	public function get_credit_card_issuer_id() {
		return filter_input( INPUT_POST, 'pronamic_credit_card_issuer_id', FILTER_SANITIZE_STRING );
	}

	/**
	 * Get credit card object.
	 *
	 * @return CreditCard|null
	 */
	public function get_credit_card() {
		return null;
	}

	/**
	 * Subscription.
	 *
	 * @return Subscription|null
	 */
	public function get_subscription() {
		return null;
	}

	/**
	 * Subscription ID.
	 *
	 * @return int|null
	 */
	abstract public function get_subscription_id();

	/**
	 * Is this a recurring (not first) payment?
	 *
	 * @return bool|null
	 */
	public function get_recurring() {
		return $this->recurring;
	}

	/**
	 * Set recurring.
	 *
	 * @param bool|null $recurring Boolean flag which indicates recurring.
	 */
	public function set_recurring( $recurring ) {
		$this->recurring = $recurring;
	}
}
