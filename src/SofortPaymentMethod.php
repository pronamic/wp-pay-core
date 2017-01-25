<?php

/**
 * Title: SOFORT Banking payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class Pronamic_WP_Pay_SofortPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize SOFORT Banking payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::SOFORT;
		$this->name = __( 'SOFORT Banking', 'pronamic_ideal' );
	}
}
