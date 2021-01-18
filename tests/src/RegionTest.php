<?php
/**
 * Region test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use WP_UnitTestCase;

/**
 * Region test
 *
 * @link https://github.com/woocommerce/woocommerce/blob/3.5.5/i18n/states/US.php
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.1.6
 */
class RegionTest extends WP_UnitTestCase {
	/**
	 * Test country.
	 */
	public function test_country() {
		$region = new Region();

		$region->set_code( 'AL' );
		$region->set_name( 'Alabama' );

		$this->assertEquals( 'AL', $region->get_code() );
		$this->assertEquals( 'Alabama', $region->get_name() );

		$this->assertEquals( 'AL - Alabama', (string) $region );
	}
}
