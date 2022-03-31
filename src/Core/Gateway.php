<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Util as PayUtil;
use WP_Error;

/**
 * Title: Gateway
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.5.1
 * @since   1.0.0
 */
abstract class Gateway {
	/**
	 * Method indicator for an gateway which works through an HTML form
	 *
	 * @var int
	 */
	const METHOD_HTML_FORM = 1;

	/**
	 * Method indicator for an gateway which works through an HTTP redirect
	 *
	 * @var int
	 */
	const METHOD_HTTP_REDIRECT = 2;

	/**
	 * Indicator for test mode
	 *
	 * @var int
	 */
	const MODE_TEST = 'test';

	/**
	 * Indicator for live mode
	 *
	 * @var int
	 */
	const MODE_LIVE = 'live';

	/**
	 * The method of this gateway
	 *
	 * @var int
	 */
	private $method;

	/**
	 * The transaction ID
	 *
	 * @var string
	 */
	private $transaction_id;

	/**
	 * Action URL
	 *
	 * @var string
	 */
	private $action_url;

	/**
	 * Payment method to use on this gateway.
	 *
	 * @since 1.2.3
	 * @var string|null
	 */
	private $payment_method;

	/**
	 * Supported features on this gateway.
	 *
	 * @since 1.3.9
	 * @var array
	 */
	protected $supports;

	use ModeTrait;

	/**
	 * Constructs and initializes an gateway
	 *
	 * @param GatewayConfig $config Gateway configuration object.
	 */
	public function __construct( GatewayConfig $config = null ) {
		/**
		 * Supported features.
		 *
		 * Possible values:
		 *  - payment_status_request      Gateway can request current payment status.
		 *  - recurring_apple_pay         Recurring payments through Apple Pay.
		 *  - recurring_credit_card       Recurring payments through credit card.
		 *  - recurring_direct_debit      Recurring payments through direct debit.
		 */
		$this->supports = array();
	}

	/**
	 * Check if a gateway supports a given feature.
	 *
	 * @since 1.3.11
	 * @param string $feature The feature to check.
	 * @return bool True if supported, false otherwise.
	 */
	public function supports( $feature ) {
		return in_array( $feature, $this->supports, true );
	}

	/**
	 * Set the method.
	 *
	 * @param int $method HTML form or HTTP redirect method.
	 * @return void
	 */
	public function set_method( $method ) {
		$this->method = $method;
	}

	/**
	 * Check if this gateway works trough an HTTP redirect
	 *
	 * @return boolean true if an HTTP redirect is required, false otherwise
	 */
	public function is_http_redirect() {
		return self::METHOD_HTTP_REDIRECT === $this->method;
	}

	/**
	 * Check if this gateway works through an HTML form
	 *
	 * @return boolean true if an HTML form is required, false otherwise
	 */
	public function is_html_form() {
		return self::METHOD_HTML_FORM === $this->method;
	}

	/**
	 * Get iDEAL issuers.
	 *
	 * @return array
	 */
	public function get_issuers() {
		return array();
	}

	/**
	 * Get credit card issuers.
	 *
	 * @return array|null
	 */
	public function get_credit_card_issuers() {
		return null;
	}

	/**
	 * Get the iDEAL issuers transient.
	 *
	 * @return array|null
	 */
	public function get_transient_issuers() {
		$issuers = null;

		// Transient name.
		$transient = 'pronamic_pay_issuers_' . md5( serialize( $this ) );

		$result = get_transient( $transient );

		if ( is_wp_error( $result ) || false === $result ) {
			try {
				$issuers = $this->get_issuers();
			} catch ( \Exception $e ) {
				$issuers = null;
			}

			if ( ! empty( $issuers ) ) {
				// 60 * 60 * 24 = 24 hours = 1 day
				set_transient( $transient, $issuers, 60 * 60 * 24 );
			}
		} elseif ( is_array( $result ) ) {
			$issuers = $result;
		}

		return $issuers;
	}

	/**
	 * Get the credit card issuers transient.
	 *
	 * @return array|null
	 */
	public function get_transient_credit_card_issuers() {
		$issuers = null;

		// Transient name.
		$transient = 'pronamic_pay_credit_card_issuers_' . md5( serialize( $this ) );

		$result = get_transient( $transient );

		if ( is_wp_error( $result ) || false === $result ) {
			$issuers = $this->get_credit_card_issuers();

			if ( $issuers ) {
				// 60 * 60 * 24 = 24 hours = 1 day
				set_transient( $transient, $issuers, 60 * 60 * 24 );
			}
		} elseif ( is_array( $result ) ) {
			$issuers = $result;
		}

		return $issuers;
	}

