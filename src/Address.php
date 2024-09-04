<?php
/**
 * Address.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use InvalidArgumentException;
use stdClass;

/**
 * Address
 *
 * @link   https://en.wikipedia.org/wiki/HTML_element#address
 * @link   https://en.wikipedia.org/wiki/Address_(geography)
 * @link   https://tools.ietf.org/html/rfc6350#section-6.3.1
 * @link   https://schema.org/PostalAddress
 * @link   https://github.com/wp-premium/gravityforms/blob/2.3.2/includes/fields/class-gf-field-address.php
 * @link   https://developer.salesforce.com/docs/atlas.en-us.object_reference.meta/object_reference/sforce_api_objects_address.htm
 * @link   https://c3.twinfield.com/webservices/documentation/#/ApiReference/Masters/Suppliers#Create-Update-Delete
 * @link   https://github.com/wp-pay-gateways/omnikassa-2/blob/develop/src/Address.php
 * @link   https://docs.adyen.com/developers/api-reference/common-api#address
 * @link   https://developer.paypal.com/docs/api/payments/v1/#definition-address
 * @link   https://docs.mollie.com/reference/v2/payments-api/create-payment
 * @link   https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/java/payments/create.html#payments-create-payload
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.1.0
 */
class Address {
	/**
	 * Contact name.
	 *
	 * @var ContactName|null
	 */
	private $name;

	/**
	 * Email address.
	 *
	 * @var string|null
	 */
	private $email;

	/**
	 * Company name.
	 *
	 * @var string|null
	 */
	private $company_name;

	/**
	 * Chamber of Commerce registration number.
	 *
	 * @var string|null
	 */
	private $coc_number;

	/**
	 * Address line 1.
	 *
	 * @var string|null
	 */
	private $line_1;

	/**
	 * Address line 2.
	 *
	 * @var string|null
	 */
	private $line_2;

	/**
	 * Street name.
	 *
	 * @var string|null
	 */
	private $street_name;

	/**
	 * House number.
	 *
	 * @var HouseNumber|null
	 */
	private $house_number;

	/**
	 * Postal Code.
	 *
	 * @var string|null
	 */
	private $postal_code;

	/**
	 * City.
	 *
	 * @var string|null
	 */
	private $city;

	/**
	 * Region.
	 *
	 * Alias: `region`, `county`, `state`, `province`, `stateOrProvince`, `stateCode`.
	 *
	 * @var Region|null
	 */
	private $region;

	/**
	 * Country.
	 *
	 * @var Country|null
	 */
	private $country;

	/**
	 * Phone.
	 *
	 * @var string|null
	 */
	private $phone;

	/**
	 * Get contact name.
	 *
	 * @return ContactName|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set contact name.
	 *
	 * @param ContactName|null $name Contact name.
	 * @return void
	 */
	public function set_name( ContactName $name = null ) {
		$this->name = $name;
	}

	/**
	 * Get email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string|null $email Email.
	 * @return void
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Get company name.
	 *
	 * @return string|null
	 */
	public function get_company_name() {
		return $this->company_name;
	}

	/**
	 * Set company name.
	 *
	 * @param string|null $company_name Company name.
	 * @return void
	 */
	public function set_company_name( $company_name ) {
		$this->company_name = $company_name;
	}

	/**
	 * Get Kamer van Koophandel number.
	 *
	 * @return string|null
	 */
	public function get_coc_number() {
		return $this->coc_number;
	}

	/**
	 * Set Kamer van Koophandel number.
	 *
	 * @param string|null $coc_number Kamer van Koophandel number.
	 * @return void
	 */
	public function set_coc_number( $coc_number ) {
		$this->coc_number = $coc_number;
	}

	/**
	 * Get address line 1.
	 *
	 * @return string|null
	 */
	public function get_line_1() {
		return $this->line_1;
	}

	/**
	 * Set address line 1.
	 *
	 * @param string|null $line_1 Address 1.
	 * @return void
	 */
	public function set_line_1( $line_1 ) {
		$this->line_1 = $line_1;
	}

