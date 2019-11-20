<?php
/**
 * PHP Dependency test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * PHP Dependency test
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
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
