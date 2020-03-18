<?php
/**
 * Contact name helper test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

/**
 * Contact name helper test
 *
 * @author  Remco Tolsma
 * @version 2.2.8
 * @since   2.2.8
 */
class ContactNameHelperTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test empty name.
	 */
	public function test_complement_empty_name_initials() {
		$name = new ContactName();

		ContactNameHelper::complement_name( $name );

		$this->assertNull( $name->get_initials() );
	}

	/**
	 * Test multiple first names initials.
	 */
	public function test_complement_multiple_first_names_initials() {
		$name = new ContactName();

		$name->set_first_name( 'John Jane Test' );
		$name->set_middle_name( 'Fitzgerald' );

		ContactNameHelper::complement_name( $name );

		$this->assertEquals( 'J.J.T.F.', $name->get_initials() );
	}

	/**
	 * Test spacy name initials.
	 */
	public function test_complement_spacy_name_initials() {
		$name = new ContactName();

		$name->set_first_name( '     John     Jane     ' );
		$name->set_middle_name( '     Fitzgerald     ' );

		ContactNameHelper::complement_name( $name );

		$this->assertEquals( 'J.J.F.', $name->get_initials() );
	}

	/**
	 * Test mutlibyte name initials.
	 */
	public function test_complement_mb_name_initials() {
		$name = new ContactName();

		$name->set_first_name( '𝔍𝔬𝔥𝔫' );
		$name->set_middle_name( '𝔉𝔦𝔱𝔷𝔤𝔢𝔯𝔞𝔩𝔡' );

		ContactNameHelper::complement_name( $name );

		$this->assertEquals( '𝔍.𝔉.', $name->get_initials() );
	}

	/**
	 * Test initials complement.
	 */
	public function test_complement_initials() {
		$name = new ContactName();

		$name->set_first_name( 'John' );
		$name->set_middle_name( 'Fitzgerald' );
		$name->set_last_name( 'Doe' );

		ContactNameHelper::complement_name( $name );

		$this->assertEquals( 'J.F.', $name->get_initials() );

		$name = new ContactName();

		$name->set_first_name( 'Jane' );
		$name->set_last_name( 'Doe' );

		ContactNameHelper::complement_name( $name );

		$this->assertEquals( 'J.', $name->get_initials() );
	}
}
