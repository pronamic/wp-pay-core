<?php

/**
 * Title: Bancontact payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version unreleased
 * @since unreleased
 */
class Pronamic_WP_Pay_BancontactPaymentMethod extends Pronamic_WP_Pay_PaymentMethod {
	/**
	 * Constructs and intialize Bancontact payment method.
	 */
	public function __construct() {
		$this->id   = Pronamic_WP_Pay_PaymentMethods::BANCONTACT;
		$this->name = __( 'Bancontact', 'pronamic_ideal' );
	}
}
