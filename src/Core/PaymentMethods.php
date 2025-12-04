<?php
/**
 * Payment methods
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WpPayLogos\ImageService;
use WP_Post;
use WP_Query;

/**
 * Title: WordPress pay payment methods
 * Description:
 * Copyright: 2005-2025 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.7.1
 * @since   1.0.1
 */
class PaymentMethods {
	/**
	 * Alma.
	 *
	 * @var string
	 * @since 4.26
	 */
	const ALMA = 'alma';

	/**
	 * AfterPay (afterpay.nl).
	 *
	 * @deprecated Use `AFTERPAY_NL` or `AFTERPAY_COM` instead.
	 * @var string
	 * @since 2.1.0
	 */
	const AFTERPAY = 'afterpay';

	/**
	 * AfterPay (afterpay.nl).
	 *
	 * @link https://github.com/pronamic/wp-pronamic-pay/issues/339
	 * @link https://www.afterpay.nl/
	 * @deprecated Use `RIVERTY` instead, see https://github.com/pronamic/wp-pronamic-pay/issues/339 for details.
	 * @var string
	 */
	const AFTERPAY_NL = 'afterpay_nl';

	/**
	 * Afterpay (afterpay.com).
	 *
	 * @link https://www.afterpay.com/
	 * @var string
	 */
	const AFTERPAY_COM = 'afterpay_com';

	/**
	 * Alipay
	 *
	 * @var string
	 * @since 2.0.0
	 */
	const ALIPAY = 'alipay';

	/**
	 * American Express.
	 *
	 * @var string
	 * @since 3.0.1
	 */
	const AMERICAN_EXPRESS = 'american_express';

	/**
	 * Apple Pay
	 *
	 * @var string
	 * @since 2.2.8
	 */
	const APPLE_PAY = 'apple_pay';

	/**
	 * Bancontact
	 *
	 * @var string
	 * @since 1.3.7
	 */
	const BANCONTACT = 'bancontact';

	/**
	 * BANCOMAT Pay.
	 *
	 * @var string
	 * @since 4.26
	 */
	const BANCOMAT_PAY = 'bancomat_pay';

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
	 * Billie
	 *
	 * @var string
	 */
	const BILLIE = 'billie';

	/**
	 * Billink
	 *
	 * @since 2.0.9
	 * @var string
	 */
	const BILLINK = 'billink';

	/**
	 * Bitcoin
	 *
	 * @since 1.3.9
	 * @var string
	 */
	const BITCOIN = 'bitcoin';

	/**
	 * BLIK
	 *
	 * @link https://blik.com/
	 * @var string
	 */
	const BLIK = 'blik';

	/**
	 * Bunq
	 *
	 * @link https://www.sisow.nl/news/00009
	 * @link https://plugins.trac.wordpress.org/browser/sisow-for-woocommerce/tags/4.7.2/includes/classes/Sisow/Gateway/Bunq.php
	 * @since 1.3.13
	 * @var string
	 */
	const BUNQ = 'bunq';

	/**
	 * Constant for the In3 payment method.
	 *
	 * @var string
	 * @since 2.1.0
	 */
	const IN3 = 'in3';

	/**
	 * Capayable.
	 *
	 * @var string
	 * @since 2.0.9
	 */
	const CAPAYABLE = 'capayable';

	/**
	 * Card.
	 *
	 * @link https://github.com/pronamic/pronamic-pay/issues/82
	 * @var string
	 */
	const CARD = 'card';

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
	 * @since 1.3.13
	 */
	const DIRECT_DEBIT_BANCONTACT = 'direct_debit_bancontact';

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
	 * Constant for the EPS payment method.
	 *
	 * @var string
	 * @since 2.1.7
	 */
	const EPS = 'eps';

	/**
	 * Constant for the Focum payment method.
	 *
	 * @var string
	 * @since 2.1.0
	 */
	const FOCUM = 'focum';

	/**
	 * Gift card.
	 *
	 * @var string
	 * @since 4.26
	 */
	const GIFT_CARD = 'gift_card';

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
	 * Constant for the Google Pay payment method.
	 *
	 * @var string
	 */
	const GOOGLE_PAY = 'google_pay';

