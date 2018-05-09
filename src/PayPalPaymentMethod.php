<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: PayPal payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
class PayPalPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize PayPal payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::PAYPAL;
		$this->name = __( 'PayPal', 'pronamic_ideal' );
	}
}
