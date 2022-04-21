<?php
/**
 * Credit card test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Payment test
 *
 * @author Remco Tolsma
 * @version 2.2.6
 */
class CreditCardTest extends TestCase {
	/**
	 * Test construct payment object.
	 */
	public function test_construct() {
		$credit_card = new CreditCard();

		$this->assertInstanceOf( __NAMESPACE__ . '\CreditCard', $credit_card );
	}

	/**
	 * Test setting and getting the credit card number.
	 */
	public function test_set_and_get_number() {
		$credit_card = new CreditCard();

		$number = '5300000000000006';

		$credit_card->set_number( $number );

		$this->assertEquals( $number, $credit_card->get_number() );
	}

	/**
	 * Test setting and getting the credit card expiration month.
	 */
	public function test_set_and_get_expiration_month() {
		$credit_card = new CreditCard();

		$month = 12;

		$credit_card->set_expiration_month( $month );

		$this->assertEquals( $month, $credit_card->get_expiration_month() );
	}

	/**
	 * Test setting and getting the credit card expiration year.
	 */
	public function test_set_and_get_expiration_year() {
		$credit_card = new CreditCard();

		$year = gmdate( 'Y' ) + 5;

		$credit_card->set_expiration_year( $year );

		$this->assertEquals( $year, $credit_card->get_expiration_year() );
	}

	/**
	 * Test getting the expiration date.
	 *
	 * @dataProvider expiration_dates_provider
	 *
	 * @param string|int|null $year          Year value.
	 * @param string|int|null $month         Month value.
	 * @param \DateTime|null  $expected_date Expected date.
	 */
	public function test_get_expiration_date( $year, $month, $expected_date ) {
		$credit_card = new CreditCard();

		$credit_card->set_expiration_year( $year );
		$credit_card->set_expiration_month( $month );

		$date = $credit_card->get_expiration_date();

		$this->assertEquals( $expected_date, $date );
	}

	/**
	 * Expiration dates provider.
	 *
	 * @return array
	 */
	public function expiration_dates_provider() {
		return [
			[ '2018', '12', new \DateTime( 'first day of December 2018' ) ],
			[ 2018, 12, new \DateTime( 'first day of December 2018' ) ],
			[ '2018', null, null ],
			[ null, null, null ],
			[ false, false, null ],
		];
	}

	/**
	 * Test setting and getting the credit card security code.
	 */
	public function test_set_and_get_security_code() {
		$credit_card = new CreditCard();

		$code = '123';

		$credit_card->set_security_code( $code );

		$this->assertEquals( $code, $credit_card->get_security_code() );
	}

	/**
	 * Test setting and getting the credit card name.
	 */
	public function test_set_and_get_name() {
		$credit_card = new CreditCard();

		$name = 'Pronamic';

		$credit_card->set_name( $name );

		$this->assertEquals( $name, $credit_card->get_name() );
	}
}
