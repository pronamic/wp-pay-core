<?php
/**
 * Region
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
 * Region
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.1.6
 */
class Region {
	/**
	 * Value.
	 *
	 * @var string|null
	 */
	private $value;

	/**
	 * Code.
	 *
	 * @var string|null
	 */
	private $code;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Construct region.
	 *
	 * @param string|null $value Value.
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
	 * Get code.
	 *
	 * @return string|null
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Set code.
	 *
	 * @param string|null $code Code.
	 * @return void
	 */
	public function set_code( $code ) {
		$this->code = $code;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'value' => $this->value,
			'code'  => $this->code,
			'name'  => $this->name,
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
	 * @return Region
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( is_string( $json ) ) {
			return new self( $json );
		}

		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$region = new self();

		if ( isset( $json->value ) ) {
			$region->set_value( $json->value );
		}

		if ( isset( $json->code ) ) {
			$region->set_code( $json->code );
		}

		if ( isset( $json->name ) ) {
			$region->set_name( $json->name );
		}

		return $region;
	}

	/**
	 * Create string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		if ( is_string( $this->value ) ) {
			return $this->value;
		}

		$values = [
			$this->code,
			$this->name,
		];

		$values = array_filter( $values );

		return implode( ' - ', $values );
	}

	/**
	 * Anonymize.
	 *
	 * @return void
	 */
	public function anonymize() {
		$this->set_value( PrivacyManager::anonymize_data( 'text', $this->get_value() ) );
		$this->set_code( PrivacyManager::anonymize_data( 'text', $this->get_code() ) );
		$this->set_name( PrivacyManager::anonymize_data( 'text', $this->get_name() ) );
	}
}
