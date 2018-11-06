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
use Pronamic\WordPress\Pay\ContactName;
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
	 * The amount of this payment, for example 18.95.
	 *
	 * @var Money
	 */
	protected $amount;

	/**
	 * Get the payment amount.
	 *
	 * @return Money
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Set the payment amount.
	 *
	 * @param Money $amount Money object.
	 */
	public function set_amount( Money $amount ) {
		$this->amount = $amount;
	}

	/**
	 * Get the payment currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->get_amount()->get_currency()->get_alphabetic_code();
	}

	/**
	 * Get currency numeric code
	 *
	 * @return string|null
	 */
	public function get_currency_numeric_code() {
		return $this->get_amount()->get_currency()->get_numeric_code();
	}

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

	/**
	 * Get.
	 *
	 * @link http://php.net/manual/en/language.oop5.overloading.php#object.get
	 * @param string $name Name.
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'language':
				return $this->get_language();
			case 'locale':
				return $this->get_locale();
			case 'email':
				return ( null === $this->get_customer() ) ? null : $this->get_customer()->get_email();
			case 'user_agent':
				return ( null === $this->get_customer() ) ? null : $this->get_customer()->get_user_agent();
			case 'user_ip':
				return ( null === $this->get_customer() ) ? null : $this->get_customer()->get_ip_address();
			case 'customer_name':
				return $this->get_customer_name();
			case 'first_name':
				return $this->get_first_name();
			case 'last_name':
				return $this->get_last_name();
			case 'address':
				return $this->get_address();
			case 'zip':
				return $this->get_zip();
			case 'city':
				return $this->get_city();
			case 'country':
				return $this->get_country();
			case 'telephone_number':
				return $this->get_telephone_number();
		}

		return $this->{$name};
	}

	/**
	 * Set.
	 *
	 * @link http://php.net/manual/en/language.oop5.overloading.php#object.set
	 * @param string $name  Name.
	 * @param mixed  $value Value.
	 */
	public function __set( $name, $value ) {
		$legacy_keys = array(
			'language',
			'locale',
			'email',
			'first_name',
			'last_name',
			'telephone_number',
			'country',
			'zip',
			'city',
			'address',
		);

		if ( ! in_array( $name, $legacy_keys, true ) ) {
			$this->{$name} = $value;

			return;
		}

		$customer     = $this->get_customer();
		$address      = $this->get_billing_address();
		$contact_name = null;

		if ( in_array( $name, array( 'language', 'locale', 'email', 'first_name', 'last_name' ), true ) ) {
			if ( null === $value && null === $customer ) {
				return;
			}

			if ( null === $customer ) {
				$customer = new Customer();

				$this->set_customer( $customer );
			}

			if ( in_array( $name, array( 'first_name', 'last_name' ), true ) ) {
				$contact_name = $customer->get_name();

				if ( null === $value && null === $contact_name ) {
					return;
				}

				if ( null === $contact_name ) {
					$contact_name = new ContactName();

					$customer->set_name( $contact_name );
				}
			}
		}

		if ( in_array( $name, array( 'telephone_number', 'country', 'zip', 'city', 'address' ), true ) ) {
			if ( null === $value && null === $address ) {
				return;
			}

			if ( null === $address ) {
				$address = new Address();

				$this->set_billing_address( $address );
			}
		}

		switch ( $name ) {
			case 'language':
				return ( null === $customer ) ? null : $customer->set_language( $value );
			case 'email':
				return ( null === $customer ) ? null : $customer->set_email( $value );
			case 'first_name':
				return ( null === $contact_name ) ? null : $contact_name->set_first_name( $value );
			case 'last_name':
				return ( null === $contact_name ) ? null : $contact_name->set_last_name( $value );
			case 'locale':
				return ( null === $customer ) ? null : $customer->set_locale( $value );
			case 'telephone_number':
				return ( null === $address ) ? null : $address->set_phone( $value );
			case 'country':
				return ( null === $address ) ? null : $address->set_country_code( $value );
			case 'zip':
				return ( null === $address ) ? null : $address->set_postal_code( $value );
			case 'city':
				return ( null === $address ) ? null : $address->set_city( $value );
			case 'address':
				return ( null === $address ) ? null : $address->set_line_1( $value );
		}

		$this->{$name} = $value;
	}
}