	/**
	 * Constant for the KBC/CBC Payment Button method.
	 *
	 * @since 1.3.11
	 * @var string
	 */
	const KBC = 'kbc';

	/**
	 * Constant for the Klarna payment method.
	 *
	 * @var string
	 */
	const KLARNA = 'klarna';

	/**
	 * Constant for the Klarna Pay Later payment method.
	 *
	 * Klarna Pay Later is not one specific payment method, but a category with a number of pay later payment methods.
	 *
	 * @link https://docs.klarna.com/klarna-payments/in-depth-knowledge/payment-method-grouping/
	 * @since 2.1.0
	 * @var string
	 */
	const KLARNA_PAY_LATER = 'klarna_pay_later';

	/**
	 * Constant for the Klarna Pay Now payment method.
	 *
	 * Klarna Pay Now is not one specific payment method, but a category with a number of pay later payment methods.
	 *
	 * @link https://docs.klarna.com/klarna-payments/in-depth-knowledge/payment-method-grouping/
	 * @since 4.1.0
	 * @var string
	 */
	const KLARNA_PAY_NOW = 'klarna_pay_now';

	/**
	 * Constant for the Klarna Pay Over Time payment method.
	 *
	 * Klarna Pay Over Time is not one specific payment method, but a category with a number of pay over time payment methods.
	 * Klarna Pay Over Time is also known as Klarna Slice It, some payment providers also use this naming convention.
	 *
	 * @link https://docs.klarna.com/klarna-payments/in-depth-knowledge/payment-method-grouping/
	 * @since 4.1.0
	 * @var string
	 */
	const KLARNA_PAY_OVER_TIME = 'klarna_pay_over_time';

	/**
	 * Constant for the Maestro payment method.
	 *
	 * @var string
	 * @since 1.3.10
	 */
	const MAESTRO = 'maestro';

	/**
	 * Constant for the Mastercard payment method.
	 *
	 * @link https://www.mastercard.nl/
	 * @var string
	 * @since 3.0.1
	 */
	const MASTERCARD = 'mastercard';

	/**
	 * MB WAY
	 *
	 * @since unreleased
	 * @link  https://www.mbway.pt/
	 * @var string
	 */
	const MB_WAY = 'mb_way';

	/**
	 * Bancontact/Mister Cash
	 *
	 * @deprecated "Bancontact/Mister Cash" was renamed to just "Bancontact".
	 * @var string
	 */
	const MISTER_CASH = 'mister_cash';

	/**
	 * MobilePay
	 *
	 * @link https://www.mobilepay.dk/
	 * @var string
	 */
	const MOBILEPAY = 'mobilepay';

	/**
	 * Multibanco.
	 *
	 * @var string
	 * @since 4.26
	 */
	const MULTIBANCO = 'multibanco';

	/**
	 * MyBank.
	 *
	 * @link https://github.com/mollie/mollie-api-php/blob/ed5b2ba1dc8f30a4674f10ca78ad547c2df91008/src/Types/PaymentMethod.php#L114-L117
	 * @link https://github.com/mollie/WooCommerce/blob/bda9155ac19e1c576f19f436d74fe3f7fe845298/src/PaymentMethods/Mybank.php#L7
	 * @link https://mybank.eu/
	 * @var string
	 */
	const MYBANK = 'mybank';

	/**
	 * Constant for the Pay by Bank method.
	 *
	 * @since 4.26.0
	 * @var string
	 */
	const PAY_BY_BANK = 'paybybank';

	/**
	 * Constant for the Payconiq method.
	 *
	 * @since 2.0.0
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
	 * Paysafecard.
	 *
	 * @var string
	 * @since 4.26
	 */
	const PAYSAFECARD = 'paysafecard';

	/**
	 * Postepay.
	 *
	 * @var string
	 */
	const POSTEPAY = 'postepay';

	/**
	 * Przelewy24
	 *
	 * @since 2.5.0
	 * @var string
	 */
	const PRZELEWY24 = 'przelewy24';

