<?php

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.1.0
 */
class Pronamic_WP_Pay_PaymentMethods {
	/**
	 * Direct Debit
	 *
	 * @var string
	 */
	const DIRECT_DEBIT = 'direct_debit';

	/**
	 * Credit Card
	 *
	 * @var string
	 */
	const CREDIT_CARD = 'credit_card';

	/**
	 * iDEAL
	 *
	 * @var string
	 */
	const IDEAL = 'ideal';

	/**
	 * MiniTix
	 *
	 * @var string
	 */
	const MINITIX = 'minitix';

	/**
	 * Bancontact/Mister Cash
	 *
	 * @var string
	 */
	const MISTER_CASH = 'mister_cash';

	/**
	 * SOFORT Banking
	 *
	 * @var string
	 * @since 1.1.0
	 */
	const SOFORT = 'sofort';
}
