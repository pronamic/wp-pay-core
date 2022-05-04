<?php
/**
 * Server test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Core;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: WordPress pay server test
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.1.0
 */
class ServerTest extends TestCase {
	/**
	 * Test server get.
	 */
	public function test_server_get() {
		$value = Server::get( 'REQUEST_METHOD', FILTER_SANITIZE_STRING );

		$this->assertTrue( in_array( $value, [ null, 'GET' ], true ) );
	}
}
