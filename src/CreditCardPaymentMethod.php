<?php

/**
 * Title: Credit Card payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class Pronamic_WP_Pay_CreditCardPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize iDEAL payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD;
		$this->name = __( 'Credit Card', 'pronamic_ideal' );
	}
}
