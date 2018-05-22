<?php

namespace Pronamic\WordPress\Pay\Core\XML;

/**
 * Title: XML Security
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class Security {
	/**
	 * Filter XML variable
	 */
	public static function filter( $variable, $filter = FILTER_SANITIZE_STRING ) {
		if ( ! $variable ) {
			return null;
		}

		return filter_var( (string) $variable, $filter );
	}
}
