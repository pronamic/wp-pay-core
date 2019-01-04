<?php
/**
 * XML Security
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core\XML
 */

namespace Pronamic\WordPress\Pay\Core\XML;

use SimpleXMLElement;

/**
 * Title: XML Security
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class Security {
	/**
	 * Filter XML variable.
	 *
	 * @param string|SimpleXMLElement $variable Variable to filter.
	 * @param int                     $filter   PHP filter flag constant.
	 */
	public static function filter( $variable, $filter = FILTER_SANITIZE_STRING ) {
		if ( ! $variable ) {
			return null;
		}

		return filter_var( (string) $variable, $filter );
	}
}
