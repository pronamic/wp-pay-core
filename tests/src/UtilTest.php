<?php
/**
 * Util test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use stdClass;
use WP_UnitTestCase;

/**
 * Title: WordPress utility test
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class UtilTest extends WP_UnitTestCase {
	/**
	 * Test to string boolean.
	 */
	public function test_to_string_boolean() {
		$boolean = false;

		$result = Util::boolean_to_string( $boolean );

		$this->assertEquals( 'false', $result );
	}
}
