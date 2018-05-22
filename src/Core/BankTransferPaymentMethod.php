<?php
/**
 * Bank Transfer payment method
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Bank Transfer payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
class BankTransferPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize Bank Transfer payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::BANK_TRANSFER;
		$this->name = __( 'Bank Transfer', 'pronamic_ideal' );
	}
}
