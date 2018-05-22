<?php
/**
 * SOFORT Banking payment method
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: SOFORT Banking payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
class SofortPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize SOFORT Banking payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::SOFORT;
		$this->name = __( 'SOFORT Banking', 'pronamic_ideal' );
	}
}
