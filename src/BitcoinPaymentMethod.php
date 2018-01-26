<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Bitcoin payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class BitcoinPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize Bitcoin payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::BITCOIN;
		$this->name = __( 'Bitcoin', 'pronamic_ideal' );
	}
}
