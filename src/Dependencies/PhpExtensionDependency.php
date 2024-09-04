<?php
/**
 * PHP Extension Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * PHP Extension Dependency
 *
 * @link    https://github.com/Yoast/yoast-acf-analysis/blob/2.3.0/inc/dependencies/dependency-yoast-seo.php
 * @link    https://github.com/dsawardekar/wp-requirements/blob/0.3.0/lib/Requirements.php#L104-L118
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class PhpExtensionDependency extends Dependency {
	/**
	 * Required PHP extension.
	 *
	 * @var string
	 */
	private $required_extension;

	/**
	 * Construct PHP extension dependency.
	 *
	 * @param string $required_extension Required PHP extension.
	 */
	public function __construct( $required_extension ) {
		$this->required_extension = $required_extension;
	}

	/**
	 * Is met.
	 *
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		return \extension_loaded( $this->required_extension );
	}
}