	/**
	 * Riverty.
	 *
	 * @link https://github.com/pronamic/wp-pronamic-pay/issues/339
	 * @since 4.6.0
	 * @var string
	 */
	const RIVERTY = 'riverty';

	/**
	 * Santander
	 *
	 * @var string
	 * @since 2.6.0
	 */
	const SANTANDER = 'santander';

	/**
	 * Satispay.
	 *
	 * @var string
	 * @since 4.26
	 */
	const SATISPAY = 'satispay';

	/**
	 * SOFORT Banking
	 *
	 * @var string
	 * @since 1.0.1
	 */
	const SOFORT = 'sofort';

	/**
	 * SprayPay
	 *
	 * @var string
	 * @since 2.8.0
	 */
	const SPRAYPAY = 'spraypay';

	/**
	 * Swish
	 *
	 * @var string
	 * @since 2.6.3
	 */
	const SWISH = 'swish';

	/**
	 * Trustly.
	 *
	 * @var string
	 * @since 4.26
	 */
	const TRUSTLY = 'trustly';

	/**
	 * TWINT
	 *
	 * @var string
	 * @since unreleased
	 */
	const TWINT = 'twint';

	/**
	 * Constant for the V PAY payment method.
	 *
	 * @link https://en.wikipedia.org/wiki/V_Pay
	 * @var string
	 * @since 3.0.1
	 */
	const V_PAY = 'v_pay';

	/**
	 * Vipps
	 *
	 * @var string
	 * @since 2.6.3
	 */
	const VIPPS = 'vipps';

	/**
	 * Constant for the Visa payment method.
	 *
	 * @link https://www.visa.nl/
	 * @var string
	 * @since 3.0.1
	 */
	const VISA = 'visa';

	/**
	 * Vouchers.
	 *
	 * @var string
	 * @since 4.26
	 */
	const VOUCHERS = 'vouchers';

