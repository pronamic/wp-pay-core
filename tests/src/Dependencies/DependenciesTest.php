<?php
/**
 * Dependencies
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

/**
 * Dependencies
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class DependenciesTest extends \WP_UnitTestCase {
	/**
	 * Test dependencies.
	 */
	public function test_dependencies() {
		$dependencies = new Dependencies();

		$dependencies->add( new WordPressDependency( '4.7' ) );

		$this->assertTrue( $dependencies->are_met() );
	}
}
