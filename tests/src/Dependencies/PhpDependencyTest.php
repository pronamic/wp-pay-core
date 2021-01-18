<?php
/**
 * PHP Dependency test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * PHP Dependency test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class PhpDependencyTest extends \WP_UnitTestCase {
	/**
	 * Test dependency is met.
	 */
	public function test_is_met() {
		$dependency = new PhpDependency( '5.6' );

		$this->assertTrue( $dependency->is_met() );
	}

	/**
	 * Test dependency is not met.
	 */
	public function test_is_not_met() {
		$dependency = new PhpDependency( '100' );

		$this->assertFalse( $dependency->is_met() );
	}
}
