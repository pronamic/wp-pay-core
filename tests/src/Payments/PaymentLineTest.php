<?php
/**
 * Payment line test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;
use WP_UnitTestCase;

/**
 * Payment line test
 *
 * @author Remco Tolsma
 * @version 1.0
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
		$line = new PaymentLine();

		$line->set_quantity( 2 );
		$line->set_unit_price_excluding_tax( new Money( 100, 'EUR' ) );
		$line->set_unit_price_including_tax( new Money( 121, 'EUR' ) );
		$line->set_tax_percentage( 21 );
		$line->set_discount_amount( new Money( 21, 'EUR' ) );
		$line->set_total_amount_excluding_tax( new Money( 200, 'EUR' ) );
		$line->set_total_amount_including_tax( new Money( 242, 'EUR' ) );

		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../../json/payment-line.json', wp_json_encode( $line->get_json() ) );
	}
}
