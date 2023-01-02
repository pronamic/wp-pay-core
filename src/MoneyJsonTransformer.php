<?php
/**
 * Money JSON transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;

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
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		// Default arguments.
		$value          = 0;
		$currency       = null;
		$tax_value      = null;
		$tax_percentage = null;

		$money = new Money();

		if ( \property_exists( $json, 'value' ) ) {
			$value = $json->value;
		}

		if ( \property_exists( $json, 'currency' ) ) {
			$currency = $json->currency;
		}

		if ( \property_exists( $json, 'tax_value' ) ) {
			$tax_value = $json->tax_value;
		}

		if ( \property_exists( $json, 'tax_percentage' ) ) {
			$tax_percentage = $json->tax_percentage;
		}

		/**
		 * In older versions of this library the currency could be empty,
		 * for backward compatibility we fall back to the euro.
		 */
		if ( null === $currency ) {
			$currency = 'EUR';
		}

		if ( ! empty( $tax_value ) || ! empty( $tax_percentage ) ) {
			return new TaxedMoney( $value, $currency, $tax_value, $tax_percentage );
		}

		return new Money( $value, $currency );
	}
}
