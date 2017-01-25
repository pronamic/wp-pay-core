<?php

/**
 * Title: Bank Transfer payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version unreleased
 * @since unreleased
 */
class Pronamic_WP_Pay_BankTransferPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize Bank Transfer payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER;
		$this->name = __( 'Bank Transfer', 'pronamic_ideal' );
	}
}
