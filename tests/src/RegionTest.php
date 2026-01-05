<?php
/**
 * Region test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Region test
 *
 * @link https://github.com/woocommerce/woocommerce/blob/3.5.5/i18n/states/US.php
 *
 * @version 2.1.6
 * @since   2.1.6
 */
class RegionTest extends TestCase {
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
