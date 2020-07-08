<?php
/**
 * VAT Number validity test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT Number validity test
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.1.6
 */
class VatNumberValidityTest extends \WP_UnitTestCase {
	/**
	 * Test VAT number validity.
	 */
	public function test_vat_number_validity() {
		$vat_number = new VatNumber( 'NL999999999B01' );

		$request_date = new \DateTimeImmutable();

		$validity = new VatNumberValidity( $vat_number, $request_date, true );
		$validity->set_name( 'Pronamic' );
		$validity->set_address( "BURGEMEESTER WUITEWEG 00039 B\r\n9203KA DRACHTEN" );
		$validity->set_service( VatNumberValidationService::VIES );

		$this->assertEquals( $vat_number, $validity->get_vat_number() );
		$this->assertEquals( $request_date, $validity->get_request_date() );
		$this->assertTrue( $validity->is_valid() );
		$this->assertEquals( 'Pronamic', $validity->get_name() );
		$this->assertEquals( "BURGEMEESTER WUITEWEG 00039 B\r\n9203KA DRACHTEN", $validity->get_address() );
		$this->assertEquals( VatNumberValidationService::VIES, $validity->get_service() );
	}
}
