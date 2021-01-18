<?php
/**
 * Bank transfer details test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Banks;

use PHPUnit\Framework\TestCase;

/**
 * Bank transfer details test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class BankTransferDetailsTest extends TestCase {
	/**
	 * Test bank account details test.
	 */
	public function test_bank_account_details() {
		$bank_transfer_details = new BankTransferDetails();

		$bank_account_details = new BankAccountDetails();

		$bank_account_details->set_name( 'Pronamic' );
		$bank_account_details->set_account_number( '1086.34.779' );
		$bank_account_details->set_iban( 'NL56 RABO 0108 6347 79' );
		$bank_account_details->set_bic( 'RABONL2U' );
		$bank_account_details->set_city( 'Drachten' );

		$bank_transfer_details->set_bank_account( $bank_account_details );
		$bank_transfer_details->set_reference( 'AB12-3456-7890-1234' );

		$this->assertEquals( $bank_account_details, $bank_transfer_details->get_bank_account() );
		$this->assertEquals( 'AB12-3456-7890-1234', $bank_transfer_details->get_reference() );

		// Test.
		$json_file = __DIR__ . '/../../json/bank-transfer-details.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $bank_transfer_details->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}
}
