<?php
/**
 * Country
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
 * Country
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.1.6
 */
class Country {
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
	 * @throws InvalidArgumentException Thrown when country code length is not equal to 2.
	 *
	 * @param string|null $code Code.
	 * @return void
	 */
	public function set_code( $code ) {
		if ( null !== $code && 2 !== strlen( $code ) ) {
			throw new InvalidArgumentException(
				\sprintf(
					'Given country code `%s` not ISO 3166-1 alpha-2 value.',
					\esc_html( $code )
				)
			);
		}

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
			'code' => $this->code,
			'name' => $this->name,
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
	 * @return Country
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$country = new self();

		if ( isset( $json->code ) ) {
			$country->set_code( $json->code );
		}

		if ( isset( $json->name ) ) {
			$country->set_name( $json->name );
		}

		return $country;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
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
		$this->set_code( PrivacyManager::anonymize_data( 'text', $this->get_code() ) );
		$this->set_name( PrivacyManager::anonymize_data( 'text', $this->get_name() ) );
	}
}
