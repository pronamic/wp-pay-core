<?php
/**
 * Dependencies
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Dependencies
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class DependenciesTest extends TestCase {
	/**
	 * Test dependencies.
	 */
	public function test_dependencies() {
		$dependencies = new Dependencies();

		$dependencies->add( new WordPressDependency( '4.7' ) );

		$this->assertTrue( $dependencies->are_met() );
	}
}
