<?php

/**
 * Title: WordPress pay class test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 * @since 1.1.0
 */
class Pronamic_WP_Pay_ClassTest extends WP_UnitTestCase {
	/**
	 * Test method exists.
	 *
	 * @dataProvider status_matrix_provider
	 */
	public function test_method_exists( $class, $method, $expected ) {
		$exists = Pronamic_WP_Pay_Class::method_exists( $class, $method );

		$this->assertEquals( $expected, $exists );
	}

	public function status_matrix_provider() {
		return array(
			array( 'Pronamic_WP_Pay_Class', 'method_exists', true ),
			array( 'Pronamic_WP_Pay_Server', 'get', true ),
			array( 'ClassDoesNotExist', 'method_does_not_exist', false ),
			array( '', '', false ),
			array( null, null, false ),
		);
	}
}