	/**
	 * Custom payment redirect.
	 * Intended to be overridden by gateway.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return void
	 */
	public function payment_redirect( Payment $payment ) {
	}

	/**
	 * Get supported payment providers for gateway.
	 * Intended to be overridden by gateway.
	 *
	 * @return array
	 */
	public function get_supported_payment_methods() {
		return array();
	}

	/**
	 * Get available payment methods.
	 * Intended to be overridden by gateway if active payment methods for account can be determined.
	 *
	 * @since 1.3.0
	 * @return array|null
	 */
	public function get_available_payment_methods() {
		return null;
	}

	/**
	 * Get the payment methods transient
	 *
	 * @since 1.3.0
	 * @param bool $update_active_methods Whether active payment methods option should be updated.
	 * @return array|null
	 */
	public function get_transient_available_payment_methods( $update_active_methods = true ) {
		// Transient name.
		$transient = 'pronamic_gateway_payment_methods_' . md5( serialize( $this ) );

		$methods = get_transient( $transient );

		if ( is_wp_error( $methods ) || false === $methods ) {
			$methods = $this->get_available_payment_methods();

			if ( is_array( $methods ) ) {
				set_transient( $transient, $methods, DAY_IN_SECONDS );

				if ( $update_active_methods ) {
					PaymentMethods::update_active_payment_methods();
				}
			}
		}

		if ( empty( $methods ) ) {
			return null;
		}

		return $methods;
	}

	/**
	 * Is payment method required to start transaction?
	 *
	 * @since 1.3.0
	 * @return boolean True if payment method is required, false otherwise.
	 */
	public function payment_method_is_required() {
		return false;
	}

	/**
	 * Get payment method field options.
	 *
	 * @param bool $other_first Flag to prepend the 'Other' / 'All available methods' option.
	 * @return array
	 */
	public function get_payment_method_field_options( $other_first = false ) {
		$options = array();

		$payment_methods = array();

		try {
			$available_methods = $this->get_transient_available_payment_methods();

			if ( null !== $available_methods ) {
				$payment_methods = \array_intersect( $available_methods, $this->get_supported_payment_methods() );
			}
		} catch ( \Exception $e ) {
			$payment_methods = array();
		}

		// Use all supported payment methods as fallback.
		if ( empty( $payment_methods ) ) {
			$payment_methods = $this->get_supported_payment_methods();
		}

		// Set payment methods as options with name.
		foreach ( $payment_methods as $payment_method ) {
			$options[ $payment_method ] = PaymentMethods::get_name( $payment_method );
		}

		// Sort options by name.
		natcasesort( $options );

		// Add option to use all available payment methods.
		if ( ! $this->payment_method_is_required() ) {
			if ( $other_first ) {
				$options = array( _x( 'All available methods', 'Payment method field', 'pronamic_ideal' ) ) + $options;
			} else {
				$options[] = _x( 'Other', 'Payment method field', 'pronamic_ideal' );
			}
		}

		return $options;
	}

	/**
	 * Start transaction/payment
	 *
	 * @param Payment $payment The payment to start up at this gateway.
	 * @return void
	 */
	public function start( Payment $payment ) {

	}

	/**
	 * Handle subscription update.
	 *
	 * @param Payment $payment The payment to handle subscription update for.
	 * @return void
	 */
	public function update_subscription( Payment $payment ) {

	}

	/**
	 * Handle subscription cancellation.
	 *
	 * @param Subscription $subscription The subscription to handle cancellation for.
	 * @return void
	 */
	public function cancel_subscription( Subscription $subscription ) {

	}

	/**
	 * Redirect to the gateway action URL.
	 *
	 * @param Payment $payment The payment to redirect for.
	 * @return void
	 * @throws \Exception Throws exception when action URL for HTTP redirect is empty.
	 */
	public function redirect( Payment $payment ) {
		// Switch to user locale.
		Util::switch_to_user_locale();

		switch ( $this->method ) {
			case self::METHOD_HTTP_REDIRECT:
				$this->redirect_via_http( $payment );

				break;
			case self::METHOD_HTML_FORM:
				$this->redirect_via_html( $payment );

				break;
			default:
				// No idea how to redirect to the gateway.
		}
	}

