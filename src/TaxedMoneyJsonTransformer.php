<?php
/**
 * Taxed money JSON transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
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

		$object = (object) array();

		if ( null !== $money->get_amount() ) {
			$object->amount = $money->get_amount();
		}

		if ( null !== $money->get_currency() ) {
			$object->currency = $money->get_currency()->get_alphabetic_code();
		}

		if ( null !== $money->get_tax_amount() ) {
			$object->tax_amount = $money->get_tax_amount();
		}

		if ( null !== $money->get_tax_percentage() ) {
			$object->tax_percentage = $money->get_tax_percentage();
		}

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
		$amount         = 0;
		$currency       = null;
		$tax_amount     = null;
		$tax_percentage = null;

		if ( property_exists( $json, 'amount' ) ) {
			$amount = $json->amount;
		}

		if ( property_exists( $json, 'currency' ) ) {
			$currency = $json->currency;
		}

		if ( property_exists( $json, 'tax_amount' ) ) {
			$tax_amount = $json->tax_amount;
		}

		if ( property_exists( $json, 'tax_percentage' ) ) {
			$tax_percentage = $json->tax_percentage;
		}

		$money = new TaxedMoney( $amount, $currency, $tax_amount, $tax_percentage );

		return $money;
	}
}
