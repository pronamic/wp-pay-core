<?php
/**
 * WordPress Dependency test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * WordPress Dependency test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class WordPressDependencyTest extends TestCase {
	/**
	 * Test dependency is met.
	 */
	public function test_is_met() {
		$dependency = new WordPressDependency( '4.7' );

		$this->assertTrue( $dependency->is_met() );
	}

	/**
	 * Test dependency is not met.
	 */
	public function test_is_not_met() {
		$dependency = new WordPressDependency( '100' );

		$this->assertFalse( $dependency->is_met() );
	}
}
