<?php
/**
 * Money JSON transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
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
 * @version 2.0.8
 * @since   2.0.8
 */
class MoneyJsonTransformer {
	/**
	 * Convert money object to JSON.
	 *
	 * @param Money|null $money Money.
	 */
	public static function to_json( Money $money = null ) {
		if ( null === $money ) {
			return null;
		}

		$object = (object) array();

		$object->value = $money->get_value();

		if ( null !== $money->get_currency() ) {
			$object->currency = $money->get_currency()->get_alphabetic_code();
		}

		return $object;
	}

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
