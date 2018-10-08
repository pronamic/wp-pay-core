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
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::set_price' );
		$line_a->set_price( 39.99 );

		$this->lines->add_line( $line_a );

		$line_b = new PaymentLine();

		$line_b->set_id( '5678' );
		$line_b->set_description( 'Lorem ipsum dolor sit amet.' );
		$line_b->set_quantity( 10 );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::set_price' );
		$line_b->set_price( 25 );

		$this->lines->add_line( $line_b );

		$line_c = new PaymentLine();

		$this->lines->add_line( $line_c );

		$line_d = new PaymentLine();

		$line_d->set_id( null );
		$line_d->set_description( null );
		$line_d->set_quantity( null );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::set_price' );
		$line_d->set_price( null );

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

		$expected .= '1234 Lorem ipsum dolor sit amet. 50 39.99 1999.50' . PHP_EOL;
		$expected .= '5678 Lorem ipsum dolor sit amet. 10 25.00 250.00' . PHP_EOL;
		$expected .= '  1 0.00 0.00' . PHP_EOL;
		$expected .= '  0 0.00 0.00';

		$this->assertEquals( $expected, $string );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		$json_data   = $this->lines->get_json();
		$json_string = wp_json_encode( $json_data );

		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../../json/payment-lines.json', $json_string );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_string = file_get_contents( __DIR__ . '/../../json/payment-lines.json', true );

		$json = json_decode( $json_string );

		$lines = Items::from_json( $json );

		$this->assertCount( 4, $lines );

		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../../json/payment-lines.json', wp_json_encode( $lines->get_json() ) );
	}
}
