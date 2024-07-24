<?php
/**
 * Id Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Id Trait
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 * @link    https://github.com/search?q=%22trait+IdTrait%22+language%3APHP&type=Code
 */
trait IdTrait {
	/**
	 * ID.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * Get the ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the ID.
	 *
	 * @param int $id Unique ID.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}
}
