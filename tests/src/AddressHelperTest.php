<?php
/**
 * Address helper test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * Address helper test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class AddressHelperTest extends WP_UnitTestCase {
	/**
	 * Test address setters and getters.
	 */
	public function test_setters_and_getters() {
		$address = new Address();

		$this->assertInstanceOf( __NAMESPACE__ . '\Address', $address );

		$address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$address->set_line_2( '1e etage' );

		AddressHelper::complement_address( $address );

		$this->assertEquals( 'Burgemeester Wuiteweg', $address->get_street_name() );
		$this->assertEquals( '39b', $address->get_house_number() );
		$this->assertEquals( '39', $address->get_house_number_base() );
		$this->assertEquals( 'b', $address->get_house_number_addition() );
	}
}
