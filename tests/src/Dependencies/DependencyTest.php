<?php
/**
 * Dependency test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Dependencies
 */

namespace Pronamic\WordPress\Pay\Dependencies;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Dependency test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class DependencyTest extends TestCase {
	/**
	 * Test dependency is met.
	 *
	 * @link https://phpunit.de/manual/6.5/en/test-doubles.html#test-doubles.mocking-traits-and-abstract-classes
	 */
	public function test_is_met() {
		$mock = $this->getMockForAbstractClass( Dependency::class );

		$mock->expects( $this->any() )->method( 'is_met' )->will( $this->returnValue( true ) );

		$this->assertTrue( $mock->is_met() );
	}

	/**
	 * Test dependency is not met.
	 */
	public function test_is_not_met() {
		$mock = $this->getMockForAbstractClass( Dependency::class );

		$mock->expects( $this->any() )->method( 'is_met' )->will( $this->returnValue( false ) );

		$this->assertFalse( $mock->is_met() );
	}
}
