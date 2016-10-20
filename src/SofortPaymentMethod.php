<?php

/**
 * Title: SOFORT Banking payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version unreleased
 * @since unreleased
 */
class Pronamic_WP_Pay_SofortPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize iDEAL payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::SOFORT;
		$this->name = __( 'SOFORT Banking', 'pronamic_ideal' );
	}
}
