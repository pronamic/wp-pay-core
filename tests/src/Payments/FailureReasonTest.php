<?php
/**
 * Failure reason test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Failure reason test
 *
 * @author  Remco Tolsma
 * @version 2.2.8
 * @since   2.2.8
 */
class FailureReasonTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test failure reason object.
	 *
	 * @link https://help.mollie.com/hc/en-us/articles/115000309865-What-do-the-various-reason-codes-to-failed-direct-debits-mean-
	 */
	public function test_failure_reason() {
		$failure_reason = new FailureReason();

		$failure_reason->set_code( 'AC01' );
		$failure_reason->set_message( 'IBAN incorrect or unknown' );

		$this->assertEquals( 'AC01', $failure_reason->get_code() );
		$this->assertEquals( 'IBAN incorrect or unknown', $failure_reason->get_message() );

		// Test.
		$json_file = __DIR__ . '/../../json/failure-reason.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $failure_reason->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}
}
