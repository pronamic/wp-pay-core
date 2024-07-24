<?php
/**
 * Version Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Version Trait
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 * @link    https://github.com/search?q=%22trait+VersionTrait%22+language%3APHP&type=Code
 */
trait VersionTrait {
	/**
	 * Version.
	 *
	 * @var string|null
	 */
	private $version;

	/**
	 * Set version.
	 *
	 * @param string|null $version Version.
	 * @return void
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * Get version.
	 *
	 * @return string|null
	 */
	public function get_version() {
		return $this->version;
	}
}
