<?php
/**
 * Address helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;

/**
 * Payment line helper
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   2.0.8
 */
class PaymentLineHelper {
	/**
	 * Convert money from exclusive to inclusive tax.
	 *
	 * @param Money $money          Money.
	 * @param float $tax_percentage Tax percentage.
	 */
	private static function exclusive_to_inclusive( Money $money, $tax_percentage ) {
		$exclusive = $money->get_amount();

		$tax = $exclusive * ( $tax_percentage / 100 );

		$inclusive = $exclusive + $tax;

		return new Money( $inclusive, $money->get_currency() );
	}

	/**
	 * Convert money from inclusive to exclusive tax.
	 *
	 * @param Money $money          Money.
	 * @param float $tax_percentage Tax percentage.
	 */
	private static function inclusive_to_exclusive( Money $money, $tax_percentage ) {
		$inclusive = $money->get_amount();

		$tax = ( $inclusive / ( 100 + $tax_percentage ) ) * $tax_percentage;

		$exclusive = $inclusive - $tax;

		return new Money( $exclusive, $money->get_currency() );
	}

	/**
	 * Complement payment line.
	 *
	 * @param PaymentLine $line Payment line to complement.
	 */
	public static function complement_payment_line( PaymentLine $line ) {
		if ( null !== $line->get_tax_percentage() ) {
			if ( null === $line->get_unit_price_including_tax() && null !== $line->get_unit_price_excluding_tax() ) {
				$line->set_unit_price_including_tax( self::exclusive_to_inclusive( $line->get_unit_price_excluding_tax(), $line->get_tax_percentage() ) );
			}

			if ( null === $line->get_unit_price_excluding_tax() && null !== $line->get_unit_price_including_tax() ) {
				$line->set_unit_price_excluding_tax( self::inclusive_to_exclusive( $line->get_unit_price_including_tax(), $line->get_tax_percentage() ) );
			}

			if ( null === $line->get_total_amount_including_tax() && null !== $line->get_total_amount_excluding_tax() ) {
				$line->set_total_amount_including_tax( self::exclusive_to_inclusive( $line->get_total_amount_excluding_tax(), $line->get_tax_percentage() ) );
			}

			if ( null === $line->get_total_amount_excluding_tax() && null !== $line->get_total_amount_including_tax() ) {
				$line->set_total_amount_excluding_tax( self::inclusive_to_exclusive( $line->get_total_amount_including_tax(), $line->get_tax_percentage() ) );
			}
		}
	}
}
