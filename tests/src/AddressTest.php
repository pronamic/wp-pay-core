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
	 * Address.
	 *
	 * @var Address
	 */
	private $address;

	/**
	 * Name.
	 *
	 * @var Address
	 */
	private $name;

	/**
	 * Setup.
	 */
	public function setUp() {
		$this->address = new Address();

		$this->name = new ContactName();
		$this->name->set_first_name( 'Remco' );
		$this->name->set_last_name( 'Tolsma' );

		$this->address->set_company_name( 'Pronamic' );
		$this->address->set_kvk_number( '01108446' );
		$this->address->set_name( $this->name );
		$this->address->set_email( 'info@pronamic.nl' );
		$this->address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$this->address->set_line_2( '1e etage' );
		$this->address->set_street_name( 'Burgemeester Wuiteweg' );
		$this->address->set_house_number( '39b' );
		$this->address->set_house_number_base( '39' );
		$this->address->set_house_number_addition( 'b' );
		$this->address->set_postal_code( '9203 KA' );
		$this->address->set_city( 'Drachten' );
		$this->address->set_region( 'Friesland' );
		$this->address->set_country_code( 'NL' );
		$this->address->set_country_name( 'Nederland' );
		$this->address->set_phone( '085 40 11 580' );
	}

	/**
	 * Test address setters and getters.
	 */
	public function test_setters_and_getters() {
		$this->assertInstanceOf( __NAMESPACE__ . '\Address', $this->address );

		$this->assertEquals( 'Pronamic', $this->address->get_company_name() );
		$this->assertEquals( '01108446', $this->address->get_kvk_number() );
		$this->assertEquals( $this->name, $this->address->get_name() );
		$this->assertEquals( 'info@pronamic.nl', $this->address->get_email() );
		$this->assertEquals( 'Burgemeester Wuiteweg 39b', $this->address->get_line_1() );
		$this->assertEquals( '1e etage', $this->address->get_line_2() );
		$this->assertEquals( 'Burgemeester Wuiteweg', $this->address->get_street_name() );
		$this->assertEquals( '39b', $this->address->get_house_number() );
		$this->assertEquals( '39', $this->address->get_house_number_base() );
		$this->assertEquals( 'b', $this->address->get_house_number_addition() );
		$this->assertEquals( '9203 KA', $this->address->get_postal_code() );
		$this->assertEquals( 'Drachten', $this->address->get_city() );
		$this->assertEquals( 'Friesland', $this->address->get_region() );
		$this->assertEquals( 'NL', $this->address->get_country_code() );
		$this->assertEquals( 'Nederland', $this->address->get_country_name() );
		$this->assertEquals( '085 40 11 580', $this->address->get_phone() );

		$string = '';

		$string .= 'Pronamic' . PHP_EOL;
		$string .= 'Remco Tolsma' . PHP_EOL;
		$string .= 'Burgemeester Wuiteweg 39b' . PHP_EOL;
		$string .= '1e etage' . PHP_EOL;
		$string .= '9203 KA Drachten' . PHP_EOL;
		$string .= 'NL' . PHP_EOL;
		$string .= '085 40 11 580' . PHP_EOL;
		$string .= 'info@pronamic.nl';

		$this->assertEquals( $string, (string) $this->address );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../json/address.json', wp_json_encode( $this->address->get_json() ) );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_string = file_get_contents( __DIR__ . '/../json/address.json', true );

		$json = json_decode( $json_string );

		$address = Address::from_json( $json );

		$this->assertInstanceOf( __NAMESPACE__ . '\Address', $address );
	}
}
