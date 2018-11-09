<?php
/**
 * Payment lines test
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
 * Payment lines test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class PaymentLinesTest extends WP_UnitTestCase {
	/**
	 * Lines.
	 *
	 * @var PaymentLines
	 */
	private $lines;

	/**
	 * Setup.
	 */
	public function setUp() {
		$this->lines = new PaymentLines();

		$line_a = new PaymentLine();

		$line_a->set_id( '1234' );
		$line_a->set_description( 'Lorem ipsum dolor sit amet.' );
		$line_a->set_quantity( 50 );
		$line_a->set_total_amount( new Money( 39.99, 'EUR' ) );

		$this->lines->add_line( $line_a );

		$line_b = new PaymentLine();

		$line_b->set_id( '5678' );
		$line_b->set_description( 'Lorem ipsum dolor sit amet.' );
		$line_b->set_quantity( 10 );
		$line_b->set_total_amount( new Money( 25, 'EUR' ) );

		$this->lines->add_line( $line_b );

		$line_c = new PaymentLine();

		$this->lines->add_line( $line_c );

		$line_d = new PaymentLine();

		$line_d->set_id( null );
		$line_d->set_description( null );
		$line_d->set_quantity( null );
		$line_d->set_total_amount( null );

		$this->lines->add_line( $line_d );
	}

	/**
	 * Test count.
	 */
	public function test_count() {
		$this->assertCount( 4, $this->lines );
	}

	/**
	 * Test to string.
	 */
	public function test_to_string() {
		$string = (string) $this->lines;

		$expected = '';

		$expected .= '1234 - Lorem ipsum dolor sit amet. - 50' . PHP_EOL;
		$expected .= '5678 - Lorem ipsum dolor sit amet. - 10' . PHP_EOL;
		$expected .= '' . PHP_EOL;
		$expected .= '';

		$this->assertEquals( $expected, $string );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		$json_file = __DIR__ . '/../../json/payment-lines.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $this->lines->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_file = __DIR__ . '/../../json/payment-lines.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$lines = PaymentLines::from_json( $json_data );

		$this->assertCount( 4, $lines );

		$json_string = wp_json_encode( $lines->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}
}
