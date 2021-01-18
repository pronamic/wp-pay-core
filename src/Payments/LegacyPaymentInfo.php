<?php
/**
 * Legacy payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Customer;

/**
 * Legacy payment.
 *
 * Legacy and deprecated functions are here to keep the Payment class clean.
 * This class will be removed in future versions.
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.1.0
 *
 * @property string|null $language
 * @property string|null $locale
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $customer_name
 * @property string|null $telephone_number
 * @property string|null $country
 * @property string|null $zip
 * @property string|null $city
 * @property string|null $address
 * @property int|string|null $user_id
 */
abstract class LegacyPaymentInfo extends PaymentInfo {
	/**
	 * The amount of this payment, for example 18.95.
	 *
	 * @deprecated 2.0.9 Use Payment::$total_amount instead.
	 *
	 * @var Money
	 */
	protected $amount;

	/**
	 * Get the payment amount.
	 *
	 * @deprecated 2.0.9 Use Payment::get_total_amount() instead.
	 *
	 * @return Money
	 */
	public function get_amount() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_total_amount()' );

		if ( \method_exists( $this, 'get_total_amount' ) ) {
			return $this->get_total_amount();
		}

		return new Money( 0 );
	}

	/**
	 * Set the payment amount.
	 *
	 * @param Money $amount Money object.
	 * @return void
	 * @deprecated 2.0.9 Use Payment::set_total_amount() instead.
	 */
	public function set_amount( Money $amount ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::set_total_amount()' );

		if ( \method_exists( $this, 'set_total_amount' ) ) {
			$money = new TaxedMoney( $amount->get_value(), $amount->get_currency() );

			$this->set_total_amount( $money );
		}
	}

	/**
	 * Get the payment currency alphabetic code.
	 *
	 * @deprecated 2.0.9 Use Payment::get_total_amount()->get_currency()->get_alphabetic_code() instead.
	 *
	 * @return string|null
	 */
	public function get_currency() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_total_amount()->get_currency()->get_alphabetic_code()' );

		if ( \method_exists( $this, 'get_total_amount' ) ) {
			return $this->get_total_amount()->get_currency()->get_alphabetic_code();
		}

		return null;
	}

	/**
	 * Get currency numeric code
	 *
	 * @deprecated 2.0.9 Use Payment::get_total_amount()->get_currency()->get_numeric_code() instead.
	 *
	 * @return string|null
	 */
	public function get_currency_numeric_code() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_total_amount()->get_currency()->get_numeric_code()' );

		if ( \method_exists( $this, 'get_total_amount' ) ) {
			return $this->get_total_amount()->get_currency()->get_numeric_code();
		}

		return null;
	}

	/**
	 * Get the payment language.
	 *
	 * @deprecated 2.0.9 Use Payment::get_customer()->get_language() instead.
	 *
	 * @return string|null
	 */
	public function get_language() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_customer()->get_language()' );

		$customer = $this->get_customer();

		if ( null === $customer ) {
			return null;
		}

		return $customer->get_language();
	}

	/**
	 * Get the payment locale.
	 *
	 * @deprecated 2.0.9 Use Payment::get_customer()->get_locale() instead.
	 *
	 * @return string|null
	 */
	public function get_locale() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_customer()->get_locale()' );

		$customer = $this->get_customer();

		if ( null === $customer ) {
			return null;
		}

		return $customer->get_locale();
	}

	/**
	 * Get first name.
	 *
	 * @deprecated 2.0.9 Use Payment::get_customer()->get_name()->get_first_name() instead.
	 *
	 * @return string|null
	 */
	public function get_first_name() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_customer()->get_name()->get_first_name()' );

		$customer = $this->get_customer();

		if ( null === $customer ) {
			return null;
		}

		$name = $customer->get_name();

		if ( null === $name ) {
			return null;
		}

		return $name->get_first_name();
	}

	/**
	 * Get last name.
	 *
	 * @deprecated 2.0.9 Use Payment::get_customer()->get_name()->get_last_name() instead.
	 *
	 * @return string|null
	 */
	public function get_last_name() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_customer()->get_name()->get_last_name()' );

		$customer = $this->get_customer();

		if ( null === $customer ) {
			return null;
		}

		$name = $customer->get_name();

		if ( null === $name ) {
			return null;
		}

		return $name->get_last_name();
	}

	/**
	 * Get customer name.
	 *
	 * @deprecated 2.0.9 Use Payment::get_customer()->get_name() instead.
	 *
	 * @return string|null
	 */
	public function get_customer_name() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_customer()->get_name()' );

		$customer = $this->get_customer();

		if ( null === $customer ) {
			return null;
		}

		$name = $customer->get_name();

		if ( null === $name ) {
			return null;
		}

		return strval( $name );
	}

	/**
	 * Get address.
	 *
	 * @deprecated 2.0.9 Use Payment::get_billing_address()->get_line_1() instead.
	 *
	 * @return string|null
	 */
	public function get_address() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_billing_address()->get_line_1()' );

		$address = $this->get_billing_address();

		if ( null === $address ) {
			return null;
		}

		return $address->get_line_1();
	}

	/**
	 * Get city.
	 *
	 * @deprecated 2.0.9 Use Payment::get_billing_address()->get_city() instead.
	 *
	 * @return string|null
	 */
	public function get_city() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_billing_address()->get_city()' );

		$address = $this->get_billing_address();

		if ( null === $address ) {
			return null;
		}

		return $address->get_city();
	}

	/**
	 * Get ZIP.
	 *
	 * @deprecated 2.0.9 Use Payment::get_billing_address()->get_postal_code() instead.
	 *
	 * @return string|null
	 */
	public function get_zip() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_billing_address()->get_postal_code()' );

		$address = $this->get_billing_address();

		if ( null === $address ) {
			return null;
		}

		return $address->get_postal_code();
	}

	/**
	 * Get country.
	 *
	 * @deprecated 2.0.9 Use Payment::get_billing_address()->get_country_code() instead.
	 *
	 * @return string|null
	 */
	public function get_country() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_billing_address()->get_country()' );

		$address = $this->get_billing_address();

		if ( null === $address ) {
			return null;
		}

		return $address->get_country_code();
	}

	/**
	 * Get telephone number.
	 *
	 * @deprecated 2.0.9 Use Payment::get_billing_address()->get_phone() instead.
	 *
	 * @return string|null
	 */
	public function get_telephone_number() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'Payment::get_billing_address()->get_phone()' );

		$address = $this->get_billing_address();

		if ( null === $address ) {
			return null;
		}

		return $address->get_phone();
	}

	/**
	 * Set consumer name.
	 *
	 * @deprecated 2.2.6 Use Payment::set_consumer_bank_details()->set_name() instead.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_consumer_name( $name ) {
		_deprecated_function( __FUNCTION__, '2.2.6', 'Payment::set_consumer_bank_details()->set_name()' );

		$consumer_bank_details = $this->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();

			$this->set_consumer_bank_details( $consumer_bank_details );
		}

		$consumer_bank_details->set_name( $name );
	}

	/**
	 * Set consumer account number.
	 *
	 * @deprecated 2.2.6 Use Payment::set_consumer_bank_details()->set_account_number() instead.
	 *
	 * @param string|null $account_number Account number.
	 * @return void
	 */
	public function set_consumer_account_number( $account_number ) {
		_deprecated_function( __FUNCTION__, '2.2.6', 'Payment::set_consumer_bank_details()->set_account_number()' );

		$consumer_bank_details = $this->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();

			$this->set_consumer_bank_details( $consumer_bank_details );
		}

		$consumer_bank_details->set_account_number( $account_number );
	}

	/**
	 * Set consumer IBAN.
	 *
	 * @deprecated 2.2.6 Use Payment::set_consumer_bank_details()->set_iban() instead.
	 *
	 * @param string|null $iban IBAN.
	 * @return void
	 */
	public function set_consumer_iban( $iban ) {
		_deprecated_function( __FUNCTION__, '2.2.6', 'Payment::set_consumer_bank_details()->set_iban()' );

		$consumer_bank_details = $this->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();

			$this->set_consumer_bank_details( $consumer_bank_details );
		}

		$consumer_bank_details->set_iban( $iban );
	}

	/**
	 * Set consumer BIC.
	 *
	 * @deprecated 2.2.6 Use Payment::set_consumer_bank_details()->set_bic() instead.
	 *
	 * @param string|null $bic BIC.
	 * @return void
	 */
	public function set_consumer_bic( $bic ) {
		_deprecated_function( __FUNCTION__, '2.2.6', 'Payment::set_consumer_bank_details()->set_bic()' );

		$consumer_bank_details = $this->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();

			$this->set_consumer_bank_details( $consumer_bank_details );
		}

		$consumer_bank_details->set_bic( $bic );
	}

	/**
	 * Set consumer city.
	 *
	 * @deprecated 2.2.6 Use Payment::set_consumer_bank_details()->set_city() instead.
	 *
	 * @param string|null $city City.
	 * @return void
	 */
	public function set_consumer_city( $city ) {
		_deprecated_function( __FUNCTION__, '2.2.6', 'Payment::set_consumer_bank_details()->set_city()' );

		$consumer_bank_details = $this->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();

			$this->set_consumer_bank_details( $consumer_bank_details );
		}

		$consumer_bank_details->set_city( $city );
	}

	/**
	 * Get.
	 *
	 * @link http://php.net/manual/en/language.oop5.overloading.php#object.get
	 * @param string $name Name.
	 * @return mixed
	 */
	public function __get( $name ) {
		$customer              = $this->get_customer();
		$consumer_bank_details = $this->get_consumer_bank_details();

		switch ( $name ) {
			case 'language':
				return $this->get_language();
			case 'locale':
				return $this->get_locale();
			case 'email':
				return ( null === $customer ) ? null : $customer->get_email();
			case 'user_agent':
				return ( null === $customer ) ? null : $customer->get_user_agent();
			case 'user_id':
				return ( null === $customer ) ? null : $customer->get_user_id();
			case 'user_ip':
				return ( null === $customer ) ? null : $customer->get_ip_address();
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

			// @since 2.2.6
			case 'consumer_name':
				return ( null === $consumer_bank_details ) ? null : $consumer_bank_details->get_name();
			case 'consumer_account_number':
				return ( null === $consumer_bank_details ) ? null : $consumer_bank_details->get_account_number();
			case 'consumer_iban':
				return ( null === $consumer_bank_details ) ? null : $consumer_bank_details->get_iban();
			case 'consumer_bic':
				return ( null === $consumer_bank_details ) ? null : $consumer_bank_details->get_bic();
			case 'consumer_city':
				return ( null === $consumer_bank_details ) ? null : $consumer_bank_details->get_city();
		}

		return $this->{$name};
	}

	/**
	 * Set.
	 *
	 * @link http://php.net/manual/en/language.oop5.overloading.php#object.set
	 *
	 * @param string $name  Name.
	 * @param mixed  $value Value.
	 *
	 * @return null|void
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
			'user_id',
			'consumer_name',
			'consumer_account_number',
			'consumer_iban',
			'consumer_bic',
			'consumer_city',
		);

		if ( ! in_array( $name, $legacy_keys, true ) ) {
			$this->{$name} = $value;

			return null;
		}

		$customer              = $this->get_customer();
		$address               = $this->get_billing_address();
		$consumer_bank_details = $this->get_consumer_bank_details();
		$contact_name          = null;

		if ( in_array( $name, array( 'language', 'locale', 'email', 'first_name', 'last_name', 'user_id' ), true ) ) {
			if ( null === $value && null === $customer ) {
				return null;
			}

			if ( null === $customer ) {
				$customer = new Customer();

				$this->set_customer( $customer );
			}

			if ( in_array( $name, array( 'first_name', 'last_name' ), true ) ) {
				$contact_name = $customer->get_name();

				if ( null === $value && null === $contact_name ) {
					return null;
				}

				if ( null === $contact_name ) {
					$contact_name = new ContactName();

					$customer->set_name( $contact_name );
				}
			}
		}

		if ( in_array( $name, array( 'telephone_number', 'country', 'zip', 'city', 'address' ), true ) ) {
			if ( null === $value && null === $address ) {
				return null;
			}

			if ( null === $address ) {
				$address = new Address();

				$this->set_billing_address( $address );
			}
		}

		// Consumer.
		if ( in_array( $name, array( 'consumer_name', 'consumer_account_number', 'consumer_iban', 'consumer_bic', 'consumer_city' ), true ) ) {
			if ( null === $value && null === $consumer_bank_details ) {
				return null;
			}

			if ( null === $consumer_bank_details ) {
				$consumer_bank_details = new BankAccountDetails();

				$this->set_consumer_bank_details( $consumer_bank_details );
			}
		}

		switch ( $name ) {
			case 'language':
				if ( null !== $customer ) {
					$customer->set_language( $value );
				}

				return;
			case 'email':
				if ( null !== $customer ) {
					$customer->set_email( $value );
				}

				return;
			case 'first_name':
				if ( null !== $contact_name ) {
					$contact_name->set_first_name( $value );
				}

				return;
			case 'last_name':
				if ( null !== $contact_name ) {
					$contact_name->set_last_name( $value );
				}

				return;
			case 'locale':
				if ( null !== $customer ) {
					$customer->set_locale( $value );
				}

				return;
			case 'telephone_number':
				if ( null !== $address ) {
					$address->set_phone( $value );
				}

				return;
			case 'country':
				if ( null !== $address ) {
					$address->set_country_code( $value );
				}

				return;
			case 'zip':
				if ( null !== $address ) {
					$address->set_postal_code( $value );
				}

				return;
			case 'city':
				if ( null !== $address ) {
					$address->set_city( $value );
				}

				return;
			case 'address':
				if ( null !== $address ) {
					$address->set_line_1( $value );
				}

				return;
			case 'user_id':
				if ( null !== $customer ) {
					$customer->set_user_id( $value );
				}

				return;
			// @since 2.2.6
			case 'consumer_name':
				if ( null !== $consumer_bank_details ) {
					$consumer_bank_details->set_name( $value );
				}

				return;
			case 'consumer_account_number':
				if ( null !== $consumer_bank_details ) {
					$consumer_bank_details->set_account_number( $value );
				}

				return;
			case 'consumer_iban':
				if ( null !== $consumer_bank_details ) {
					$consumer_bank_details->set_iban( $value );
				}

				return;
			case 'consumer_bic':
				if ( null !== $consumer_bank_details ) {
					$consumer_bank_details->set_bic( $value );
				}

				return;
			case 'consumer_city':
				if ( null !== $consumer_bank_details ) {
					$consumer_bank_details->set_city( $value );
				}

				return;
		}

		$this->{$name} = $value;

		return null;
	}
}
