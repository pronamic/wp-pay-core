<?php
/**
 * Recurring
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: WordPress pay recurring
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 2.0.0
 * @since 1.3.11
 */
class Recurring {
	/**
	 * Constant for the first payment.
	 *
	 * @var string
	 */
	const FIRST = 'first';

	/**
	 * Constant for recurring payments.
	 *
	 * @var string
	 */
	const RECURRING = 'recurring';

	/**
	 * Constant for subscription payments.
	 *
	 * @var string
	 */
	const SUBSCRIPTION = 'subscription';
}
