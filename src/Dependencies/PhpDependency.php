<?php
/**
 * PHP Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * PHP Dependency
 *
 * @link    https://github.com/Yoast/yoast-acf-analysis/blob/2.3.0/inc/dependencies/dependency-yoast-seo.php
 * @link    https://github.com/dsawardekar/wp-requirements/blob/0.3.0/lib/Requirements.php#L104-L118
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class PhpDependency extends Dependency {
	/**
	 * Minimum PHP version.
	 *
	 * @var string
	 */
	private $minimum_version;

	/**
	 * Construct PHP dependency.
	 *
	 * @param string $minimum_version Minimum PHP version.
	 */
	public function __construct( $minimum_version ) {
		$this->minimum_version = $minimum_version;
	}

	/**
	 * Is met.
	 *
	 * @link https://github.com/dsawardekar/wp-requirements/blob/0.3.0/lib/Requirements.php#L104-L118
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		return \version_compare(
			\strval( \phpversion() ),
			$this->minimum_version,
			'>='
		);
	}
}