	/**
	 * Get address line 2.
	 *
	 * @return string|null
	 */
	public function get_line_2() {
		return $this->line_2;
	}

	/**
	 * Set address line 2.
	 *
	 * @param string|null $line_2 Address 2.
	 * @return void
	 */
	public function set_line_2( $line_2 ) {
		$this->line_2 = $line_2;
	}

	/**
	 * Get street name.
	 *
	 * @return string|null
	 */
	public function get_street_name() {
		return $this->street_name;
	}

	/**
	 * Set street name.
	 *
	 * @param string|null $street_name Street name.
	 * @return void
	 */
	public function set_street_name( $street_name ) {
		$this->street_name = $street_name;
	}

	/**
	 * Get house number.
	 *
	 * @return HouseNumber|null
	 */
	public function get_house_number() {
		return $this->house_number;
	}

	/**
	 * Set house number.
	 *
	 * @param string|HouseNumber|null $house_number House number.
	 * @return void
	 */
	public function set_house_number( $house_number ) {
		if ( is_string( $house_number ) ) {
			$house_number = new HouseNumber( $house_number );
		}

		$this->house_number = $house_number;
	}

	/**
	 * Get house number base.
	 *
	 * @return string|null
	 */
	public function get_house_number_base() {
		if ( null === $this->house_number ) {
			return null;
		}

		return $this->house_number->get_base();
	}

	/**
	 * Set house number base.
	 *
	 * @param string|null $house_number_base House number base.
	 * @return void
	 */
	public function set_house_number_base( $house_number_base ) {
		if ( null === $this->house_number ) {
			$this->house_number = new HouseNumber();
		}

		$this->house_number->set_base( $house_number_base );
	}

	/**
	 * Get house number addition.
	 *
	 * @return string|null
	 */
	public function get_house_number_addition() {
		if ( null === $this->house_number ) {
			return null;
		}

		return $this->house_number->get_addition();
	}

	/**
	 * Set house number addition.
	 *
	 * @param string|null $house_number_addition House number addition.
	 * @return void
	 */
	public function set_house_number_addition( $house_number_addition ) {
		if ( null === $this->house_number ) {
			$this->house_number = new HouseNumber();
		}

		$this->house_number->set_addition( $house_number_addition );
	}

	/**
	 * Get postal code.
	 *
	 * @return string|null
	 */
	public function get_postal_code() {
		return $this->postal_code;
	}

