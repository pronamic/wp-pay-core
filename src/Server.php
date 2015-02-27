<?php

/**
 * Title: Server
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.1.0
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Server {
	/**
	 * Get
	 *
	 * This helper function was created to bypass PHP bug 49184.
	 * @see https://bugs.php.net/49184
	 *
	 * @param string $key
	 * @param int $filter
	 * @return mixed
	 */
	public static function get( $key, $filter ) {
		$value = null;

		if ( isset( $_SERVER[ $key ] ) ) {
			$value = filter_var( $_SERVER[ $key ], $filter );
		}

		return $value;
	}
}
