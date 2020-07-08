<?php
/**
 * Customer test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\VatNumbers\VatNumber;
use Pronamic\WordPress\Pay\VatNumbers\VatNumberValidity;
use Pronamic\WordPress\Pay\VatNumbers\VatNumberValidationService;
use WP_UnitTestCase;

/**
 * Customer test
 *
 * @author Remco Tolsma
 * @version 2.4.0
 */
class CustomerTest extends WP_UnitTestCase {
	/**
	 * Customer.
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Name.
	 *
	 * @var ContactName
	 */
	private $name;

	/**
	 * Setup.
	 */
	public function setUp() {
		$this->customer = new Customer();

		$this->name = new ContactName();
		$this->name->set_first_name( 'Remco' );
		$this->name->set_last_name( 'Tolsma' );

		$this->customer->set_name( $this->name );
		$this->customer->set_company_name( 'Pronamic' );
		$this->customer->set_gender( 'M' );
		$this->customer->set_birth_date( new DateTime( '31-12-1970' ) );
		$this->customer->set_email( 'remco@pronamic.nl' );
		$this->customer->set_phone( '085 40 11 580' );
		$this->customer->set_ip_address( '127.0.0.1' );
		$this->customer->set_user_agent( 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36' );
		$this->customer->set_language( 'nl' );
		$this->customer->set_locale( 'nl_NL' );
		$this->customer->set_user_id( 2 );
	}

	/**
	 * Test address setters and getters.
	 */
	public function test_setters_and_getters() {
		$this->assertInstanceOf( __NAMESPACE__ . '\Customer', $this->customer );

		$this->assertEquals( $this->name, $this->customer->get_name() );
		$this->assertEquals( 'Pronamic', $this->customer->get_company_name() );
		$this->assertEquals( 'M', $this->customer->get_gender() );
		$this->assertEquals( '31-12-1970', $this->customer->get_birth_date()->format( 'd-m-Y' ) );
		$this->assertEquals( 'remco@pronamic.nl', $this->customer->get_email() );
		$this->assertEquals( '085 40 11 580', $this->customer->get_phone() );
		$this->assertEquals( '127.0.0.1', $this->customer->get_ip_address() );
		$this->assertEquals( 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36', $this->customer->get_user_agent() );
		$this->assertEquals( 'nl', $this->customer->get_language() );
		$this->assertEquals( 'nl_NL', $this->customer->get_locale() );
		$this->assertEquals( 2, $this->customer->get_user_id() );

		$string = '';

		$string .= 'Remco Tolsma' . PHP_EOL;
		$string .= 'remco@pronamic.nl' . PHP_EOL;
		$string .= '085 40 11 580' . PHP_EOL;
		$string .= 'M' . PHP_EOL;
		$string .= '1970-12-31T00:00:00+00:00' . PHP_EOL;
		$string .= 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36' . PHP_EOL;
		$string .= '127.0.0.1' . PHP_EOL;
		$string .= 'nl' . PHP_EOL;
		$string .= 'nl_NL';

		$this->assertEquals( $string, (string) $this->customer );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		$json_file = __DIR__ . '/../json/customer.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $this->customer->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_file = __DIR__ . '/../json/customer.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$customer = Customer::from_json( $json_data );

		$json_string = wp_json_encode( $customer->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test VAT number.
	 */
	public function test_vat_number() {
		$customer = new Customer();

		$vat_number = new VatNumber( 'NL999999999B01' );

		$request_date = new \DateTimeImmutable( '2020-06-04 14:00:00', new \DateTimeZone( 'UTC' ) );

		$validity = new VatNumberValidity( $vat_number, $request_date, true );
		$validity->set_name( 'Pronamic' );
		$validity->set_address( "BURGEMEESTER WUITEWEG 00039 B\r\n9203KA DRACHTEN" );
		$validity->set_service( VatNumberValidationService::VIES );

		$vat_number->set_validity( $validity );

		$customer->set_vat_number( $vat_number );

		$this->assertEquals( $vat_number, $customer->get_vat_number() );
		$this->assertEquals( $validity, $customer->get_vat_number()->get_validity() );
	}
}