	/**
	 * Set postal code.
	 *
	 * @param string|null $postal_code Postal Code.
	 * @return void
	 */
	public function set_postal_code( $postal_code ) {
		$this->postal_code = $postal_code;
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function get_city() {
		return $this->city;
	}

	/**
	 * Set city.
	 *
	 * @param string|null $city City.
	 * @return void
	 */
	public function set_city( $city ) {
		$this->city = $city;
	}

	/**
	 * Get region.
	 *
	 * @return Region|null
	 */
	public function get_region() {
		return $this->region;
	}

	/**
	 * Set region.
	 *
	 * @param string|Region|null $region Region.
	 * @return void
	 */
	public function set_region( $region ) {
		if ( is_string( $region ) ) {
			$region = new Region( $region );
		}

		$this->region = $region;
	}

	/**
	 * Get country.
	 *
	 * @return Country|null
	 */
	public function get_country() {
		return $this->country;
	}

	/**
	 * Set country.
	 *
	 * @param Country|null $country Country.
	 * @return void
	 */
	public function set_country( $country ) {
		$this->country = $country;
	}

	/**
	 * Get ISO 3166-1 alpha-2 country code.
	 *
	 * @return string|null
	 */
	public function get_country_code() {
		if ( null === $this->country ) {
			return null;
		}

		return $this->country->get_code();
	}

	/**
	 * Set country code.
	 *
	 * @throws InvalidArgumentException Thrown when country code length is not equal to 2.
	 *
	 * @param null|string $country_code Country code.
	 * @return void
	 */
	public function set_country_code( $country_code ) {
		if ( null === $this->country ) {
			$this->country = new Country();
		}

		$this->country->set_code( $country_code );
	}

	/**
	 * Get country name.
	 *
	 * @return string|null
	 */
	public function get_country_name() {
		if ( null === $this->country ) {
			return null;
		}

		return $this->country->get_name();
	}

	/**
	 * Set country name.
	 *
	 * @param string|null $country_name Country name.
	 * @return void
	 */
	public function set_country_name( $country_name ) {
		if ( null === $this->country ) {
			$this->country = new Country();
		}

		$this->country->set_name( $country_name );
	}

	/**
	 * Get phone.
	 *
	 * @return string|null
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * Set phone.
	 *
	 * @param string|null $phone Phone.
	 * @return void
	 */
	public function set_phone( $phone ) {
		$this->phone = $phone;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'name'         => ( null === $this->name ) ? null : $this->name->get_json(),
			'email'        => $this->get_email(),
			'company_name' => $this->get_company_name(),
			'coc_number'   => $this->get_coc_number(),
			'line_1'       => $this->get_line_1(),
			'line_2'       => $this->get_line_2(),
			'street_name'  => $this->get_street_name(),
			'house_number' => ( null === $this->house_number ) ? null : $this->house_number->get_json(),
			'postal_code'  => $this->get_postal_code(),
			'city'         => $this->get_city(),
			'region'       => ( null === $this->region ) ? null : $this->region->get_json(),
			'country'      => ( null === $this->country ) ? null : $this->country->get_json(),
			'phone'        => $this->get_phone(),
		];

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create address from object.
	 *
	 * @param mixed $json JSON.
	 * @return Address
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$address = new self();

		if ( isset( $json->name ) ) {
			$address->set_name( ContactName::from_json( $json->name ) );
		}

		if ( isset( $json->email ) ) {
			$address->set_email( $json->email );
		}

		if ( isset( $json->company_name ) ) {
			$address->set_company_name( $json->company_name );
		}

		if ( isset( $json->coc_number ) ) {
			$address->set_coc_number( $json->coc_number );
		}

		if ( isset( $json->line_1 ) ) {
			$address->set_line_1( $json->line_1 );
		}

		if ( isset( $json->line_2 ) ) {
			$address->set_line_2( $json->line_2 );
		}

		if ( isset( $json->street_name ) ) {
			$address->set_street_name( $json->street_name );
		}

		if ( isset( $json->house_number ) || isset( $json->house_number_base ) || isset( $json->house_number_addition ) ) {
			$house_number = new HouseNumber();

			if ( isset( $json->house_number ) ) {
				$house_number = HouseNumber::from_json( $json->house_number );
			}

			if ( isset( $json->house_number_base ) ) {
				$house_number->set_base( $json->house_number_base );
			}

			if ( isset( $json->house_number_addition ) ) {
				$house_number->set_addition( $json->house_number_addition );
			}

			$address->set_house_number( $house_number );
		}

		if ( isset( $json->postal_code ) ) {
			$address->set_postal_code( $json->postal_code );
		}

		if ( isset( $json->city ) ) {
			$address->set_city( $json->city );
		}

		if ( isset( $json->region ) ) {
			$address->set_region( Region::from_json( $json->region ) );
		}

		if ( isset( $json->country ) || isset( $json->country_code ) || isset( $json->country_name ) ) {
			$country = isset( $json->country ) ? Country::from_json( $json->country ) : new Country();

			if ( isset( $json->country_code ) ) {
				$country->set_code( $json->country_code );
			}

			if ( isset( $json->country_name ) ) {
				$country->set_name( $json->country_name );
			}

			$address->set_country( $country );
		}

		if ( isset( $json->phone ) ) {
			$address->set_phone( $json->phone );
		}

		return $address;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = [
			$this->get_company_name(),
			$this->get_name(),
			$this->get_line_1(),
			$this->get_line_2(),
			strval( $this->get_postal_code() ) . ' ' . strval( $this->get_city() ),
			$this->get_country_code(),
			$this->get_phone(),
			$this->get_email(),
		];

		$parts = array_map( 'strval', $parts );

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( PHP_EOL, $parts );

		return $string;
	}
}
