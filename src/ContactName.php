<?php
/**
 * Personal Name
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use stdClass;

/**
 * Personal Name
 *
 * @link   https://en.wikipedia.org/wiki/Personal_name
 * @author Remco Tolsma
 * @since  1.4.0
 */
class ContactName {
	/**
	 * Prefix.
	 *
	 * @var string
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Suffix_(name)
	 */
	private $prefix;

	/**
	 * First name.
	 *
	 * @var string
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 */
	private $first_name;

	/**
	 * Middle name.
	 *
	 * @var string
	 *
	 * @link https://en.wikipedia.org/wiki/Middle_name
	 * @link https://en.wikipedia.org/wiki/Tussenvoegsel
	 */
	private $middle_name;

	/**
	 * Last name.
	 *
	 * @var string
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Surname
	 */
	private $last_name;

	/**
	 * Suffix.
	 *
	 * @var string
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Suffix_(name)
	 */
	private $suffix;

	/**
	 * Get prefix.
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Set prefix.
	 *
	 * @param string $prefix Prefix.
	 */
	public function set_prefix( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Get first name.
	 *
	 * @return string
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * Set first name.
	 *
	 * @param string $first_name First name.
	 */
	public function set_first_name( $first_name ) {
		$this->first_name = $first_name;
	}

	/**
	 * Get middle name.
	 *
	 * @return string
	 */
	public function get_middle_name() {
		return $this->middle_name;
	}

	/**
	 * Set middle name.
	 *
	 * @param string $middle_name Middle name.
	 */
	public function set_middle_name( $middle_name ) {
		$this->middle_name = $middle_name;
	}

	/**
	 * Get last name.
	 *
	 * @return string
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * Set last name.
	 *
	 * @param string $last_name Last name.
	 */
	public function set_last_name( $last_name ) {
		$this->last_name = $last_name;
	}

	/**
	 * Get suffix.
	 *
	 * @return string
	 */
	public function get_suffix() {
		return $this->suffix;
	}

	/**
	 * Set suffix.
	 *
	 * @param string $suffix Suffix.
	 */
	public function set_suffix( $suffix ) {
		$this->suffix = $suffix;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = array(
			'prefix'     => $this->get_prefix(),
			'first_name' => $this->get_first_name(),
			'midle_name' => $this->get_middle_name(),
			'last_name'  => $this->get_last_name(),
			'suffix'     => $this->get_suffix(),
		);

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create contact name from object.
	 *
	 * @param stdClass $object Object.
	 * @return ContactName
	 */
	public static function from_object( stdClass $object ) {
		$contact_name = new self();

		$properties = get_class_vars( get_class( $contact_name ) );

		foreach ( $properties as $property => $default_value ) {
			$method = sprintf( 'set_%s', $property );

			if ( isset( $object->{$property} ) && is_callable( array( $contact_name, $method ) ) ) {
				$value = $object->{$property};

				call_user_func( array( $contact_name, $method ), $value );
			}
		}

		return $contact_name;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = array(
			$this->get_prefix(),
			$this->get_first_name(),
			$this->get_middle_name(),
			$this->get_last_name(),
			$this->get_suffix(),
		);

		$pieces = array_map( 'trim', $pieces );

		$pieces = array_filter( $pieces );

		$string = implode( ' ', $pieces );

		return $string;
	}
}
