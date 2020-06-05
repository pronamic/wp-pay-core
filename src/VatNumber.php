<?php
/**
 * VAT Number
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * VAT Number
 *
 * @link    https://en.wikipedia.org/wiki/VAT_identification_number
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.4.0
 */
class VatNumber {
	/**
	 * Value.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * Construct VAT number object.
	 *
	 * @param string $value VAT identification number.
	 */
	public function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * Get value.
	 *
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Get 2 digit prefix.
	 *
	 * The full identifier starts with an ISO 3166-1 alpha-2 (2 letters) country code (except for Greece, which uses the ISO 639-1 language code EL for the Greek language, instead of its ISO 3166-1 alpha-2 country code GR).
	 *
	 * @link https://en.wikipedia.org/wiki/VAT_identification_number
	 * @return string
	 */
	public function get_2_digit_prefix() {
		$value = self::normalize( $this->value );

		$prefix = \substr( $value, 0, 2 );

		return $prefix;
	}

	/**
	 * Get the number without the 2 digit prefix.
	 *
	 * @link https://en.wikipedia.org/wiki/VAT_identification_number
	 * @return string
	 */
	public function normalized_without_prefix() {
		$value = self::normalize( $this->value );

		return \substr( $value, 2 );
	}

	/**
	 * Get JSON.
	 *
	 * @return string
	 */
	public function get_json() {
		return $this->value;
	}

	/**
	 * Create contact name from object.
	 *
	 * @param mixed $json JSON.
	 * @return VatNumber
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		return new self( $json );
	}

	/**
	 * Create string representation of VAT nunber.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->value;
	}

	/**
	 * Normalize VAT number.
	 *
	 * @link https://gitlab.com/pronamic-plugins/edd-vat/-/blob/1.0.0/includes/class-check-vat-eu.php#L39-47
	 * @param string $value VAT identification number.
	 * @return string
	 */
	public static function normalize( $value ) {
		/**
		 * Replace white spaces and dots.
		 */
		$value = \str_replace(
			array(
				' ',
				'.',
			),
			'',
			$value
		);

		return $value;
	}
}
