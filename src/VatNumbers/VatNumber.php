<?php
/**
 * VAT Number
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT Number
 *
 * @link    https://en.wikipedia.org/wiki/VAT_identification_number
 * @author  Remco Tolsma
 * @version 2.4.0
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
	 * Validity.
	 *
	 * @var VatNumberValidity|null
	 */
	private $validity;

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
	 * Get normalized value.
	 *
	 * @return string
	 */
	public function normalized() {
		return self::normalize( $this->value );
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
	 * Get validity.
	 *
	 * @return VatNumberValidity|null
	 */
	public function get_validity() {
		return $this->validity;
	}

	/**
	 * Set validity
	 *
	 * @param VatNumberValidity|null $validity Validity.
	 * @return void
	 */
	public function set_validity( VatNumberValidity $validity = null ) {
		$this->validity = $validity;
	}

	/**
	 * Get JSON.
	 *
	 * @return string|object
	 */
	public function get_json() {
		if ( null === $this->validity ) {
			return $this->value;
		}

		$data = [
			'value'    => $this->value,
			'validity' => $this->validity->get_json(),
		];

		return (object) $data;
	}

	/**
	 * Create VAT number from JSON.
	 *
	 * @param mixed $json JSON.
	 * @return VatNumber
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( \is_string( $json ) ) {
			return new self( $json );
		}

		if ( ! \is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be either a string or object.' );
		}

		if ( ! \property_exists( $json, 'value' ) ) {
			throw new \InvalidArgumentException( 'JSON object must contain value property.' );
		}

		$vat_number = new self( $json->value );

		if ( property_exists( $json, 'validity' ) ) {
			$validity = VatNumberValidity::from_json( $json->validity );

			$vat_number->set_validity( $validity );
		}

		return $vat_number;
	}

	/**
	 * Create VAT number from string.
	 *
	 * @param string $value VAT number string.
	 * @return VatNumber
	 */
	public static function from_string( $value ) {
		return new self( $value );
	}

	/**
	 * Create VAT number from prefix and number.
	 *
	 * @param string $prefix Prefix (country code).
	 * @param string $value  VAT number.
	 * @return VatNumber
	 */
	public static function from_prefix_and_number( $prefix, $value ) {
		return new self( $prefix . $value );
	}

	/**
	 * Create string representation of VAT number.
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
			[
				' ',
				'.',
			],
			'',
			$value
		);

		return $value;
	}
}
