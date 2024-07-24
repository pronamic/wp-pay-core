<?php
/**
 * Payment line type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Payment line type.
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class PaymentLineType {
	/**
	 * Constant for 'digital' type.
	 *
	 * @var string
	 */
	const DIGITAL = 'digital';

	/**
	 * Constant for 'discount' type.
	 *
	 * @var string
	 */
	const DISCOUNT = 'discount';

	/**
	 * Constant for 'fee' type.
	 *
	 * @var string
	 */
	const FEE = 'fee';

	/**
	 * Constant for 'physical' type.
	 *
	 * @var string
	 */
	const PHYSICAL = 'physical';

	/**
	 * Constant for 'shipping' type.
	 *
	 * @var string
	 */
	const SHIPPING = 'shipping';

	/**
	 * Constant for 'tax' type.
	 *
	 * @var string
	 */
	const TAX = 'tax';
}
