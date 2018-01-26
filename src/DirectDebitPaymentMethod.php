<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Direct Debit payment method
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
 * @since 1.3.11
 */
class DirectDebitPaymentMethod extends PaymentMethod {
	/**
	 * Constructs and intialize Direct Debit payment method.
	 */
	public function __construct() {
		$this->id   = PaymentMethods::DIRECT_DEBIT;
		$this->name = __( 'Direct Debit', 'pronamic_ideal' );
	}
}
