<?php

/**
 * Title: PayPal payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class Pronamic_WP_Pay_PayPalPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize PayPal payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::PAYPAL;
		$this->name = __( 'PayPal', 'pronamic_ideal' );
	}
}
