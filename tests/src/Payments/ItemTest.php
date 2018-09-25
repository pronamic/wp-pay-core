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
		$item->set_price( 39.99 );

		$this->assertEquals( '1234', $item->get_id() );
		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $item->get_description() );
		$this->assertEquals( 50, $item->get_quantity() );
		$this->assertEquals( 39.99, $item->get_price() );
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

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::setPrice' );
		$item->setPrice( 39.99 );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getNumber' );
		$this->assertEquals( '1234', $item->getNumber() );

		$this->assertEquals( 'Lorem ipsum dolor sit amet.', $item->get_description() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getQuantity' );
		$this->assertEquals( 50, $item->getQuantity() );

		$this->setExpectedDeprecated( __NAMESPACE__ . '\Item::getPrice' );
		$this->assertEquals( 39.99, $item->getPrice() );

		$this->assertEquals( 1999.5, $item->get_amount() );
	}
}
