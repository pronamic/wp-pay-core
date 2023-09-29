<?php
/**
 * Payment test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Banks\BankTransferDetails;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\MergeTags\MergeTag;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Payment test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.0.0
 */
class PaymentTest extends TestCase {
	/**
	 * Test construct payment object.
	 */
	public function test_construct() {
		$payment = new Payment();

		$this->assertInstanceOf( __NAMESPACE__ . '\Payment', $payment );
	}

	/**
	 * Test set and get.
	 *
	 * @dataProvider get_and_set_provider
	 *
	 * @param string $set_function      Setter function name.
	 * @param string $get_function      Getter function name.
	 * @param string $value             Expected value.
	 * @param bool   $expect_deprecated Deprecated notice expected.
	 */
	public function test_set_and_get( $set_function, $get_function, $value, $expect_deprecated = false ) {
		$payment = new Payment();

		if ( $expect_deprecated ) {
			$this->setExpectedDeprecated( $set_function );
			$this->setExpectedDeprecated( $get_function );
		}

		$payment->$set_function( $value );

		$this->assertEquals( $value, $payment->$get_function() );
	}

	/**
	 * Get and set provider.
	 *
	 * @return array
	 */
	public function get_and_set_provider() {
		$now = new DateTime();

		return [
			[ 'set_date', 'get_date', $now ],
			[ 'set_id', 'get_id', uniqid() ],
			[ 'set_config_id', 'get_config_id', 2 ],
			[ 'set_mode', 'get_mode', Gateway::MODE_LIVE ],
			[ 'set_transaction_id', 'get_transaction_id', uniqid() ],
			[ 'set_source', 'get_source', 'test' ],
			[ 'set_source_id', 'get_source_id', 5 ],
			[ 'set_status', 'get_status', 'completed' ],
			[ 'set_version', 'get_version', '5.4.2' ],
			[ 'set_lines', 'get_lines', new PaymentLines() ],
			[ 'set_total_amount', 'get_total_amount', new TaxedMoney( 89.95, 'EUR' ) ],
			[ 'set_shipping_address', 'get_shipping_address', new Address() ],
			[ 'set_shipping_amount', 'get_shipping_amount', new Money( 10, 'EUR' ) ],
			[ 'set_consumer_bank_details', 'get_consumer_bank_details', new BankAccountDetails() ],
			[ 'set_bank_transfer_recipient_details', 'get_bank_transfer_recipient_details', new BankTransferDetails() ],
			[ 'set_failure_reason', 'get_failure_reason', new FailureReason() ],
		];
	}

	/**
	 * Test get.
	 *
	 * @dataProvider get_provider
	 *
	 * @param string $property          Property name.
	 * @param string $get_function      Getter function name.
	 * @param string $value             Expected value.
	 * @param bool   $expect_deprecated Expect deprecated notice.
	 */
	public function test_get( $property, $get_function, $value, $expect_deprecated = false ) {
		$payment = new Payment();

		if ( $expect_deprecated ) {
			$this->setExpectedDeprecated( $get_function );
		}

		if ( 'customer_name' === $property ) {
			$names = explode( ' ', $value );

			$payment->set_customer( new Customer() );
			$payment->get_customer()->set_name( new ContactName() );
			$payment->get_customer()->get_name()->set_first_name( $names[0] );
			$payment->get_customer()->get_name()->set_last_name( $names[1] );
		} else {
			$payment->$property = $value;
		}

		$this->assertEquals( $value, call_user_func( [ $payment, $get_function ] ) );
	}

	/**
	 * Get provider.
	 *
	 * @return array
	 */
	public function get_provider() {
		return [
			[ 'order_id', 'get_order_id', 1234 ],
			[ 'email', 'get_email', 'john.doe@example.com' ],
		];
	}

	/**
	 * Test getting no payment.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/2.8.18/tests/tests-payment-class.php#L70-L79
	 */
	public function test_getting_no_payment() {
		$payment = new Payment();

		$this->assertNull( $payment->get_id() );
	}

