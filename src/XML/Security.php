<?php

/**
 * Title: XML Security
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_XML_Security {
	/**
	 * Filter XML variable
	 */
	public static function filter( $variable, $filter = FILTER_SANITIZE_STRING ) {
		$result = null;

		if ( $variable ) {
			$result = filter_var( (string) $variable, $filter );
		}

		return $result;
	}
}
