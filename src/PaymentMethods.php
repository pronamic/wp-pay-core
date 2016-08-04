<?php

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.8
 * @since 1.0.1
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
	 * Constant for the iDEAL + Direct Debit payment method.
	 *
	 * @var string
	 * @since unreleased
	 */
	const IDEAL_DIRECTDEBIT = 'ideal_directdebit';

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
	 * @deprecated deprecated since version 1.3.1
	 */
	const MINITIX = 'minitix';

	/**
	 * Bancontact/Mister Cash
	 *
	 * @deprecated "Bancontact/Mister Cash" was renamed to just "Bancontact".
	 * @var string
	 */
	const MISTER_CASH = 'mister_cash';

	/**
	 * Bancontact
	 *
	 * @var string
	 * @since 1.3.7
	 */
	const BANCONTACT = 'bancontact';

	/**
	 * PayPal
	 *
	 * @var string
	 * @since 1.3.7
	 */
	const PAYPAL = 'paypal';

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
	 * @since 1.3.0
	 * @var string
	 * @return array
	 */
	public static function get_payment_methods() {
		$payment_methods = array(
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT        => __( 'Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER     => __( 'Bank Transfer', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD       => __( 'Credit Card', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT      => __( 'Direct Debit', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEAL             => __( 'iDEAL', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEAL_DIRECTDEBIT => __( 'iDEAL + Direct Debit', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::MISTER_CASH       => __( 'Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::PAYPAL            => __( 'PayPal', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::SOFORT            => __( 'SOFORT Banking', 'pronamic_ideal' ),
		);

		return $payment_methods;
	}

	/**
	 * Get payment method name
	 *
	 * @since 1.3.0
	 * @var string
	 * @return string
	 */
	public static function get_name( $method = null ) {
		$payment_methods = self::get_payment_methods();

		if ( null !== $method && array_key_exists( $method, $payment_methods ) ) {
			return $payment_methods[ $method ];
		}

		return '';
	}
}
