<?php

/**
 * Title: Server
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Server {
	/**
	 * Get
	 *
	 * This helper function was created to bypass PHP bug 49184.
	 *
	 * @see https://bugs.php.net/49184
	 * @param string $key
	 * @param int $filter
	 * @return mixed
	 */
	public static function get( $key, $filter = FILTER_DEFAULT ) {
		$value = null;

		if ( isset( $_SERVER[ $key ] ) ) { // WPCS: input var okay
			$value = filter_var( wp_unslash( $_SERVER[ $key ] ), $filter ); // WPCS: input var okay
		}

		return $value;
	}
}
