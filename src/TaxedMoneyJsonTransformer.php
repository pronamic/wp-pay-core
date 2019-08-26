<?php
/**
 * Taxed money JSON transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use InvalidArgumentException;
use Pronamic\WordPress\Money\TaxedMoney;

/**
 * Taxed money JSON transformer
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   2.0.8
 */
class TaxedMoneyJsonTransformer {
	/**
	 * Convert taxed money object to JSON.
	 *
	 * @param TaxedMoney|null $money Money.
	 *
	 * @return null|object
	 */
	public static function to_json( TaxedMoney $money = null ) {
		if ( null === $money ) {
			return null;
		}

		$object = MoneyJsonTransformer::to_json( $money );

		if ( null === $object ) {
			return null;
		}

		$properties = (array) $object;

		if ( null !== $money->get_tax_value() ) {
			$properties['tax_value'] = $money->get_tax_value();
		}

		if ( null !== $money->get_tax_percentage() ) {
			$properties['tax_percentage'] = $money->get_tax_percentage();
		}

		$object = (object) $properties;

		return $object;
	}

	/**
	 * Convert JSON to taxed money object.
	 *
	 * @param mixed $json JSON.
	 *
	 * @return TaxedMoney
	 *
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		// Default arguments.
		$value          = 0;
		$currency       = null;
		$tax_value      = null;
		$tax_percentage = null;

		if ( property_exists( $json, 'value' ) ) {
			$value = $json->value;
		}

		if ( property_exists( $json, 'currency' ) ) {
			$currency = $json->currency;
		}

		if ( property_exists( $json, 'tax_value' ) ) {
			$tax_value = $json->tax_value;
		}

		if ( property_exists( $json, 'tax_percentage' ) ) {
			$tax_percentage = $json->tax_percentage;
		}

		$money = new TaxedMoney( $value, $currency, $tax_value, $tax_percentage );

		return $money;
	}
}
