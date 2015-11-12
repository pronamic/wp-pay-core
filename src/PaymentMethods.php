<?php

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.1
 */
class Pronamic_WP_Pay_PaymentMethods {
	/**
	 * Bank transfer
	 *
	 * @var string
	 */
	const BANK_TRANSFER = 'bank_transfer';

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
	 * Constant for the iDEAL payment method.
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
	 * @since 1.0.1
	 */
	const SOFORT = 'sofort';

	/**
	 * Get payment methods
	 *
	 * @var string
	 */
	public static function get_payment_methods() {
		$payment_methods = array (
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER => __( 'Bank Transfer', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT  => __( 'Direct Debit', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD   => __( 'Credit Card', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEAL         => __( 'iDEAL', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::MINITIX       => __( 'MiniTix', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::MISTER_CASH   => __( 'Bancontact/Mister Cash', 'pronamic-ideal' ),
			Pronamic_WP_Pay_PaymentMethods::SOFORT        => __( 'SOFORT Banking', 'pronamic-ideal' ),
		);

		return $payment_methods;
	}

	/**
	 * Get payment method name
	 *
	 * @var string
	 */
	public static function get_name( $method = null ) {
		$payment_methods = self::get_payment_methods();

		if ( null !== $method && array_key_exists( $method, $payment_methods ) ) {
			return $payment_methods[ $method ];
		}

		return '';
	}
}
