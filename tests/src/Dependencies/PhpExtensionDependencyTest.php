<?php
/**
 * PHP Extension Dependency test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * PHP Extension Dependency test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class PhpExtensionDependencyTest extends \WP_UnitTestCase {
	/**
	 * Test loaded extensions.
	 *
	 * @link https://www.php.net/manual/en/function.get-loaded-extensions.php
	 */
	public function test_loaded_extensions() {
		$extensions = get_loaded_extensions();

		foreach ( get_loaded_extensions()  as $extension ) {
			$dependency = new PhpExtensionDependency( $extension );

			$this->assertTrue( $dependency->is_met() );
		}
	}

	/**
	 * Test non-existing extension.
	 */
	public function test_non_existent_extension() {
		$dependency = new PhpExtensionDependency( 'non-existing-extension' );

		$this->assertFalse( $dependency->is_met() );
	}
}
