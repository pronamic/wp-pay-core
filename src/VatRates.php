<?php
/**
 * VAT rates
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * VAT rates
 *
 * @link https://ec.europa.eu/taxation_customs/sites/taxation/files/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf
 * @link https://github.com/apilayer/euvatrates.com
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class VatRates {
	/**
	 * Standard rate.
	 *
	 * @var string
	 */
	const STANDARD = 'standard';

	/**
	 * Reduced rate.
	 *
	 * @var string
	 */
	const REDUCED = 'reduced';

	/**
	 * Super reduced rate.
	 *
	 * @var string
	 */
	const SUPER_REDUCED = 'super_reduced';

	/**
	 * Parking rate.
	 *
	 * @var string
	 */
	const PARKING = 'parking';
}
