<?php
/**
 * Payment test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Customer;
use WP_UnitTestCase;

/**
 * Payment test
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class PaymentTest extends WP_UnitTestCase {
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

		return array(
			array( 'set_date', 'get_date', $now ),
			array( 'set_id', 'get_id', uniqid() ),
			array( 'set_config_id', 'get_config_id', 2 ),
			array( 'set_mode', 'get_mode', Gateway::MODE_LIVE ),
			array( 'set_transaction_id', 'get_transaction_id', uniqid() ),
			array( 'set_start_date', 'get_start_date', $now ),
			array( 'set_end_date', 'get_end_date', $now ),
			array( 'set_source', 'get_source', 'test' ),
			array( 'set_source_id', 'get_source_id', 5 ),
			array( 'set_status', 'get_status', 'completed' ),
			array( 'set_version', 'get_version', '5.4.2' ),
			array( 'set_lines', 'get_lines', new PaymentLines() ),
			array( 'set_total_amount', 'get_total_amount', new TaxedMoney( 89.95, 'EUR' ) ),
			array( 'set_shipping_address', 'get_shipping_address', new Address() ),
			array( 'set_shipping_amount', 'get_shipping_amount', new Money( 10, 'EUR' ) ),
			array( 'set_ga_tracked', 'get_ga_tracked', true ),

			// Deprecated.
			array( 'set_amount', 'get_amount', new TaxedMoney( 89.95, 'EUR' ), true ),
		);
	}

	/**
	 * Test set.
	 *
	 * @dataProvider set_provider
	 *
	 * @param string $set_function Setter function name.
	 * @param string $property     Property name.
	 * @param string $value        Expected value.
	 */
	public function test_set( $set_function, $property, $value ) {
		$payment = new Payment();

		$payment->$set_function( $value );

		$this->assertEquals( $value, $payment->$property );
	}

	/**
	 * Set provider.
	 *
	 * @return array
	 */
	public function set_provider() {
		return array(
			array( 'set_consumer_name', 'consumer_name', 'John Doe' ),
			array( 'set_consumer_account_number', 'consumer_account_number', '1086.34.779' ),
			array( 'set_consumer_iban', 'consumer_iban', 'NL56 RABO 0108 6347 79' ),
			array( 'set_consumer_bic', 'consumer_bic', 'RABONL2U' ),
			array( 'set_consumer_city', 'consumer_city', 'Drachten' ),
		);
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

		$this->assertEquals( $value, call_user_func( array( $payment, $get_function ) ) );
	}

	/**
	 * Get provider.
	 *
	 * @return array
	 */
	public function get_provider() {
		return array(
			array( 'order_id', 'get_order_id', 1234 ),
			array( 'method', 'get_method', 'ideal' ),
			array( 'issuer', 'get_issuer', 'ideal_KNABNL2H' ),
			array( 'description', 'get_description', 'Lorem ipsum dolor sit amet, consectetur.' ),
			array( 'email', 'get_email', 'john.doe@example.com' ),
			array( 'analytics_client_id', 'get_analytics_client_id', 'GA1.2.1234567890.1234567890' ),
			array( 'entrance_code', 'get_entrance_code', uniqid() ),

			// Deprecated.
			array( 'language', 'get_language', 'nl', true ),
			array( 'locale', 'get_locale', 'nl_NL', true ),
			array( 'first_name', 'get_first_name', 'John', true ),
			array( 'last_name', 'get_last_name', 'Doe', true ),
			array( 'customer_name', 'get_customer_name', 'John Doe', true ),
			array( 'address', 'get_address', 'Burgemeester Wuiteweg 39b', true ),
			array( 'city', 'get_city', 'Drachten', true ),
			array( 'zip', 'get_zip', '9203 KA', true ),
			array( 'country', 'get_country', 'NL', true ),
			array( 'telephone_number', 'get_telephone_number', '1234567890', true ),
		);
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
		$credit_card->set_expiration_year( date( 'Y' ) + 5 );
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
		$payment->set_ga_tracked( true );
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
		$line->set_total_amount( new TaxedMoney( 39.99, 'EUR' ) );

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
		$payment->set_start_date( new DateTime( '2005-05-05' ) );
		$payment->set_end_date( new DateTime( '2100-05-05' ) );

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
}
