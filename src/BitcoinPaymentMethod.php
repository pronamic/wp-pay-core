<?php

/**
 * Title: Bitcoin payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class Pronamic_WP_Pay_BitcoinPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize Bitcoin payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::BITCOIN;
		$this->name = __( 'Bitcoin', 'pronamic_ideal' );
	}
}
