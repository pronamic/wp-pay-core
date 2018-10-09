<?php
/**
 * Payment line test
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
 * Payment line test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class PaymentLineTest extends WP_UnitTestCase {
	/**
	 * Test setters and getters.
	 */
	public function test_setters_and_getters() {
		$line = new PaymentLine();

		$line->set_id( '1234' );
		$line->set_description( 'Lorem ipsum dolor sit amet.' );
		$line->set_quantity( 50 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::set_price' );
		$line->set_price( 39.99 );

		$this->assertEquals( '1234', $line->get_id() );
		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $line->get_description() );
		$this->assertEquals( 50, $line->get_quantity() );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::get_price' );
		$this->assertEquals( 39.99, $line->get_price() );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::get_amount' );
		$this->assertEquals( 1999.5, $line->get_amount() );
	}

	/**
	 * Test deprecated setters and getters.
	 *
	 * @link https://stackoverflow.com/questions/24959096/ignore-only-specific-warnings-in-phpunit-like-e-strict-or-e-deprecated
	 */
	public function test_deprecated_setters_and_getters() {
		$line = new PaymentLine();

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::setNumber' );
		$line->setNumber( '1234' );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::setDescription' );
		$line->setDescription( 'Lorem ipsum dolor sit amet.' );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::setQuantity' );
		$line->setQuantity( 50 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::set_price' );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::setPrice' );
		$line->setPrice( 39.99 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::getNumber' );
		$this->assertEquals( '1234', $line->getNumber() );

		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $line->get_description() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::getQuantity' );
		$this->assertEquals( 50, $line->getQuantity() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::get_price' );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::getPrice' );
		$this->assertEquals( 39.99, $line->getPrice() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::get_amount' );
		$this->assertEquals( 1999.5, $line->get_amount() );
	}

	/**
	 * Test new functions.
	 */
	public function test_json() {
		$line = new PaymentLine();

		$line->set_quantity( 2 );
		$line->set_unit_price( new Money( 121, 'EUR' ) );
		$line->set_tax_percentage( 0.21 );
		$line->set_discount_amount( new Money( 21, 'EUR' ) );
		$line->set_total_amount( new Money( 242, 'EUR' ) );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\PaymentLine::get_price' );
		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../../json/payment-line.json', wp_json_encode( $line->get_json() ) );
	}
}
