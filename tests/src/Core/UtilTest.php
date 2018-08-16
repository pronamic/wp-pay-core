<?php
/**
 * Util test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: WordPress pay util test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.1.0
 */
class UtilTest extends \WP_UnitTestCase {
	/**
	 * Test method exists.
	 *
	 * @dataProvider status_matrix_provider
	 *
	 * @param string $class    Class name to check.
	 * @param string $method   Method name to check.
	 * @param bool   $expected Expected result.
	 */
	public function test_class_method_exists( $class, $method, $expected ) {
		$exists = Util::class_method_exists( $class, $method );

		$this->assertEquals( $expected, $exists );
	}

	/**
	 * Status matrix provider.
	 *
	 * @return array
	 */
	public function status_matrix_provider() {
		return array(
			array( __NAMESPACE__ . '\Util', 'class_method_exists', true ),
			array( __NAMESPACE__ . '\Server', 'get', true ),
			array( 'ClassDoesNotExist', 'method_does_not_exist', false ),
			array( '', '', false ),
			array( null, null, false ),
		);
	}
}
