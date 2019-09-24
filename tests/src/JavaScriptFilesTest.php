<?php
/**
 * House number test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

/**
 * House number test
 *
 * @link https://github.com/woocommerce/woocommerce/blob/3.5.5/i18n/states/US.php
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.1.6
 */
class JavaScriptFilesTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test files.
	 */
	public function test_files() {
		$iterator = new \GlobIterator( __DIR__ . '/../../js/src/*.js' );

		foreach ( $iterator as $item ) {
			$dist_file     = __DIR__ . '/../../js/dist/' . $item->getBasename();
			$dist_min_file =__DIR__ . '/../../js/dist/' . $item->getBasename( '.js' ) . '.min.js';

			$this->assertFileExists( $dist_file );
			$this->assertFileExists( $dist_min_file );
			$this->assertFileEquals( $item->getPathname(), $dist_file );
		}
	}
}
