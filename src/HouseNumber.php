<?php
/**
 * House number
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
 * House number
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.1.6
 */
class HouseNumber {
	/**
	 * Value.
	 *
	 * @var string|null
	 */
	private $value;

	/**
	 * Base.
	 *
	 * @var string|null
	 */
	private $base;

	/**
	 * Addition.
	 *
	 * @var string|null
	 */
	private $addition;

	/**
	 * Construct house number.
	 *
	 * @param string|null $value House number.
	 */
	public function __construct( $value = null ) {
		$this->set_value( $value );
	}

	/**
	 * Get value.
	 *
	 * @return string|null
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set value.
	 *
	 * @param string|null $value Value.
	 * @return void
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Get base.
	 *
	 * @return string|null
	 */
	public function get_base() {
		return $this->base;
	}

	/**
	 * Set base.
	 *
	 * @param string|null $base Base.
	 * @return void
	 */
	public function set_base( $base ) {
		$this->base = $base;
	}

	/**
	 * Get addition.
	 *
	 * @return string|null
	 */
	public function get_addition() {
		return $this->addition;
	}

	/**
	 * Set addition.
	 *
	 * @param string|null $addition Addition.
	 * @return void
	 */
	public function set_addition( $addition ) {
		$this->addition = $addition;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'value'    => $this->value,
			'base'     => $this->base,
			'addition' => $this->addition,
		];

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create from object.
	 *
	 * @param mixed $json JSON.
	 * @return HouseNumber
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( is_string( $json ) ) {
			return new self( $json );
		}

		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$house_number = new self();

		if ( isset( $json->value ) ) {
			$house_number->set_value( $json->value );
		}

		if ( isset( $json->base ) ) {
			$house_number->set_base( $json->base );
		}

		if ( isset( $json->addition ) ) {
			$house_number->set_addition( $json->addition );
		}

		return $house_number;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
		return strval( $this->value );
	}

	/**
	 * Anonymize.
	 *
	 * @return void
	 */
	public function anonymize() {
		$this->set_value( PrivacyManager::anonymize_data( 'text', $this->get_value() ) );
		$this->set_base( PrivacyManager::anonymize_data( 'text', $this->get_base() ) );
		$this->set_addition( PrivacyManager::anonymize_data( 'text', $this->get_addition() ) );
	}
}
