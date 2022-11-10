<?php
/**
 * Mode Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Mode Trait
 *
 * @link    https://github.com/search?q=%22trait+VersionTrait%22+language%3APHP&type=Code
 * @since   2.5.0
 * @version 2.5.0
 * @author  Remco Tolsma
 */
trait ModeTrait {
	/**
	 * Mode.
	 *
	 * @var string
	 */
	protected $mode = 'live';

	/**
	 * Set mode.
	 *
	 * @param string $mode Mode.
	 *
	 * @return void
	 * @throws \InvalidArgumentException Throws invalid argument exception when mode is not a string or not one of the mode constants.
	 */
	public function set_mode( $mode ) {
		if ( ! is_string( $mode ) ) {
			throw new \InvalidArgumentException( 'Mode must be a string.' );
		}

		if ( ! in_array( $mode, [ Gateway::MODE_TEST, Gateway::MODE_LIVE ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid mode.' );
		}

		$this->mode = $mode;
	}

	/**
	 * Get mode.
	 *
	 * @return string
	 */
	public function get_mode() {
		return $this->mode;
	}
}
