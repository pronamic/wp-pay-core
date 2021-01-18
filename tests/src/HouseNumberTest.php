<?php
/**
 * House number test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * House number test
 *
 * @link https://github.com/woocommerce/woocommerce/blob/3.5.5/i18n/states/US.php
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.1.6
 */
class HouseNumberTest extends WP_UnitTestCase {
	/**
	 * Test house number.
	 */
	public function test_country() {
		$house_number = new HouseNumber();

		$house_number->set_value( '39b' );
		$house_number->set_base( '39' );
		$house_number->set_addition( 'b' );

		$this->assertEquals( '39b', $house_number->get_value() );
		$this->assertEquals( '39', $house_number->get_base() );
		$this->assertEquals( 'b', $house_number->get_addition() );

		$this->assertEquals( '39b', (string) $house_number );
	}
}
