<?php
/**
 * Country test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * Country test
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.1.6
 */
class CountryTest extends WP_UnitTestCase {
	/**
	 * Test country.
	 */
	public function test_country() {
		$country = new Country();

		$country->set_code( 'NL' );
		$country->set_name( 'Nederland' );

		$this->assertEquals( 'NL', $country->get_code() );
		$this->assertEquals( 'Nederland', $country->get_name() );

		$this->assertEquals( 'NL - Nederland', (string) $country );
	}
}
