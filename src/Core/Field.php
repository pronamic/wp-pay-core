<?php
/**
 * Field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Field class
 */
class Field {
	/**
	 * ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Required.
	 *
	 * @var bool
	 */
	private $required = false;

	/**
	 * Construct field
	 *
	 * @param string $id ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Get ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set required.
	 *
	 * @param bool $required Required.
	 */
	public function set_required( $required ) {
		$this->required = $required;
	}
}
