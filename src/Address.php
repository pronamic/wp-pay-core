<?php
/**
 * Address.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
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
 * @author Remco Tolsma
 * @since  1.4.0
 */
class Address {
	/**
	 * Contact name.
	 *
	 * @var ContactName
	 */
	private $name;

	/**
	 * Email address.
	 *
	 * @var string
	 */
	private $email;

	/**
	 * Company name.
	 *
	 * @var string
	 */
	private $company_name;

	/**
	 * Company registration number.
	 *
	 * @var string
	 */
	private $company_coc;

	/**
	 * Address line 1.
	 *
	 * @var string
	 */
	private $address_1;

	/**
	 * Address line 2.
	 *
	 * @var string
	 */
	private $address_2;

	/**
	 * Street name.
	 *
	 * @var string
	 */
	private $street_name;

	/**
	 * House number.
	 *
	 * @var string
	 */
	private $house_number;

	/**
	 * House number base (exclusive addition/extension).
	 *
	 * @var string|null
	 */
	private $house_number_base;

	/**
	 * House number addition.
	 *
	 * @var string
	 */
	private $house_number_addition;

	/**
	 * ZIP code.
	 *
	 * @var string
	 */
	private $zip;

	/**
	 * City.
	 *
	 * @var string
	 */
	private $city;

	/**
	 * Region.
	 *
	 * Alias: `region`, `county`, `state`, `province`, `stateOrProvince`, `stateCode`.
	 *
	 * @var string
	 */
	private $region;

	/**
	 * Country.
	 *
	 * @todo use country code to get country name?
	 *
	 * @var string
	 */
	private $country;

	/**
	 * Country code.
	 *
	 * Alias: `country` or `country_code`.
	 *
	 * @var string
	 */
	private $country_code;

	/**
	 * Phone.
	 *
	 * @var string
	 */
	private $phone;

	/**
	 * Contact constructor.
	 */
	public function __construct() {
		$this->set_name( new ContactName() );
	}

	/**
	 * Complement.
	 */
	public function complement() {
	}

