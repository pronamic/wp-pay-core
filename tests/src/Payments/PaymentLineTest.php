<?php
/**
 * Payment line test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use WP_UnitTestCase;

/**
 * Payment line test
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class PaymentLineTest extends WP_UnitTestCase {
	/**
	 * Test setters and getters.
	 */
	public function test_setters_and_getters() {
		$line = new PaymentLine();

		$line->set_id( '1234' );
		$line->set_description( 'Lorem ipsum dolor sit amet.' );
		$line->set_quantity( 50 );

		$this->assertEquals( '1234', $line->get_id() );
		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $line->get_description() );
		$this->assertEquals( 50, $line->get_quantity() );
	}

	/**
	 * Test new functions.
	 */
	public function test_json() {
		// Line.
		$line = new PaymentLine();

		$line->set_quantity( 2 );
		$line->set_unit_price( new TaxedMoney( 121, 'EUR', 21 ) );
		$line->set_discount_amount( new Money( 21, 'EUR' ) );
		$line->set_total_amount( new TaxedMoney( 242, 'EUR', null, 21 ) );

		// JSON.
		$json_file = __DIR__ . '/../../json/payment-line.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $line->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}
}
