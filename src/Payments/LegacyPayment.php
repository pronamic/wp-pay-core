<?php
/**
 * Legacy payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use WP_Post;

/**
 * Legacy payment.
 *
 * Legacy and deprecated functions are here to keep the Payment class clean.
 * This class will be removed in future versions.
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   2.0.8
 */
abstract class LegacyPayment {
	/**
	 * The language of the user who started this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $language;

	/**
	 * The locale of the user who started this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $locale;

	/**
	 * The customer name of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var  string
	 */
	public $customer_name;

	/**
	 * The address of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $address;

	/**
	 * The city of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $city;

	/**
	 * The ZIP of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $zip;

	/**
	 * The country of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $country;

	/**
	 * The telephone number of the consumer of this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $telephone_number;

	/**
	 * The first name of the user who started this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $first_name;

	/**
	 * The last name of the user who started this payment.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $last_name;

	/**
	 * User agent.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $user_agent;

	/**
	 * User IP address.
	 *
	 * @deprecated 2.0.8
	 *
	 * @var string
	 */
	public $user_ip;

	/**
	 * Get the payment language.
	 *
	 * @return string|null
	 */
	public function get_language() {
		if ( null === $this->get_customer() ) {
			return null;
		}

		return $this->get_customer()->get_language();
	}

	/**
	 * Get the payment locale.
	 *
	 * @return string|null
	 */
	public function get_locale() {
		if ( null === $this->get_customer() ) {
			return null;
		}

		return $this->get_customer()->get_locale();
	}

	/**
	 * Get the redirect URL for this payment.
	 *
	 * @deprecated 4.1.2 Use get_return_redirect_url()
	 * @return string
	 */
	public function get_redirect_url() {
		_deprecated_function( __FUNCTION__, '4.1.2', 'get_return_redirect_url()' );

		return $this->get_return_redirect_url();
	}

	/**
	 * Get first name.
	 *
	 * @return string|null
	 */
	public function get_first_name() {
		if ( null === $this->get_customer() ) {
			return null;
		}

		if ( null === $this->get_customer()->get_name() ) {
			return null;
		}

		return $this->get_customer()->get_name()->get_first_name();
	}

	/**
	 * Get last name.
	 *
	 * @return string|null
	 */
	public function get_last_name() {
		if ( null === $this->get_customer() ) {
			return null;
		}

		if ( null === $this->get_customer()->get_name() ) {
			return null;
		}

		return $this->get_customer()->get_name()->get_last_name();
	}

	/**
	 * Get customer name.
	 *
	 * @return string|null
	 */
	public function get_customer_name() {
		if ( null === $this->get_customer() ) {
			return null;
		}

		if ( null === $this->get_customer()->get_name() ) {
			return null;
		}

		return strval( $this->get_customer()->get_name() );
	}

	/**
	 * Get address.
	 *
	 * @return string|null
	 */
	public function get_address() {
		if ( null === $this->get_billing_address() ) {
			return null;
		}

		return $this->get_billing_address()->get_line_1();
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function get_city() {
		if ( null === $this->get_billing_address() ) {
			return null;
		}

		return $this->get_billing_address()->get_city();
	}

	/**
	 * Get ZIP.
	 *
	 * @return string|null
	 */
	public function get_zip() {
		if ( null === $this->get_billing_address() ) {
			return null;
		}

		return $this->get_billing_address()->get_postal_code();
	}

	/**
	 * Get country.
	 *
	 * @return string|null
	 */
	public function get_country() {
		if ( null === $this->get_billing_address() ) {
			return null;
		}

		return $this->get_billing_address()->get_country_code();
	}

	/**
	 * Get telephone number.
	 *
	 * @return string|null
	 */
	public function get_telephone_number() {
		if ( null === $this->get_billing_address() ) {
			return null;
		}

		return $this->get_billing_address()->get_phone();
	}
}
