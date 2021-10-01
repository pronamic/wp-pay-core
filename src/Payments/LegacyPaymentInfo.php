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
 * @property string|null $locale
 * @property string|null $email
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
			case 'email':
				return ( null === $customer ) ? null : $customer->get_email();
			case 'user_agent':
				return ( null === $customer ) ? null : $customer->get_user_agent();
			case 'user_id':
				return ( null === $customer ) ? null : $customer->get_user_id();
			case 'user_ip':
				return ( null === $customer ) ? null : $customer->get_ip_address();
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