	/**
	 * Get contact name.
	 *
	 * @return ContactName
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set contact name.
	 *
	 * @param ContactName $name Contact name.
	 */
	public function set_name( ContactName $name ) {
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
	 */
	public function set_company_name( $company_name ) {
		$this->company_name = $company_name;
	}

	/**
	 * Get company registration number.
	 *
	 * @return string|null
	 */
	public function get_company_coc() {
		return $this->company_coc;
	}

	/**
	 * Set company registration number.
	 *
	 * @param string|null $company_coc Company registration number.
	 */
	public function set_company_coc( $company_coc ) {
		$this->company_coc = $company_coc;
	}

	/**
	 * Get address 1.
	 *
	 * @return string|null
	 */
	public function get_address_1() {
		return $this->address_1;
	}

	/**
	 * Set address 1.
	 *
	 * @param string|null $address_1 Address 1.
	 */
	public function set_address_1( $address_1 ) {
		$this->address_1 = $address_1;
	}

	/**
	 * Get address 2.
	 *
	 * @return string|null
	 */
	public function get_address_2() {
		return $this->address_2;
	}

	/**
	 * Set address 2.
	 *
	 * @param string|null $address_2 Address 2.
	 */
	public function set_address_2( $address_2 ) {
		$this->address_2 = $address_2;
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
	 */
	public function set_street_name( $street_name ) {
		$this->street_name = $street_name;
	}

	/**
	 * Get house number.
	 *
	 * @return string|null
	 */
	public function get_house_number() {
		return $this->house_number;
	}

	/**
	 * Set house number.
	 *
	 * @param string|null $house_number House number.
	 */
	public function set_house_number( $house_number ) {
		$this->house_number = $house_number;
	}

	/**
	 * Get house number base.
	 *
	 * @return string|null
	 */
	public function get_house_number_base() {
		return $this->house_number_base;
	}

	/**
	 * Set house number base.
	 *
	 * @param string|null $house_number_base House number base.
	 */
	public function set_house_number_base( $house_number_base ) {
		$this->house_number_base = $house_number_base;
	}

	/**
	 * Get house number addition.
	 *
	 * @return string|null
	 */
	public function get_house_number_addition() {
		return $this->house_number_addition;
	}

	/**
	 * Set house number addition.
	 *
	 * @param string|null $house_number_addition House number addition.
	 */
	public function set_house_number_addition( $house_number_addition ) {
		$this->house_number_addition = $house_number_addition;
	}

	/**
	 * Get zip.
	 *
	 * @return string
	 */
	public function get_zip() {
		return $this->zip;
	}

	/**
	 * Set zip.
	 *
	 * @param string $zip Zip.
	 */
	public function set_zip( $zip ) {
		$this->zip = $zip;
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
	 */
	public function set_city( $city ) {
		$this->city = $city;
	}

	/**
	 * Get region.
	 *
	 * @return string|null
	 */
	public function get_region() {
		return $this->region;
	}

	/**
	 * Set region.
	 *
	 * @param string|null $region Region.
	 */
	public function set_region( $region ) {
		$this->region = $region;
	}

	/**
	 * Get country.
	 *
	 * @return string
	 */
	public function get_country() {
		return $this->country;
	}

	/**
	 * Set country.
	 *
	 * @param string $country Country.
	 */
	public function set_country( $country ) {
		$this->country = $country;
	}

	/**
	 * Get country code.
	 *
	 * @return string|null
	 */
	public function get_country_code() {
		return $this->country_code;
	}

	/**
	 * Set country code.
	 *
	 * @throws InvalidArgumentException Thrown when country code length is not equal to 2.
	 *
	 * @param string $country_code Country code.
	 */
	public function set_country_code( $country_code ) {
		if ( 2 !== strlen( $country_code ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Given country code `%s` not ISO 3166-1 alpha-2 value.',
					$country_code
				)
			);
		}

		$this->country_code = $country_code;
	}

	/**
	 * Get phone.
	 *
	 * @return string
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * Set phone.
	 *
	 * @param string $phone Phone.
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
		$data = array(
			'name'                  => $this->get_name()->get_json(),
			'email'                 => $this->get_email(),
			'company_name'          => $this->get_company_name(),
			'company_coc'           => $this->get_company_coc(),
			'address_1'             => $this->get_address_1(),
			'address_2'             => $this->get_address_2(),
			'street_name'           => $this->get_street_name(),
			'house_number'          => $this->get_house_number(),
			'house_number_addition' => $this->get_house_number_addition(),
			'zip'                   => $this->get_zip(),
			'city'                  => $this->get_city(),
			'region'                => $this->get_region(),
			'country'               => $this->get_country(),
			'country_code'          => $this->get_country_code(),
			'phone'                 => $this->get_phone(),
		);

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create address from object.
	 *
	 * @param stdClass $object Object.
	 * @return Address
	 */
	public static function from_object( stdClass $object ) {
		$address = new self();

		foreach ( $object as $key => $value ) {
			$method = sprintf( 'set_%s', $key );

			if ( is_callable( array( $address, $method ) ) ) {
				if ( 'name' === $key ) {
					$value = ContactName::from_object( $value );
				}

				call_user_func( array( $address, $method ), $value );
			}
		}

		return $address;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			$this->get_name(),
			$this->get_company_name(),
			$this->get_address_1(),
			$this->get_address_2(),
			$this->get_street_name(),
			$this->get_house_number(),
			$this->get_zip() . ' ' . $this->get_city(),
			$this->get_region(),
			$this->get_country_code(),
			$this->get_phone(),
			$this->get_email(),
			$this->get_company_coc(),
		);

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( PHP_EOL, $parts );

		return $string;
	}
}
