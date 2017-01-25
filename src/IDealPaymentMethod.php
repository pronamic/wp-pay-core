<?php

/**
 * Title: iDEAL payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version unreleased
 * @since unreleased
 */
class Pronamic_WP_Pay_IDealPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize iDEAL payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::IDEAL;
		$this->name = __( 'iDEAL', 'pronamic_ideal' );
	}
}
