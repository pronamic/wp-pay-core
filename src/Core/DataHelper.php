<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Data helper class
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class DataHelper {
	/**
	 * Filter the specified characters from the string
	 *
	 * @param array $characters
	 * @param string $string
	 * @param int $max
	 *
	 * @return string
	 */
	public static function filter( array $characters, $string, $max = null ) {
		$pattern = '#[^' . implode( $characters ) . ']#';

		$string = preg_replace( $pattern, '', $string );

		if ( isset( $max ) ) {
			$string = substr( $string, 0, $max );
		}

		return $string;
	}
}
