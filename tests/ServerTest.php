<?php

/**
 * Title: WordPress pay server test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 * @since 1.1.0
 */
class Pronamic_WP_Pay_ServerTest extends WP_UnitTestCase {
	public function test_server_get() {
		$value = Pronamic_WP_Pay_Server::get( 'REQUEST_METHOD', FILTER_SANITIZE_STRING );

		$this->assertTrue( in_array( $value, array( null, 'GET' ), true ) );
	}
}
