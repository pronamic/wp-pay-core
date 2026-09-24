<?php
/**
 * Payment lines test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\CurrencyMismatchException;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Number\Number;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Payment lines test
 *
 * @version 2.1.0
 * @since   1.0.0
 */
class PaymentLinesTest extends TestCase {
	/**
	 * Lines.
	 *
	 * @var PaymentLines
	 */
	private $lines;

	/**
	 * Setup.
	 */
	public function set_up() {
		parent::set_up();

		$this->lines = new PaymentLines();

		$line_a = new PaymentLine();

		$line_a->set_id( '1234' );
		$line_a->set_description( 'Lorem ipsum dolor sit amet.' );
		$line_a->set_quantity( new Number( 50 ) );
		$line_a->set_total_amount( new TaxedMoney( 39.99, 'EUR' ) );

		$this->lines->add_line( $line_a );

		$line_b = new PaymentLine();

		$line_b->set_id( '5678' );
		$line_b->set_description( 'Lorem ipsum dolor sit amet.' );
		$line_b->set_quantity( new Number( 10 ) );
		$line_b->set_total_amount( new TaxedMoney( 25, 'EUR' ) );

		$this->lines->add_line( $line_b );

		$line_c = new PaymentLine();

		$this->lines->add_line( $line_c );

		$line_d = new PaymentLine();

		$line_d->set_id( null );
		$line_d->set_description( null );
		$line_d->set_quantity( null );

		$this->lines->add_line( $line_d );
	}

	/**
	 * Test count.
	 */
	public function test_count() {
		$this->assertCount( 4, $this->lines );
	}

	/**
	 * Test amount with a non-default currency and tax.
	 */
	public function test_amount_with_non_default_currency_and_tax() {
		$lines = new PaymentLines();

		// Non-default currency.
		$line = new PaymentLine();

		$line->set_total_amount( new TaxedMoney( 10, 'USD', 2 ) );

		$lines->add_line( $line );

		// Second line with tax.
		$taxed_line = new PaymentLine();

		$taxed_line->set_total_amount( new TaxedMoney( 5, 'USD', 1 ) );

		$lines->add_line( $taxed_line );

		// Zero line without tax.
		$zero_line = new PaymentLine();

		$zero_line->set_total_amount( new TaxedMoney( 0, 'USD' ) );

		$lines->add_line( $zero_line );

		$amount = $lines->get_amount();

		$this->assertSame( '15', $amount->get_value() );
		$this->assertSame( '3', $amount->get_tax_value() );
		$this->assertSame( 'USD', $amount->get_currency()->get_alphabetic_code() );
	}

	/**
	 * Test amount with different currencies.
	 */
	public function test_amount_with_different_currencies() {
		$lines = new PaymentLines();

		$eur_line = new PaymentLine();
		$eur_line->set_total_amount( new TaxedMoney( 10, 'EUR' ) );

		$lines->add_line( $eur_line );

		$usd_line = new PaymentLine();
		$usd_line->set_total_amount( new TaxedMoney( 10, 'USD' ) );

		$lines->add_line( $usd_line );

		$this->expectException( CurrencyMismatchException::class );

		$lines->get_amount();
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
