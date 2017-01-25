<?php

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.11
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
	 * Bitcoin
	 *
	 * @since 1.3.9
	 * @var string
	 */
	const BITCOIN = 'bitcoin';

	/**
	 * Direct Debit
	 *
	 * @var string
	 */
	const DIRECT_DEBIT = 'direct_debit';

	/**
	 * Constant for the Direct Debit mandate via Credit Card payment method.
	 *
	 * @var string
	 * @since 1.3.9
	 */
	const DIRECT_DEBIT_CREDIT_CARD = 'direct_debit_credit_card';

	/**
	 * Constant for the Direct Debit mandate via iDEAL payment method.
	 *
	 * @var string
	 * @since 1.3.9
	 */
	const DIRECT_DEBIT_IDEAL = 'direct_debit_ideal';

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
	 * Constant for the Maestro payment method.
	 *
	 * @var string
	 * @since 1.3.10
	 */
	const MAESTRO = 'MAESTRO';

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
	 * Constant for the KBC/CBC Payment Button method.
	 *
	 * @since 1.3.11
	 * @var string
	 */
	const KBC = 'kbc';

	/**
	 * Constant for the Belfius Direct Net method.
	 *
	 * @since 1.3.11
	 * @var string
	 */
	const BELFIUS = 'belfius';

	/**
	 * Get payment methods
	 *
	 * @since 1.3.0
	 * @var string
	 * @return array
	 */
	public static function get_payment_methods() {
		$payment_methods = array(
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT         => __( 'Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER      => __( 'Bank Transfer', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BELFIUS            => __( 'Belfius Direct Net', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BITCOIN            => __( 'Bitcoin', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD        => __( 'Credit Card', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT       => __( 'Direct Debit', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL => __( 'Direct Debit mandate via iDEAL', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEAL              => __( 'iDEAL', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::KBC                => __( 'KBC/CBC Payment Button', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::PAYPAL             => __( 'PayPal', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::SOFORT             => __( 'SOFORT Banking', 'pronamic_ideal' ),
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
