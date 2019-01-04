<?php
/**
 * Server
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Server
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.1.0
 */
class Server {
	/**
	 * Get server variable.
	 *
	 * This helper function was created to bypass PHP bug 49184.
	 *
	 * @link https://bugs.php.net/49184
	 *
	 * @param string $key    Server value key.
	 * @param int    $filter PHP filter constant.
	 *
	 * @return mixed
	 */
	public static function get( $key, $filter = FILTER_DEFAULT ) {
		$value = null;

		if ( isset( $_SERVER[ $key ] ) ) { // WPCS: input var okay.
			$value = filter_var( wp_unslash( $_SERVER[ $key ] ), $filter ); // WPCS: input var okay.
		}

		return $value;
	}
}
