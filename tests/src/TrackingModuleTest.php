<?php
/**
 * TrackingModuleTest.php.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Tracking module test
 *
 * @author  Reüel van der Steege
 * @version 2.4.0
 */
class TrackingModuleTest extends TestCase {
	/**
	 * Test get tracking url.
	 */
	public function test_get_tracking_url() {
		$tracking_module = new TrackingModule();

		$url = 'https://domain.tld/';

		// Expected.
		$expected = \sprintf(
			'https://domain.tld/?locale=%1$s&php=%2$s',
			\get_locale(),
			\str_replace( PHP_EXTRA_VERSION, '', \phpversion() )
		);

		// Actual tracking URL.
		$tracking_url = $tracking_module->get_tracking_url( $url );

		$this->assertEquals( $expected, $tracking_url );
	}
}
