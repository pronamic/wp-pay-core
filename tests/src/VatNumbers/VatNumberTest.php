<?php
/**
 * VAT Number test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT Number test
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.1.6
 */
class VatNumberTest extends \WP_UnitTestCase {
	/**
	 * Test VAT number.
	 */
	public function test_vat_number() {
		$vat_number = new VatNumber( 'NL999999999B01' );

		$this->assertEquals( 'NL999999999B01', $vat_number->get_value() );
		$this->assertEquals( 'NL', $vat_number->get_2_digit_prefix() );
	}

	/**
	 * Test normalize.
	 */
	public function test_normalize() {
		$vat_number = new VatNumber( 'NL9999.99.999.B01' );

		$this->assertEquals( 'NL999999999B01', VatNumber::normalize( $vat_number->get_value() ) );
		$this->assertEquals( 'NL', $vat_number->get_2_digit_prefix() );
		$this->assertEquals( 'NL999999999B01', $vat_number->normalized() );
		$this->assertEquals( '999999999B01', $vat_number->normalized_without_prefix() );
	}

	/**
	 * Test normalize whitespace.
	 */
	public function test_normalize_whitespace() {
		$vat_number = new VatNumber( ' NL1234.56.789.B01 ' );

		$this->assertEquals( 'NL123456789B01', VatNumber::normalize( $vat_number->get_value() ) );
		$this->assertEquals( 'NL', $vat_number->get_2_digit_prefix() );
	}
}