	/**
	 * Redirect via HTTP.
	 *
	 * @param Payment $payment The payment to redirect for.
	 * @return void
	 * @throws \Exception When payment action URL is empty.
	 */
	public function redirect_via_http( Payment $payment ) {
		if ( headers_sent() ) {
			$this->redirect_via_html( $payment );
		}

		$action_url = $payment->get_action_url();

		if ( empty( $action_url ) ) {
			throw new \Exception( 'Action URL is empty, can not redirect.' );
		}

		// Redirect, See Other.
		// https://en.wikipedia.org/wiki/HTTP_303.
		wp_redirect( $action_url, 303 );

		exit;
	}

	/**
	 * Redirect via HTML.
	 *
	 * @param Payment $payment The payment to redirect for.
	 * @return void
	 */
	public function redirect_via_html( Payment $payment ) {
		if ( headers_sent() ) {
			/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
			echo $this->get_form_html( $payment, true );
		} else {
			Core_Util::no_cache();

			include Plugin::$dirname . '/views/redirect-via-html.php';
		}

		exit;
	}

	/**
	 * Get an issuer field
	 *
	 * @return array|null
	 */
	public function get_issuer_field() {
		$field = null;

		$payment_method = $this->get_payment_method();

		// Set default payment method if needed.
		if ( null === $payment_method && $this->payment_method_is_required() ) {
			$payment_method = PaymentMethods::IDEAL;
		}

		// No issuers without payment method.
		if ( empty( $payment_method ) ) {
			return $field;
		}

		// Set issuer field for payment method.
		switch ( $payment_method ) {
			case PaymentMethods::IDEAL:
				$issuers = $this->get_transient_issuers();

				if ( ! empty( $issuers ) ) {
					$field = array(
						'id'      => 'pronamic_ideal_issuer_id',
						'name'    => 'pronamic_ideal_issuer_id',
						'label'   => __( 'Choose your bank', 'pronamic_ideal' ),
						'type'    => 'select',
						'choices' => $issuers,
					);
				}

				break;
			case PaymentMethods::CREDIT_CARD:
				$issuers = $this->get_credit_card_issuers();

				if ( ! empty( $issuers ) ) {
					$field = array(
						'id'      => 'pronamic_credit_card_issuer_id',
						'name'    => 'pronamic_credit_card_issuer_id',
						'label'   => __( 'Choose your credit card issuer', 'pronamic_ideal' ),
						'type'    => 'select',
						'choices' => $issuers,
					);
				}

				break;
		}

		return $field;
	}

	/**
	 * Get a gender field.
	 *
	 * @return array|null
	 */
	public function get_gender_field() {
		$payment_methods = array(
			PaymentMethods::AFTERPAY_NL,
			PaymentMethods::FOCUM,
			PaymentMethods::IN3,
			PaymentMethods::KLARNA_PAY_LATER,
			PaymentMethods::SPRAYPAY,
		);

		$payment_method = $this->get_payment_method();

		// Only add field for specified payment methods.
		if ( ! in_array( $payment_method, $payment_methods, true ) ) {
			return null;
		}

		// Return field.
		return array(
			'id'      => 'pronamic_pay_gender',
			'name'    => 'pronamic_pay_gender',
			'label'   => __( 'Gender', 'pronamic_ideal' ),
			'type'    => 'select',
			'choices' => array(
				array(
					'options' => array(
						''  => __( '— Select gender —', 'pronamic_ideal' ),
						'F' => __( 'Female', 'pronamic_ideal' ),
						'M' => __( 'Male', 'pronamic_ideal' ),
					),
				),
			),
		);
	}

	/**
	 * Get a date of birth field.
	 *
	 * @return array|null
	 */
	public function get_birth_date_field() {
		$payment_methods = array(
			PaymentMethods::AFTERPAY_NL,
			PaymentMethods::FOCUM,
			PaymentMethods::IN3,
			PaymentMethods::KLARNA_PAY_LATER,
			PaymentMethods::SPRAYPAY,
		);

		$payment_method = $this->get_payment_method();

		// Only add field for specified payment methods.
		if ( ! in_array( $payment_method, $payment_methods, true ) ) {
			return null;
		}

		// Return field.
		return array(
			'id'    => 'pronamic_pay_birth_date',
			'name'  => 'pronamic_pay_birth_date',
			'label' => __( 'Date of birth', 'pronamic_ideal' ),
			'type'  => 'date',
			'max'   => gmdate( 'Y-m-d' ),
		);
	}

