<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Bancontact payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
class BancontactPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize Bancontact payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::BANCONTACT;
		$this->name = __( 'Bancontact', 'pronamic_ideal' );
	}
}
