<?php
/**
 * Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * Dependency
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
abstract class Dependency {
	/**
	 * Is met.
	 *
	 * @return bool True if dependency is met, false otherwise.
	 */
	abstract public function is_met();
}
