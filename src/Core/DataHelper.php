<?php
/**
 * Data helper class
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Exception;

/**
 * Title: Data helper class
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class DataHelper {
	/**
	 * Filter the specified characters from the string.
	 *
	 * @link https://php.net/preg_replace
	 *
	 * @param array    $characters Array with characters that are allowed.
	 * @param string   $string     String to filter.
	 * @param int|null $max        Maximum length of the string.
	 *
	 * @return string
	 *
	 * @throws Exception When filtering charachters from text fails.
	 */
	public static function filter( array $characters, $string, $max = null ) {
		$pattern = '#[^' . implode( $characters ) . ']#';

		$string = preg_replace( $pattern, '', $string );

		// preg_replace() returns an array if the subject parameter is an array, or a string otherwise.
		if ( ! is_string( $string ) ) {
			throw new Exception( 'Unexpected behavior when filtering characters from text.' );
		}

		if ( isset( $max ) ) {
			$string = substr( $string, 0, $max );
		}

		return $string;
	}
}
