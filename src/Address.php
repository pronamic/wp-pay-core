<?php
/**
 * Address
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

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
	 * Line 1.
	 *
	 * PayPal: The first line of the address. For example, number, street, and so on.
	 *
	 * @link https://developer.paypal.com/docs/api/payments/v1/#definitions
	 *
	 * @var string|null
	 */
	private $line_1;

	/**
	 * Line 2.
	 *
	 * PayPal: The second line of the address. For example, suite or apartment number.
	 *
	 * @link https://developer.paypal.com/docs/api/payments/v1/#definitions
	 *
	 * @var string|null
	 */
	private $line_2;

	/**
	 * Street name (exclusive house number).
	 *
	 * @var string|null
	 */
	private $street_name;

	/**
	 * House number (including addition/extension).
	 *
	 * @var string|null
	 */
	private $house_number;

	/**
	 * House number base (exclusive addition/extension).
	 *
	 * @var string|null
	 */
	private $house_number_base;

	/**
	 * House number addition/extension.
	 *
	 * @var string|null
	 */
	private $house_number_addition;

	/**
	 * Postal code.
	 *
	 * Alias: `postal_code`, `post_code`, `zip` or `zip_code`.
	 *
	 * @var string
	 */
	private $postal_code;

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
	 * Country code.
	 *
	 * Alias: `country` or `country_code`.
	 *
	 * @var string
	 */
	private $country_code;

	/**
	 * Get line 1.
	 *
	 * @return string|null
	 */
	public function get_line_1() {
		return $this->line_1;
	}

	/**
	 * Set line 1.
	 *
	 * @param string|null $line_1 Line 1.
	 */
	public function set_line_1( $line_1 ) {
		$this->line_1 = $line_1;
	}

	/**
	 * Get line 2.
	 *
	 * @return string|null
	 */
	public function get_line_2() {
		return $this->line_2;
	}

	/**
	 * Set line 2.
	 *
	 * @param string|null $line_2 Line 2.
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
	 * @param string|null $postal_code Postal code.
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
	 * @param string|null $country_code Country code.
	 */
	public function set_country_code( $country_code ) {
		$this->country_code = $country_code;
	}

	/**
	 * Create string representation of address.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			$this->get_line_1(),
			$this->get_line_2(),
			$this->get_postal_code() . ' ' . $this->get_city(),
			$this->get_country_code(),
		);

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( PHP_EOL, $parts );

		return $string;
	}
}
