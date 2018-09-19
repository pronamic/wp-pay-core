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
	 * Street.
	 *
	 * @todo Var `street`, `street_name` or `line_1`, should we add `line_2`?
	 * @var  string
	 */
	private $street;

	/**
	 * Street number.
	 *
	 * @link https://www.pay.nl/docs/developers.php#transactions-info
	 * @todo Var `street_number`, `houseNumber` or `houseNumberOrName`?
	 * @var  string
	 */
	private $street_number;

	/**
	 * Street number extension.
	 *
	 * @link https://www.pay.nl/docs/developers.php#transactions-info
	 * @var  string
	 */
	private $street_number_extension;

	/**
	 * Postal code.
	 *
	 * @todo Var `postal_code`, `post_code`, `zip` or `zip_code`?
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
	 * @todo Var `region`, `county`, `state`, `province`, `stateOrProvince`, `stateCode?
	 * @var string
	 */
	private $region;

	/**
	 * Country.
	 *
	 * @todo Var `country` or `country_code`?
	 * @var  string
	 */
	private $country_code;

	/**
	 * Create string representation of address.
	 *
	 * @return string
	 */
	public function __toString() {
		
	}
}
