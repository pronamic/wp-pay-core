<?php
/**
 * Personal Name
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
 * Personal Name
 *
 * @link    https://en.wikipedia.org/wiki/Personal_name
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.4.0
 */
class ContactName {
	/**
	 * Full Name.
	 *
	 * @var string|null
	 */
	private $full_name;

	/**
	 * Prefix.
	 *
	 * @var string|null
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Suffix_(name)
	 */
	private $prefix;

	/**
	 * Initials.
	 *
	 * @var string|null
	 *
	 * @link https://nl.wikipedia.org/wiki/Voorletter
	 */
	private $initials;

	/**
	 * First name.
	 *
	 * @var string|null
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 */
	private $first_name;

	/**
	 * Middle name.
	 *
	 * @var string|null
	 *
	 * @link https://en.wikipedia.org/wiki/Middle_name
	 * @link https://en.wikipedia.org/wiki/Tussenvoegsel
	 */
	private $middle_name;

	/**
	 * Last name.
	 *
	 * @var string|null
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Surname
	 */
	private $last_name;

	/**
	 * Suffix.
	 *
	 * @var string|null
	 *
	 * @link https://en.wikipedia.org/wiki/Personal_name
	 * @link https://en.wikipedia.org/wiki/Suffix_(name)
	 */
	private $suffix;

	/**
	 * Get full name.
	 *
	 * @return string|null
	 */
	public function get_full_name() {
		return $this->full_name;
	}

	/**
	 * Set full name.
	 *
	 * @param string|null $full_name Full name.
	 * @return void
	 */
	public function set_full_name( $full_name ) {
		$this->full_name = $full_name;
	}

	/**
	 * Get prefix.
	 *
	 * @return string|null
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Set prefix.
	 *
	 * @param string|null $prefix Prefix.
	 * @return void
	 */
	public function set_prefix( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Get initials.
	 *
	 * @return string|null
	 */
	public function get_initials() {
		return $this->initials;
	}

	/**
	 * Set initials.
	 *
	 * @param string|null $initials Initials.
	 * @return void
	 */
	public function set_initials( $initials ) {
		$this->initials = $initials;
	}

	/**
	 * Get first name.
	 *
	 * @return string|null
	 */
	public function get_first_name() {
		return $this->first_name;
	}

	/**
	 * Set first name.
	 *
	 * @param string|null $first_name First name.
	 * @return void
	 */
	public function set_first_name( $first_name ) {
		$this->first_name = $first_name;
	}

	/**
	 * Get middle name.
	 *
	 * @return string|null
	 */
	public function get_middle_name() {
		return $this->middle_name;
	}

	/**
	 * Set middle name.
	 *
	 * @param string|null $middle_name Middle name.
	 * @return void
	 */
	public function set_middle_name( $middle_name ) {
		$this->middle_name = $middle_name;
	}

	/**
	 * Get last name.
	 *
	 * @return string|null
	 */
	public function get_last_name() {
		return $this->last_name;
	}

	/**
	 * Set last name.
	 *
	 * @param string|null $last_name Last name.
	 * @return void
	 */
	public function set_last_name( $last_name ) {
		$this->last_name = $last_name;
	}

	/**
	 * Get suffix.
	 *
	 * @return string|null
	 */
	public function get_suffix() {
		return $this->suffix;
	}

	/**
	 * Set suffix.
	 *
	 * @param string|null $suffix Suffix.
	 * @return void
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
		$data = [
			'full_name'   => $this->get_full_name(),
			'prefix'      => $this->get_prefix(),
			'initials'    => $this->get_initials(),
			'first_name'  => $this->get_first_name(),
			'middle_name' => $this->get_middle_name(),
			'last_name'   => $this->get_last_name(),
			'suffix'      => $this->get_suffix(),
		];

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create contact name from object.
	 *
	 * @param mixed $json JSON.
	 * @return ContactName
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$name = new self();

		if ( property_exists( $json, 'full_name' ) ) {
			$name->set_full_name( $json->full_name );
		}

		if ( property_exists( $json, 'prefix' ) ) {
			$name->set_prefix( $json->prefix );
		}

		if ( property_exists( $json, 'initials' ) ) {
			$name->set_initials( $json->initials );
		}

		if ( property_exists( $json, 'first_name' ) ) {
			$name->set_first_name( $json->first_name );
		}

		if ( property_exists( $json, 'middle_name' ) ) {
			$name->set_middle_name( $json->middle_name );
		}

		if ( property_exists( $json, 'last_name' ) ) {
			$name->set_last_name( $json->last_name );
		}

		if ( property_exists( $json, 'suffix' ) ) {
			$name->set_suffix( $json->suffix );
		}

		return $name;
	}

	/**
	 * Create string representation of personal name.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = [
			$this->get_prefix(),
			$this->get_first_name(),
			$this->get_middle_name(),
			$this->get_last_name(),
			$this->get_suffix(),
		];

		$pieces = array_filter( $pieces );
		$pieces = array_map( 'trim', $pieces );
		$pieces = array_filter( $pieces );

		$string = implode( ' ', $pieces );

		if ( empty( $string ) ) {
			$string = (string) $this->get_full_name();
		}

		return $string;
	}
}
