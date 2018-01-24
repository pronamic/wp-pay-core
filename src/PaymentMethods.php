<?php

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.14
 * @since 1.0.1
 */
class Pronamic_WP_Pay_PaymentMethods {
	/**
	 * Alipay
	 *
	 * @var string
	 * @since unreleased
	 */
	const ALIPAY = 'alipay';

	/**
	 * Bancontact
	 *
	 * @var string
	 * @since 1.3.7
	 */
	const BANCONTACT = 'bancontact';

	/**
	 * Bank transfer
	 *
	 * @var string
	 */
	const BANK_TRANSFER = 'bank_transfer';

	/**
	 * Constant for the Belfius Direct Net method.
	 *
	 * @since 1.3.11
	 * @var string
	 */
	const BELFIUS = 'belfius';

	/**
	 * Bitcoin
	 *
	 * @since 1.3.9
	 * @var string
	 */
	const BITCOIN = 'bitcoin';

	/**
	 * Bunq
	 *
	 * @see https://www.sisow.nl/news/00009
	 * @see https://plugins.trac.wordpress.org/browser/sisow-for-woocommerce/tags/4.7.2/includes/classes/Sisow/Gateway/Bunq.php
	 * @since 1.3.13
	 * @var string
	 */
	const BUNQ = 'bunq';

	/**
	 * Credit Card
	 *
	 * @var string
	 */
	const CREDIT_CARD = 'credit_card';

	/**
	 * Direct Debit
	 *
	 * @var string
	 */
	const DIRECT_DEBIT = 'direct_debit';

	/**
	 * Constant for the Direct Debit mandate via Bancontact payment method.
	 *
	 * @var string
	 * @since unreleased
	 */
	const DIRECT_DEBIT_BANCONTACT = 'direct_debit_bancontact';

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
	 * Constant for the Direct Debit mandate via SOFORT payment method.
	 *
	 * @var string
	 * @since 1.3.15
	 */
	const DIRECT_DEBIT_SOFORT = 'direct_debit_sofort';

	/**
	 * Constant for the iDEAL payment method.
	 *
	 * @var string
	 */
	const IDEAL = 'ideal';

	/**
	 * Constant for the iDEAL payment method.
	 *
	 * @var string
	 */
	const IDEALQR = 'idealqr';

	/**
	 * Constant for the Giropay payment method.
	 *
	 * @var string
	 */
	const GIROPAY = 'giropay';

	/**
	 * Constant for the KBC/CBC Payment Button method.
	 *
	 * @since 1.3.11
	 * @var string
	 */
	const KBC = 'kbc';

	/**
	 * Constant for the Maestro payment method.
	 *
	 * @var string
	 * @since 1.3.10
	 */
	const MAESTRO = 'maestro';

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
	 * Constant for the Payconiq method.
	 *
	 * @since unreleased
	 * @var string
	 */
	const PAYCONIQ = 'payconiq';

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

	/////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @since 1.3.0
	 * @var string
	 * @return array
	 */
	public static function get_payment_methods() {
		$payment_methods = array(
			Pronamic_WP_Pay_PaymentMethods::ALIPAY        => __( 'Alipay', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT    => __( 'Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER => __( 'Bank Transfer', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BELFIUS       => __( 'Belfius Direct Net', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BITCOIN       => __( 'Bitcoin', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::BUNQ          => __( 'Bunq', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD   => __( 'Credit Card', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT  => __( 'Direct Debit', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_BANCONTACT => __( 'Direct Debit mandate via Bancontact', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL => __( 'Direct Debit mandate via iDEAL', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_SOFORT => __( 'Direct Debit mandate via SOFORT', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::GIROPAY       => __( 'Giropay', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEAL         => __( 'iDEAL', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::IDEALQR       => __( 'iDEAL QR', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::KBC           => __( 'KBC/CBC Payment Button', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::PAYCONIQ      => __( 'Payconiq', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::PAYPAL        => __( 'PayPal', 'pronamic_ideal' ),
			Pronamic_WP_Pay_PaymentMethods::SOFORT        => __( 'SOFORT Banking', 'pronamic_ideal' ),
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

	/////////////////////////////////////////////////

	/**
	 * Get direct debit methods.
	 *
	 * @since unreleased
	 * @return array
	 */
	public static function get_direct_debit_methods() {
		$payment_methods = array(
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_BANCONTACT => Pronamic_WP_Pay_PaymentMethods::BANCONTACT,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL      => Pronamic_WP_Pay_PaymentMethods::IDEAL,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_SOFORT     => Pronamic_WP_Pay_PaymentMethods::SOFORT,
		);

		return $payment_methods;
	}

	/**
	 * Is direct debit method.
	 *
	 * @since unreleased
	 *
	 * @param $payment_method
	 *
	 * @return bool
	 */
	public static function is_direct_debit_method( $payment_method ) {
		return array_key_exists( $payment_method, self::get_direct_debit_methods() );
	}

	/////////////////////////////////////////////////

	/**
	 * Get recurring methods.
	 *
	 * @since unreleased
	 * @return array
	 */
	public static function get_recurring_methods() {
		// Get the direct debit methods
		$payment_methods = self::get_direct_debit_methods();

		// Add additional methods suitable for recurring payments
		$payment_methods[ Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD ] = Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD;

		return $payment_methods;
	}

	/**
	 * Is recurring method.
	 *
	 * @since unreleased
	 *
	 * @param $payment_method
	 *
	 * @return bool
	 */
	public static function is_recurring_method( $payment_method ) {
		return array_key_exists( $payment_method, self::get_recurring_methods() );
	}

	/////////////////////////////////////////////////

	/**
	 * Get first method for payment method.
	 *
	 * @param $payment_method
	 *
	 * @return
	 */
	public static function get_first_payment_method( $payment_method ) {
		if ( self::is_direct_debit_method( $payment_method ) ) {
			$direct_debit_methods = self::get_direct_debit_methods();

			return $direct_debit_methods[ $payment_method ];
		}

		return $payment_method;
	}

	/////////////////////////////////////////////////

	/**
	 * Update active payment methods option.
	 *
	 * @since unreleased
	 *
	 * @return array
	 */
	public static function update_active_payment_methods() {
		$active_payment_methods = array();

		$query = new \WP_Query( array(
			'post_type'      => 'pronamic_gateway',
			'posts_per_page' => 30,
			'fields'         => 'ids',
		) );

		foreach ( $query->posts as $config_id ) {
			$gateway = \Pronamic\WordPress\Pay\Plugin::get_gateway( $config_id );

			if ( ! $gateway ) {
				continue;
			}

			$active_payment_methods = array_merge(
				$active_payment_methods,
				$gateway->get_supported_payment_methods()
			);
		}

		$active_payment_methods = array_unique( $active_payment_methods );

		update_option( 'pronamic_pay_active_payment_methods', $active_payment_methods );
	}

	/////////////////////////////////////////////////

	/**
	 * Check if payment method is active.
	 *
	 * @since unreleased
	 *
	 * @return bool
	 */
	public static function is_active( $payment_method = null ) {
		$active_payment_methods = get_option( 'pronamic_pay_active_payment_methods' );

		// Update active payment methods option if necessary.
		if ( ! is_array( $active_payment_methods ) ) {
			self::update_active_payment_methods();

			$active_payment_methods = get_option( 'pronamic_pay_active_payment_methods' );
		}

		$is_active = in_array( $payment_method, $active_payment_methods, true );

		return $is_active;
	}
}
