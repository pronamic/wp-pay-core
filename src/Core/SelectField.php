<?php
/**
 * Select field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Select field class
 */
class SelectField extends Field {
	public function set_options_callback( $options_callback ) {
		$this->options_callback = $options_callback;
	}

	/**
	 * Get optoins.
	 *
	 * @return string
	 */
	public function get_options() {
		return call_user_func( $this->options_callback );
	}
}
