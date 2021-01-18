<?php
/**
 * Bank account details test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Banks;

use PHPUnit\Framework\TestCase;

/**
 * Bank account details test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.2.6
 */
class BankAccountDetailsTest extends TestCase {
	/**
	 * Test bank account details test.
	 */
	public function test_bank_account_details() {
		$bank_account_details = new BankAccountDetails();

		$bank_account_details->set_name( 'Pronamic' );
		$bank_account_details->set_account_number( '1086.34.779' );
		$bank_account_details->set_iban( 'NL56 RABO 0108 6347 79' );
		$bank_account_details->set_bic( 'RABONL2U' );
		$bank_account_details->set_city( 'Drachten' );
		$bank_account_details->set_country( 'Netherlands' );
		$bank_account_details->set_bank_name( 'Rabobank' );

		$this->assertEquals( 'Pronamic', $bank_account_details->get_name() );
		$this->assertEquals( '1086.34.779', $bank_account_details->get_account_number() );
		$this->assertEquals( 'NL56 RABO 0108 6347 79', $bank_account_details->get_iban() );
		$this->assertEquals( 'RABONL2U', $bank_account_details->get_bic() );
		$this->assertEquals( 'Drachten', $bank_account_details->get_city() );
		$this->assertEquals( 'Netherlands', $bank_account_details->get_country() );
		$this->assertEquals( 'Rabobank', $bank_account_details->get_bank_name() );

		// Test.
		$json_file = __DIR__ . '/../../json/bank-account-details.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $bank_account_details->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}
}
