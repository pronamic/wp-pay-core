<?php
/**
 * VAT number validation service
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT number validation service
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.1.0
 */
class VatNumberValidationService {
	/**
	 * VIES.
	 *
	 * @link https://ec.europa.eu/taxation_customs/vies/?locale=en
	 * @var string
	 */
	const VIES = 'vies';
}
