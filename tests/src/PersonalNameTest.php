<?php
/**
 * Personal Name test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * Personal Name test
 *
 * @author  Remco Tolsma
 * @version 1.0
 */
class PersonalNameTest extends WP_UnitTestCase {
	/**
	 * Test personal name.
	 */
	public function test_personal_name() {
		$name = new PersonalName();

		$name->set_prefix( 'Dr.' );
		$name->set_first_name( 'John' );
		$name->set_middle_name( 'Fitzgerald' );
		$name->set_last_name( 'Doe' );
		$name->set_suffix( 'J.D.' );

		$this->assertEquals( 'Dr.', $name->get_prefix() );
		$this->assertEquals( 'John', $name->get_first_name() );
		$this->assertEquals( 'Fitzgerald', $name->get_middle_name() );
		$this->assertEquals( 'Doe', $name->get_last_name() );
		$this->assertEquals( 'J.D.', $name->get_suffix() );

		$this->assertEquals( 'Dr. John Fitzgerald Doe J.D.', (string) $name );
	}
}