	/**
	 * Get a consumer bank details name field.
	 *
	 * @return array|null
	 */
	public function get_consumer_bank_details_name_field() {
		$payment_methods = array(
			PaymentMethods::DIRECT_DEBIT,
		);

		$payment_method = $this->get_payment_method();

		// Only add field for specified payment methods.
		if ( ! in_array( $payment_method, $payment_methods, true ) ) {
			return null;
		}

		// Return field.
		return array(
			'id'    => 'pronamic_pay_consumer_bank_details_name',
			'name'  => 'pronamic_pay_consumer_bank_details_name',
			'label' => __( 'Account holder name', 'pronamic_ideal' ),
			'type'  => 'text',
		);
	}

	/**
	 * Get a consumer bank details IBAN field.
	 *
	 * @return array|null
	 */
	public function get_consumer_bank_details_iban_field() {
		$payment_methods = array(
			PaymentMethods::DIRECT_DEBIT,
		);

		$payment_method = $this->get_payment_method();

		// Only add field for specified payment methods.
		if ( ! in_array( $payment_method, $payment_methods, true ) ) {
			return null;
		}

		// Return field.
		return array(
			'id'    => 'pronamic_pay_consumer_bank_details_iban',
			'name'  => 'pronamic_pay_consumer_bank_details_iban',
			'label' => __( 'IBAN', 'pronamic_ideal' ),
			'type'  => 'text',
		);
	}

	/**
	 * Get the payment method to use on this gateway.
	 *
	 * @since 1.2.3
	 * @return string|null One of the PaymentMethods constants.
	 */
	public function get_payment_method() {
		return $this->payment_method;
	}

	/**
	 * Set the payment method to use on this gateway.
	 *
	 * @since 1.2.3
	 *
	 * @param string|null $payment_method One of the PaymentMethods constants.
	 * @return void
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_method = $payment_method;
	}

	/**
	 * Get the input fields for this gateway
	 *
	 * This function will automatically add the issuer field to the
	 * input fields array of it's not empty
	 *
	 * @return array
	 */
	public function get_input_fields() {
		// Make sure to use input for first payment method.
		$payment_method = $this->get_payment_method();

		$first_payment_method = PaymentMethods::get_first_payment_method( $payment_method );

		$this->set_payment_method( $first_payment_method );

		/*
		 * Fields.
		 */
		$fields = array();

		// Issuer field.
		$fields[] = $this->get_issuer_field();

		// Gender field.
		$fields[] = $this->get_gender_field();

		// Birth date field.
		$fields[] = $this->get_birth_date_field();

		// Consumer bank details name field.
		$fields[] = $this->get_consumer_bank_details_name_field();

		// Consumer bank details IBAN field.
		$fields[] = $this->get_consumer_bank_details_iban_field();

		// Remove empty input fields.
		$fields = array_filter( $fields );

		// Restore payment method to original value.
		$this->set_payment_method( $payment_method );

		return $fields;
	}

	/**
	 * Get the input HTML
	 *
	 * This function will convert all input fields to an HTML notation
	 *
	 * @return string
	 */
	public function get_input_html() {
		$fields = $this->get_input_fields();

		return Util::input_fields_html( $fields );
	}

	/**
	 * Get form HTML.
	 *
	 * @param Payment $payment     Payment to get form HTML for.
	 * @param bool    $auto_submit Flag to auto submit.
	 * @return string
	 * @throws \Exception When payment action URL is empty.
	 */
	public function get_form_html( Payment $payment, $auto_submit = false ) {
		$form_inner = $this->get_output_html( $payment );

		$form_inner .= sprintf(
			'<input class="pronamic-pay-btn" type="submit" name="pay" value="%s" />',
			__( 'Pay', 'pronamic_ideal' )
		);

		$action_url = $payment->get_action_url();

		if ( empty( $action_url ) ) {
			throw new \Exception( 'Action URL is empty, can not get form HTML.' );
		}

		$html = sprintf(
			'<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post" action="%s">%s</form>',
			esc_attr( $action_url ),
			$form_inner
		);

		if ( $auto_submit ) {
			$html .= '<script type="text/javascript">document.pronamic_ideal_form.submit();</script>';
		}

		return $html;
	}

	/**
	 * Get output inputs.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_output_fields( Payment $payment ) {
		return array();
	}

	/**
	 * Get the output HTML
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function get_output_html( Payment $payment ) {
		$fields = $this->get_output_fields( $payment );

		return PayUtil::html_hidden_fields( $fields );
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function update_status( Payment $payment ) {

	}

	/**
	 * Create invoice.
	 *
	 * @param Payment $payment Payment.
	 * @return bool
	 */
	public function create_invoice( $payment ) {
		return false;
	}

	/**
	 * Cancel reservation.
	 *
	 * @param Payment $payment Payment.
	 * @return bool
	 */
	public function cancel_reservation( $payment ) {
		return false;
	}
}
