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

		$name = new ContactName();
		$name->set_first_name( 'Remco' );
		$name->set_last_name( 'Tolsma' );

		$address->set_company_name( 'Pronamic' );
		$address->set_company_coc( '01108446' );
		$address->set_name( $name );
		$address->set_email( 'info@pronamic.nl' );
		$address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$address->set_line_2( '1e etage' );
		$address->set_street_name( 'Burgemeester Wuiteweg' );
		$address->set_house_number( '39b' );
		$address->set_house_number_base( '39' );
		$address->set_house_number_addition( 'b' );
		$address->set_postal_code( '9203 KA' );
		$address->set_city( 'Drachten' );
		$address->set_region( 'Friesland' );
		$address->set_country_code( 'NL' );
		$address->set_phone( '085 40 11 580' );

		$this->assertEquals( 'Pronamic', $address->get_company_name() );
		$this->assertEquals( '01108446', $address->get_company_coc() );
		$this->assertEquals( $name, $address->get_name() );
		$this->assertEquals( 'info@pronamic.nl', $address->get_email() );
		$this->assertEquals( 'Burgemeester Wuiteweg 39b', $address->get_line_1() );
		$this->assertEquals( '1e etage', $address->get_line_2() );
		$this->assertEquals( 'Burgemeester Wuiteweg', $address->get_street_name() );
		$this->assertEquals( '39b', $address->get_house_number() );
		$this->assertEquals( '39', $address->get_house_number_base() );
		$this->assertEquals( 'b', $address->get_house_number_addition() );
		$this->assertEquals( '9203 KA', $address->get_postal_code() );
		$this->assertEquals( 'Drachten', $address->get_city() );
		$this->assertEquals( 'Friesland', $address->get_region() );
		$this->assertEquals( 'NL', $address->get_country_code() );
		$this->assertEquals( '085 40 11 580', $address->get_phone() );

		$string = '';

		$string .= 'Pronamic' . PHP_EOL;
		$string .= 'Remco Tolsma' . PHP_EOL;
		$string .= 'Burgemeester Wuiteweg 39b' . PHP_EOL;
		$string .= '1e etage' . PHP_EOL;
		$string .= '9203 KA Drachten' . PHP_EOL;
		$string .= 'NL' . PHP_EOL;
		$string .= '085 40 11 580' . PHP_EOL;
		$string .= 'info@pronamic.nl';

		$this->assertEquals( $string, (string) $address );
	}
}
