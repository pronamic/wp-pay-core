<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: iDEAL payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
class IDealPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize iDEAL payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::IDEAL;
		$this->name = __( 'iDEAL', 'pronamic_ideal' );
	}
}
