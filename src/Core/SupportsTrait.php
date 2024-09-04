<?php
/**
 * Supports trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Supports trait class
 */
trait SupportsTrait {
	/**
	 * Supported features.
	 *
	 * @var array
	 */
	protected $supports = [];

	/**
	 * Add support.
	 *
	 * @param string $feature Feature.
	 * @return void
	 */
	public function add_support( $feature ) {
		$this->supports[] = $feature;
	}

	/**
	 * Check if supports a given feature.
	 *
	 * @param string $feature The feature to check.
	 * @return bool True if supported, false otherwise.
	 */
	public function supports( $feature ) {
		return in_array( $feature, $this->supports, true );
	}
}
