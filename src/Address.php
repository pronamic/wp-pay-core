<?php
/**
 * Address.php.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use \stdClass;

/**
 * Address.php
 *
 * @author  Reüel van der Steege
 * @since   x.x.x
 * @version x.x.x
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
	 * @var string
	 */
	private $region;

	/**
	 * Country.
	 *
	 * @var string
	 */
	private $country;

	/**
	 * Country code.
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
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string $email Email.
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Get company name.
	 *
	 * @return string
	 */
	public function get_company_name() {
		return $this->company_name;
	}

	/**
	 * Set company name.
	 *
	 * @param string $company_name Company name.
	 */
	public function set_company_name( $company_name ) {
		$this->company_name = $company_name;
	}

	/**
	 * Get company registration number.
	 *
	 * @return string
	 */
	public function get_company_coc() {
		return $this->company_coc;
	}

	/**
	 * Set company registration number.
	 *
	 * @param string $company_coc Company registration number.
	 */
	public function set_company_coc( $company_coc ) {
		$this->company_coc = $company_coc;
	}

	/**
	 * Get address 1.
	 *
	 * @return string
	 */
	public function get_address_1() {
		return $this->address_1;
	}

	/**
	 * Set address 1.
	 *
	 * @param string $address_1 Address 1.
	 */
	public function set_address_1( $address_1 ) {
		$this->address_1 = $address_1;
	}

	/**
	 * Get address 2.
	 *
	 * @return string
	 */
	public function get_address_2() {
		return $this->address_2;
	}

	/**
	 * Set address 2.
	 *
	 * @param string $address_2 Address 2.
	 */
	public function set_address_2( $address_2 ) {
		$this->address_2 = $address_2;
	}

	/**
	 * Get street name.
	 *
	 * @return string
	 */
	public function get_street_name() {
		return $this->street_name;
	}

	/**
	 * Set street name.
	 *
	 * @param string $street_name Street name.
	 */
	public function set_street_name( $street_name ) {
		$this->street_name = $street_name;
	}

	/**
	 * Get house number.
	 *
	 * @return string
	 */
	public function get_house_number() {
		return $this->house_number;
	}

	/**
	 * Set house number.
	 *
	 * @param string $house_number House number.
	 */
	public function set_house_number( $house_number ) {
		$this->house_number = $house_number;
	}

	/**
	 * Get house number addition.
	 *
	 * @return string
	 */
	public function get_house_number_addition() {
		return $this->house_number_addition;
	}

	/**
	 * Set house number addition.
	 *
	 * @param string $house_number_addition House number addition.
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
	 * @return string
	 */
	public function get_city() {
		return $this->city;
	}

	/**
	 * Set city.
	 *
	 * @param string $city City.
	 */
	public function set_city( $city ) {
		$this->city = $city;
	}

	/**
	 * Get region.
	 *
	 * @return string
	 */
	public function get_region() {
		return $this->region;
	}

	/**
	 * Set region.
	 *
	 * @param string $region Region.
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
	 * @return string
	 */
	public function get_country_code() {
		return $this->country_code;
	}

	/**
	 * Set country code.
	 *
	 * @param string $country_code Country code.
	 */
	public function set_country_code( $country_code ) {
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
		$pieces = array(
			$this->get_name(),
			$this->get_email(),
			$this->get_company_name(),
			$this->get_company_coc(),
			$this->get_address_1(),
			$this->get_address_2(),
			$this->get_street_name(),
			$this->get_house_number(),
			$this->get_house_number_addition(),
			$this->get_zip(),
			$this->get_city(),
			$this->get_region(),
			$this->get_country(),
			$this->get_country_code(),
			$this->get_phone(),
		);

		$pieces = array_filter( $pieces );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
