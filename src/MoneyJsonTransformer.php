<?php
/**
 * Money JSON transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use InvalidArgumentException;
use Pronamic\WordPress\Money\Money;

/**
 * Money JSON transformer
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class MoneyJsonTransformer {
	/**
	 * Convert JSON to money object.
	 *
	 * @param mixed $json JSON.
	 * @return Money
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$money = new Money();

		if ( property_exists( $json, 'value' ) ) {
			$money->set_value( $json->value );
		}

		if ( property_exists( $json, 'currency' ) ) {
			$money->set_currency( $json->currency );
		}

		return $money;
	}
}
