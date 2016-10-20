<?php

/**
 * Title: Direct Debit payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version unreleased
 * @since unreleased
 */
class Pronamic_WP_Pay_DirectDebitPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize Direct Debit payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT;
		$this->name = __( 'Direct Debit', 'pronamic_ideal' );
	}
}
