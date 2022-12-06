<?php
/**
 * XML Security
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core\XML
 */

namespace Pronamic\WordPress\Pay\Core\XML;

use SimpleXMLElement;

/**
 * Title: XML Security
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 1.0.0
 * @deprecated Deprcated since version 5.0, see https://github.com/pronamic/wp-pay-core/issues/84 for more information.
 */
class Security {
	/**
	 * Filter XML variable.
	 *
	 * @deprecated Deprcated since version 5.0, see https://github.com/pronamic/wp-pay-core/issues/84 for more information.
	 * @param string|SimpleXMLElement $variable Variable to filter.
	 * @param int                     $filter   PHP filter flag constant.
	 * @return mixed|null
	 */
	public static function filter( $variable, $filter = FILTER_SANITIZE_STRING ) {
		if ( ! $variable ) {
			return null;
		}

		return filter_var( (string) $variable, $filter );
	}
}