	/**
	 * Get payment methods
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public static function get_payment_methods() {
		$payment_methods = [
			self::ALMA                    => __( 'Alma', 'pronamic_ideal' ),
			self::AFTERPAY_NL             => _x( 'AfterPay', 'afterpay.nl', 'pronamic_ideal' ),
			self::AFTERPAY_COM            => _x( 'Afterpay', 'afterpay.com', 'pronamic_ideal' ),
			self::ALIPAY                  => __( 'Alipay', 'pronamic_ideal' ),
			self::AMERICAN_EXPRESS        => __( 'American Express', 'pronamic_ideal' ),
			self::APPLE_PAY               => __( 'Apple Pay', 'pronamic_ideal' ),
			self::BANCONTACT              => __( 'Bancontact', 'pronamic_ideal' ),
			self::BANCOMAT_PAY            => __( 'BANCOMAT Pay', 'pronamic_ideal' ),
			self::BANK_TRANSFER           => __( 'Bank Transfer', 'pronamic_ideal' ),
			self::BELFIUS                 => __( 'Belfius Direct Net', 'pronamic_ideal' ),
			self::BILLIE                  => __( 'Billie', 'pronamic_ideal' ),
			self::BILLINK                 => __( 'Billink', 'pronamic_ideal' ),
			self::BITCOIN                 => __( 'Bitcoin', 'pronamic_ideal' ),
			self::BLIK                    => __( 'BLIK', 'pronamic_ideal' ),
			self::BUNQ                    => __( 'Bunq', 'pronamic_ideal' ),
			self::CAPAYABLE               => __( 'Capayable', 'pronamic_ideal' ),
			self::IN3                     => __( 'In3', 'pronamic_ideal' ),
			self::CARD                    => __( 'Card', 'pronamic_ideal' ),
			self::CREDIT_CARD             => __( 'Credit Card', 'pronamic_ideal' ),
			self::DIRECT_DEBIT            => __( 'Direct Debit', 'pronamic_ideal' ),
			self::DIRECT_DEBIT_BANCONTACT => sprintf(
				/* translators: %s: payment method */
				__( 'Direct Debit (mandate via %s)', 'pronamic_ideal' ),
				__( 'Bancontact', 'pronamic_ideal' )
			),
			self::DIRECT_DEBIT_IDEAL      => sprintf(
				/* translators: %s: payment method */
				__( 'Direct Debit (mandate via %s)', 'pronamic_ideal' ),
				__( 'iDEAL', 'pronamic_ideal' )
			),
			self::DIRECT_DEBIT_SOFORT     => sprintf(
				/* translators: %s: payment method */
				__( 'Direct Debit (mandate via %s)', 'pronamic_ideal' ),
				__( 'SOFORT', 'pronamic_ideal' )
			),
			self::EPS                     => __( 'EPS', 'pronamic_ideal' ),
			self::FOCUM                   => __( 'Focum', 'pronamic_ideal' ),
			self::GIFT_CARD               => __( 'Gift Card', 'pronamic_ideal' ),
			self::GIROPAY                 => __( 'Giropay', 'pronamic_ideal' ),
			self::GOOGLE_PAY              => __( 'Google Pay', 'pronamic_ideal' ),
			self::IDEAL                   => __( 'iDEAL', 'pronamic_ideal' ),
			self::IDEALQR                 => __( 'iDEAL QR', 'pronamic_ideal' ),
			self::KBC                     => __( 'KBC/CBC Payment Button', 'pronamic_ideal' ),
			self::KLARNA                  => __( 'Klarna', 'pronamic_ideal' ),
			self::KLARNA_PAY_LATER        => __( 'Klarna Pay Later', 'pronamic_ideal' ),
			self::KLARNA_PAY_NOW          => __( 'Klarna Pay Now', 'pronamic_ideal' ),
			self::KLARNA_PAY_OVER_TIME    => __( 'Klarna Pay Over Time', 'pronamic_ideal' ),
			self::MAESTRO                 => __( 'Maestro', 'pronamic_ideal' ),
			self::MASTERCARD              => __( 'Mastercard', 'pronamic_ideal' ),
			self::MB_WAY                  => __( 'MB WAY', 'pronamic_ideal' ),
			self::MOBILEPAY               => __( 'MobilePay', 'pronamic_ideal' ),
			self::MULTIBANCO              => __( 'Multibanco', 'pronamic_ideal' ),
			self::MYBANK                  => __( 'MyBank', 'pronamic_ideal' ),
			self::PAYCONIQ                => __( 'Payconiq', 'pronamic_ideal' ),
			self::PAYPAL                  => __( 'PayPal', 'pronamic_ideal' ),
			self::PAYSAFECARD             => __( 'Paysafecard', 'pronamic_ideal' ),
			self::PAY_BY_BANK             => __( 'Pay by Bank', 'pronamic_ideal' ),
			self::PRZELEWY24              => __( 'Przelewy24', 'pronamic_ideal' ),
			self::RIVERTY                 => __( 'Riverty', 'pronamic_ideal' ),
			self::SANTANDER               => __( 'Santander', 'pronamic_ideal' ),
			self::SATISPAY                => __( 'Satispay', 'pronamic_ideal' ),
			self::SOFORT                  => __( 'SOFORT Banking', 'pronamic_ideal' ),
			self::SPRAYPAY                => __( 'SprayPay', 'pronamic_ideal' ),
			self::SWISH                   => __( 'Swish', 'pronamic_ideal' ),
			self::TRUSTLY                 => __( 'Trustly', 'pronamic_ideal' ),
			self::TWINT                   => __( 'TWINT', 'pronamic_ideal' ),
			self::V_PAY                   => __( 'V PAY', 'pronamic_ideal' ),
			self::VIPPS                   => __( 'Vipps', 'pronamic_ideal' ),
			self::VISA                    => __( 'Visa', 'pronamic_ideal' ),
			self::VOUCHERS                => __( 'Vouchers', 'pronamic_ideal' ),
		];

		return $payment_methods;
	}

	/**
	 * Get payment method name
	 *
	 * @since 1.3.0
	 *
	 * @param string|null $method   Method to get the name for.
	 * @param string|null $fallback Default name to return if method was not found.
	 *
	 * @return string|null
	 */
	public static function get_name( $method = null, $fallback = null ) {
		// Get name from registered payment method.
		$payment_method = pronamic_pay_plugin()->get_payment_methods( [ 'id' => $method ] )->getIterator()->current();

		if ( null !== $payment_method ) {
			return $payment_method->get_name();
		}

		// Fallback to static name.
		$payment_methods = self::get_payment_methods();

		if ( null !== $method && array_key_exists( $method, $payment_methods ) ) {
			return $payment_methods[ $method ];
		}

		if ( null === $fallback ) {
			return $method;
		}

		return $fallback;
	}

	/**
	 * Get icon URL.
	 *
	 * @param string|null $method Payment method.
	 * @param string|null $size   Icon size.
	 * @return string|null
	 */
	public static function get_icon_url( $method = null, $size = null ) {
		// Check method.
		if ( empty( $method ) || 'void' === $method ) {
			return null;
		}

		// Size.
		if ( empty( $size ) ) {
			$size = '640x360';
		}

		$image_service = new ImageService();

		$method_slug = \str_replace( '_', '-', $method );

		$path = 'methods/' . $method_slug . '/method-' . $method_slug . '-' . $size . '.svg';

		$path = $image_service->get_path( $path );

		if ( ! \is_readable( $path ) ) {
			return null;
		}

		return \plugins_url( \basename( $path ), $path );
	}

	/**
	 * Maybe update active payment methods.
	 *
	 * @return void
	 */
	public static function maybe_update_active_payment_methods() {
		$payment_methods = get_option( 'pronamic_pay_active_payment_methods' );

		// Update active payment methods option if necessary.
		if ( ! is_array( $payment_methods ) ) {
			self::update_active_payment_methods();
		}
	}

	/**
	 * Update active payment methods option.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function update_active_payment_methods() {
		$active_payment_methods = [];

		$query = new WP_Query(
			[
				'post_type' => 'pronamic_gateway',
				'nopaging'  => true,
				'fields'    => 'ids',
			]
		);

		foreach ( $query->posts as $config_id ) {
			if ( $config_id instanceof WP_Post ) {
				$config_id = $config_id->ID;
			}

			$gateway = Plugin::get_gateway( $config_id );

			if ( null === $gateway ) {
				continue;
			}

			$payment_methods = $gateway->get_payment_methods(
				[
					'status' => [ '', 'active' ],
				]
			);

			foreach ( $payment_methods as $payment_method ) {
				$id = $payment_method->get_id();

				if ( ! array_key_exists( $id, $active_payment_methods ) ) {
					$active_payment_methods[ $id ] = [];
				}

				$active_payment_methods[ $id ][] = $config_id;
			}
		}

		update_option( 'pronamic_pay_active_payment_methods', $active_payment_methods );
	}

	/**
	 * Get active payment methods.
	 *
	 * @return array
	 */
	public static function get_active_payment_methods() {
		self::maybe_update_active_payment_methods();

		$payment_methods = [];

		$active_methods = get_option( 'pronamic_pay_active_payment_methods' );

		if ( is_array( $active_methods ) ) {
			$payment_methods = array_keys( $active_methods );
		}

		return $payment_methods;
	}

	/**
	 * Get config IDs for payment method.
	 *
	 * @param string $payment_method Payment method.
	 *
	 * @return array
	 */
	public static function get_config_ids( $payment_method = null ) {
		self::maybe_update_active_payment_methods();

		$config_ids = [];

		$active_methods = get_option( 'pronamic_pay_active_payment_methods' );

		// Make sure active payments methods is an array.
		if ( ! is_array( $active_methods ) ) {
			return $config_ids;
		}

		// Get config IDs for payment method.
		if ( isset( $active_methods[ $payment_method ] ) ) {
			$config_ids = $active_methods[ $payment_method ];
		}

		// Get all config IDs if payment method is empty.
		if ( empty( $payment_method ) ) {
			foreach ( $active_methods as $method_config_ids ) {
				$config_ids = array_merge( $config_ids, $method_config_ids );
			}

			$config_ids = array_unique( $config_ids );
		}

		return $config_ids;
	}

	/**
	 * Check if payment method is active.
	 *
	 * @param string $payment_method Payment method.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public static function is_active( $payment_method = null ) {
		return in_array( $payment_method, self::get_active_payment_methods(), true );
	}
}
