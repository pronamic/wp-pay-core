<?php
/**
 * Money JSON transformer test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use InvalidArgumentException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use Pronamic\WordPress\Money\Money;
use stdClass;

/**
 * Money JSON transformer test.
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class MoneyJsonTransformerTest extends TestCase {
	/**
	 * Test JSON.
	 */
	public function test_json() {
		$object = new stdClass();

		$object->value    = 12.34;
		$object->currency = 'EUR';

		$money = MoneyJsonTransformer::from_json( $object );

		// JSON.
		$json_file = __DIR__ . '/../json/money.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $money, JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, strval( $json_string ) );
	}

	/**
	 * Test from JSON invalid.
	 */
	public function test_from_json_invalid() {
		$this->expectException( InvalidArgumentException::class );

		MoneyJsonTransformer::from_json( null );
	}
}