	/**
	 * Test setting and getting the payment credit card.
	 */
	public function test_set_and_get_credit_card() {
		$payment = new Payment();

		$credit_card = new CreditCard();
		$credit_card->set_number( '5300000000000006' );
		$credit_card->set_expiration_month( 12 );
		$credit_card->set_expiration_year( gmdate( 'Y' ) + 5 );
		$credit_card->set_security_code( '123' );
		$credit_card->set_name( 'Pronamic' );

		$payment->set_credit_card( $credit_card );

		$this->assertEquals( $credit_card, $payment->get_credit_card() );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		/*
		 * Payment.
		 */
		$payment = new Payment();
		$payment->set_id( 1 );
		$payment->set_mode( Gateway::MODE_LIVE );
		$payment->set_total_amount( new TaxedMoney( 242, 'EUR', 42, 21 ) );

		// Name.
		$name = new ContactName();
		$name->set_first_name( 'Remco' );
		$name->set_last_name( 'Tolsma' );

		// Customer.
		$customer = new Customer();
		$customer->set_name( $name );
		$customer->set_gender( 'M' );
		$customer->set_birth_date( new DateTime( '1970-12-31T00:00:00+00:00' ) );
		$customer->set_email( 'remco@pronamic.nl' );
		$customer->set_phone( '085 40 11 580' );
		$customer->set_ip_address( '127.0.0.1' );
		$customer->set_user_agent( 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36' );
		$customer->set_language( 'nl' );
		$customer->set_locale( 'nl_NL' );

		// Address.
		$address = new Address();
		$address->set_name( $name );
		$address->set_email( 'info@pronamic.nl' );
		$address->set_company_name( 'Pronamic' );
		$address->set_coc_number( '01108446' );
		$address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$address->set_line_2( '1e etage' );
		$address->set_postal_code( '9203 KA' );
		$address->set_city( 'Drachten' );
		$address->set_region( 'Friesland' );
		$address->set_country_code( 'NL' );
		$address->set_country_name( 'Nederland' );
		$address->set_phone( '085 40 11 580' );

		// Payment lines.
		$lines = new PaymentLines();

		$line = $lines->new_line();
		$line->set_id( '1234' );
		$line->set_description( 'Lorem ipsum dolor sit amet.' );
		$line->set_quantity( 50 );
		$line->set_total_amount( new Money( 39.99, 'EUR' ) );

		$line = $lines->new_line();
		$line->set_id( 5678 );
		$line->set_description( 'Lorem ipsum dolor sit amet.' );
		$line->set_quantity( 10 );
		$line->set_total_amount( new TaxedMoney( 25, 'EUR', 5.25, 21 ) );

		$payment->set_customer( $customer );
		$payment->set_billing_address( $address );
		$payment->set_shipping_address( $address );
		$payment->set_lines( $lines );

		// Dates.
		$payment->set_expiry_date( new DateTime( '2005-05-05 00:30:00' ) );

		// Consumer bank details.
		$bank_details = new BankAccountDetails();

		$bank_details->set_bank_name( 'Rabobank' );
		$bank_details->set_name( 'John Doe' );
		$bank_details->set_account_number( '1086.34.779' );
		$bank_details->set_iban( 'NL56 RABO 0108 6347 79' );
		$bank_details->set_bic( 'RABONL2U' );
		$bank_details->set_city( 'Drachten' );
		$bank_details->set_country( 'Netherlands' );

		$payment->set_consumer_bank_details( $bank_details );

		// Bank transfer recipient details.
		$bank_transfer_recipient_details = new BankTransferDetails();

		$bank_transfer_recipient_details->set_bank_account( $bank_details );
		$bank_transfer_recipient_details->set_reference( 'ABCD-1234-EFGH-5678' );

		$payment->set_bank_transfer_recipient_details( $bank_transfer_recipient_details );

		$failure_reason = new FailureReason();

		$failure_reason->set_code( 'invalid_cvv' );
		$failure_reason->set_message( 'De veiligheidscode (CVV) is ongeldig.' );

		$payment->set_failure_reason( $failure_reason );

		// Test.
		$json_file = __DIR__ . '/../../json/payment.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $payment->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_file = __DIR__ . '/../../json/payment.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$payment = Payment::from_json( $json_data );

		$json_string = wp_json_encode( $payment->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test format string.
	 */
	public function test_format_string() {
		\add_filter(
			'pronamic_pay_merge_tags',
			function( $merge_tags, $controller ) {
				$merge_tags[] = new MergeTag(
					'zero',
					'Zero',
					function() {
						return '0';
					}
				);

				return $merge_tags;
			},
			10,
			2
		);

		$payment = new Payment();
		$payment->set_id( 19092023 );
		$payment->order_id = 'Order 1234567890';

		$value = '{payment_id} - {order_id} - {zero}';

		$this->assertEquals( '19092023 - Order 1234567890 - 0', $payment->format_string( $value ) );

		// Payment lines.
		$lines = new PaymentLines();

		$line = $lines->new_line();
		$line->set_name( 'Product A' );

		$line = $lines->new_line();
		$line->set_name( 'Product B' );

		$payment->set_lines( $lines );

		$value = '{payment_id} - {payment_lines_name}';

		$this->assertEquals( '19092023 - Product A, Product B', $payment->format_string( $value ) );
	}
}
