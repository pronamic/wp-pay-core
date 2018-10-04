<?php
/**
 * Item test
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
 * Item test
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class ItemTest extends WP_UnitTestCase {
	/**
	 * Test setters and getters.
	 */
	public function test_setters_and_getters() {
		$item = new Item();

		$item->set_id( '1234' );
		$item->set_description( 'Lorem ipsum dolor sit amet.' );
		$item->set_quantity( 50 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::set_price' );
		$item->set_price( 39.99 );

		$this->assertEquals( '1234', $item->get_id() );
		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $item->get_description() );
		$this->assertEquals( 50, $item->get_quantity() );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::get_price' );
		$this->assertEquals( 39.99, $item->get_price() );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::get_amount' );
		$this->assertEquals( 1999.5, $item->get_amount() );
	}

	/**
	 * Test deprecated setters and getters.
	 *
	 * @link https://stackoverflow.com/questions/24959096/ignore-only-specific-warnings-in-phpunit-like-e-strict-or-e-deprecated
	 */
	public function test_deprecated_setters_and_getters() {
		$item = new Item();

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::setNumber' );
		$item->setNumber( '1234' );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::setDescription' );
		$item->setDescription( 'Lorem ipsum dolor sit amet.' );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::setQuantity' );
		$item->setQuantity( 50 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::set_price' );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::setPrice' );
		$item->setPrice( 39.99 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getNumber' );
		$this->assertEquals( '1234', $item->getNumber() );

		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $item->get_description() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getQuantity' );
		$this->assertEquals( 50, $item->getQuantity() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::get_price' );
		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getPrice' );
		$this->assertEquals( 39.99, $item->getPrice() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::get_amount' );
		$this->assertEquals( 1999.5, $item->get_amount() );
	}

	/**
	 * Test new functions.
	 */
	public function test_json() {
		$item = new Item();

		$item->set_quantity( 2 );
		$item->set_unit_price( new Money( 121, 'EUR' ) );
		$item->set_unit_tax( new Money( 21, 'EUR' ) );
		$item->set_total_amount( new Money( 242, 'EUR' ) );
		$item->set_tax_rate( 0.21 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::get_price' );
		$this->assertJsonStringEqualsJsonFile( __DIR__ . '/../../json/item.json', wp_json_encode( $item->get_json() ) );
	}
}
