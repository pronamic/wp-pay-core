<?php
/**
 * Address test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * Address test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class AddressTest extends WP_UnitTestCase {
	/**
	 * Test address setters and getters.
	 */
	public function test_setters_and_getters() {
		$address = new Address();

		$this->assertInstanceOf( __NAMESPACE__ . '\Address', $address );

		$address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$address->set_line_2( '1e etage' );
		$address->set_street_name( 'Burgemeester Wuiteweg' );
		$address->set_house_number( '39b' );
		$address->set_house_number_base( '39' );
		$address->set_house_number_addition( 'b' );
		$address->set_postal_code( '9203 KA' );
		$address->set_city( 'Drachten' );
		$address->set_country_code( 'NL' );

		$this->assertEquals( 'Burgemeester Wuiteweg 39b', $address->get_line_1() );
		$this->assertEquals( '1e etage', $address->get_line_2() );
		$this->assertEquals( 'Burgemeester Wuiteweg', $address->get_street_name() );
		$this->assertEquals( '39b', $address->get_house_number() );
		$this->assertEquals( '39', $address->get_house_number_base() );
		$this->assertEquals( 'b', $address->get_house_number_addition() );
		$this->assertEquals( '9203 KA', $address->get_postal_code() );
		$this->assertEquals( 'Drachten', $address->get_city() );
		$this->assertEquals( 'NL', $address->get_country_code() );

		$string = '';

		$string .= 'Burgemeester Wuiteweg 39b' . PHP_EOL;
		$string .= '1e etage' . PHP_EOL;
		$string .= '9203 KA Drachten' . PHP_EOL;
		$string .= 'NL';

		$this->assertEquals( $string, (string) $address );
	}
}
