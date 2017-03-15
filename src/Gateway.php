<?php

/**
 * Title: Gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.12
 * @since 1.0.0
 */
abstract class Pronamic_WP_Pay_Gateway {
	/**
	 * Method indicator for an gateway wich works through an HTML form
	 *
	 * @var int
	 */
	const METHOD_HTML_FORM = 1;

	/**
	 * Method indicator for an gateway wich works through an HTTP redirect
	 *
	 * @var int
	 */
	const METHOD_HTTP_REDIRECT = 2;

	/////////////////////////////////////////////////

	/**
	 * Pronamic_Pay_Config
	 *
	 * @var int
	 */
	protected $config;

	/////////////////////////////////////////////////

	/**
	 * The slug of this gateway
	 *
	 * @var string
	 */
	private $slug;

	/////////////////////////////////////////////////

	/**
	 * The method of this gateway
	 *
	 * @var int
	 */
	private $method;

	/**
	 * Indiactor if this gateway supports feedback
	 *
	 * @var boolean
	 */
	private $has_feedback;

	/**
	 * The mimimum amount this gateway can handle
	 *
	 * @var float
	 */
	private $amount_minimum;

	/////////////////////////////////////////////////

	/**
	 * The transaction ID
	 *
	 * @var string
	 */
	private $transaction_id;

	/////////////////////////////////////////////////

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
	 * @var string
	 */
	private $payment_method;

	/////////////////////////////////////////////////

	/**
	 * Supported features on this gateway.
	 *
	 * @since unreleased
	 * @var array
	 */
	protected $supports;

	/////////////////////////////////////////////////

	/**
	 * Error
	 *
	 * @var WP_Error
	 */
	public $error;

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an gateway
	 *
	 * @param Pronamic_WP_Pay_GatewayConfig $config
	 */
	public function __construct( Pronamic_WP_Pay_GatewayConfig $config ) {
		$this->config = $config;

		/**
		 * Supported features.
		 *
		 * Possible values:
		 *  - payment_status_request      Gateway can request current payment status.
		 *  - recurring_credit_card       Recurring payments through credit card.
		 *  - recurring_direct_debit      Recurring payments through direct debit.
		 */
		$this->supports = array();
	}

	/////////////////////////////////////////////////

	/**
	 * Check if a gateway supports a given feature.
	 *
	 * @param string $feature
	 *
	 * @since 1.3.11
	 * @return bool
	 */
	public function supports( $feature ) {
		return in_array( $feature, $this->supports, true );
	}

	/////////////////////////////////////////////////

	/**
	 * Get the slug of this gateway
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Set the slug of this gateway
	 *
	 * @param unknown_type $slug
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the error
	 *
	 * @return WP_Error or null
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Has error
	 *
	 * @return boolean
	 */
	public function has_error() {
		return null !== $this->error;
	}

	/**
	 * Set error
	 *
	 * @param WP_Error $error
	 */
	public function set_error( WP_Error $error = null ) {
		$this->error = $error;
	}

	/////////////////////////////////////////////////

	/**
	 * Set the method
	 *
	 * @param int $method
	 */
	public function set_method( $method ) {
		$this->method = $method;
	}

	/////////////////////////////////////////////////

	/**
	 * Check if this gateway works trhough an HTTP redirect
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

	/////////////////////////////////////////////////

	/**
	 * Check if this gateway supports feedback
	 *
	 * @return boolean true if gateway supports feedback, false otherwise
	 */
	public function has_feedback() {
		return $this->has_feedback;
	}

	/**
	 * Set has feedback
	 *
	 * @param boolean $has_feedback
	 */
	public function set_has_feedback( $has_feedback ) {
		$this->has_feedback = $has_feedback;
	}

	/////////////////////////////////////////////////

	/**
	 * Set the minimum amount required
	 *
	 * @param float $amount
	 */
	public function set_amount_minimum( $amount ) {
		$this->amount_minimum = $amount;
	}

	/////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @return mixed an array or null
	 */
	public function get_issuers() {
		return null;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the issuers transient
	 */
	public function get_transient_issuers() {
		$issuers = null;

		// Transient name. Expected to not be SQL-escaped. Should be 45 characters or less in length.
		$transient = 'pronamic_pay_issuers_' . $this->config->id;

		$result = get_transient( $transient );

		if ( is_wp_error( $result ) || false === $result ) {
			$issuers = $this->get_issuers();

			if ( $issuers ) {
				// 60 * 60 * 24 = 24 hours = 1 day
				set_transient( $transient, $issuers, 60 * 60 * 24 );
			}
		} elseif ( is_array( $result ) ) {
			$issuers = $result;
		}

		return $issuers;
	}

	/////////////////////////////////////////////////

	/**
	 * Has valid mandate?
	 *
	 * @since 1.3.9
	 * @return boolean
	 */
	public function has_valid_mandate( $payment_method = '' ) {
		return null;
	}

	/**
	 * Get formatted date and time of first valid mandate.
	 *
	 * @since 1.3.9
	 * @return string
	 */
	public function get_first_valid_mandate_datetime( $payment_method = '' ) {
		return null;
	}

	/////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @since 1.3.0
	 * @return mixed an array or null
	 */
	public function get_payment_methods() {
		$methods_class = substr_replace( get_class( $this ), 'PaymentMethods', -7, 7 );

		if ( class_exists( $methods_class ) ) {
			$payment_methods = new ReflectionClass( $methods_class );

			$groups = array(
				array(
					'options' => $payment_methods->getConstants(),
				),
			);

			return $groups;
		}

		return null;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the payment methods transient
	 *
	 * @since 1.3.0
	 * @return mixed an array or null
	 */
	public function get_transient_payment_methods() {
		$methods = null;

		// Transient name. Expected to not be SQL-escaped. Should be 45 characters or less in length.
		$transient = 'pronamic_pay_payment_methods_' . $this->config->id;

		$result = get_transient( $transient );

		if ( is_wp_error( $result ) || false === $result ) {
			$methods = $this->get_payment_methods();

			if ( $methods ) {
				// Make sure methods are stored as array
				if ( is_string( $methods ) ) {
					$methods = array( $methods );
				}

				// 60 * 60 * 24 = 24 hours = 1 day
				set_transient( $transient, $methods, 60 * 60 * 24 );
			}
		} elseif ( is_array( $result ) ) {
			$methods = $result;
		}

		return $methods;
	}

	/////////////////////////////////////////////////

	/**
	 * Is payment method required to start transaction?
	 *
	 * @since 1.3.0
	 * @return boolean true if payment method is required, false otherwise
	 */
	public function payment_method_is_required() {
		return false;
	}

	/////////////////////////////////////////////////

	/**
	 * Get an payment method field
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public function get_payment_method_field( $other_first = false ) {
		$choices = null;

		if ( method_exists( $this, 'get_supported_payment_methods' ) ) {
			$gateway_methods = $this->get_transient_payment_methods();

			if ( is_array( $gateway_methods ) ) {
				$choices = array();

				foreach ( $this->get_supported_payment_methods() as $method_id ) {
					$choices[ $method_id ] = Pronamic_WP_Pay_PaymentMethods::get_name( $method_id );
				}

				if ( ! $this->payment_method_is_required() ) {
					if ( $other_first ) {
						$choices = array( _x( 'All available methods', 'Payment method field', 'pronamic_ideal' ) ) + $choices;
					} else {
						$choices[] = _x( 'Other', 'Payment method field', 'pronamic_ideal' );
					}
				}
			} elseif ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $gateway_methods ) {
				$choices[ Pronamic_WP_Pay_PaymentMethods::IDEAL ] = __( 'iDEAL', 'pronamic_ideal' );
			}
		}

		if ( null === $choices && ! $this->payment_method_is_required() ) {
			$choices = array(
				'' => _x( 'All available methods', 'Payment method field', 'pronamic_ideal' ),
			);
		}

		return array(
			'id'       => 'pronamic_pay_payment_method_id',
			'name'     => 'pronamic_pay_payment_method_id',
			'label'    => __( 'Choose a payment method', 'pronamic_ideal' ),
			'required' => true,
			'type'     => 'select',
			'choices'  => array( array( 'options' => $choices ) ),
		);
	}

	/////////////////////////////////////////////////

	/**
	 * Start transaction/payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function start( Pronamic_Pay_Payment $payment ) {

	}

	/////////////////////////////////////////////////

	/**
	 * Handle payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function payment( Pronamic_Pay_Payment $payment ) {

	}

	/////////////////////////////////////////////////

	/**
	 * Handle subscription update.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function update_subscription( Pronamic_Pay_Payment $payment ) {

	}

	/////////////////////////////////////////////////

	/**
	 * Handle subscription cancellation.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function cancel_subscription( Pronamic_Pay_Subscription $subscription ) {

	}

	/////////////////////////////////////////////////

	/**
	 * Redirect to the gateway action URL
	 */
	public function redirect( Pronamic_Pay_Payment $payment ) {
		switch ( $this->method ) {
			case self::METHOD_HTTP_REDIRECT :
				return $this->redirect_via_http( $payment );
			case self::METHOD_HTML_FORM :
				return $this->redirect_via_html( $payment );
			default :
				// No idea how to redirect to the gateway
		}
	}

	public function redirect_via_http( Pronamic_Pay_Payment $payment ) {
		if ( headers_sent() ) {
			$this->redirect_via_html( $payment );
		} else {
			// Redirect, See Other
			// http://en.wikipedia.org/wiki/HTTP_303
			wp_redirect( $payment->get_action_url(), 303 );
		}

		exit;
	}

	public function redirect_via_html( Pronamic_Pay_Payment $payment ) {
		if ( headers_sent() ) {
			// @codingStandardsIgnoreStart
			// No need to escape this echo
			echo $this->get_form_html( $payment, true );
			// @codingStandardsIgnoreEnd
		} else {
			// @see https://github.com/woothemes/woocommerce/blob/2.3.11/includes/class-wc-cache-helper.php
			// @see https://www.w3-edge.com/products/w3-total-cache/
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}

			if ( ! defined( 'DONOTCACHEDB' ) ) {
				define( 'DONOTCACHEDB', true );
			}

			if ( ! defined( 'DONOTMINIFY' ) ) {
				define( 'DONOTMINIFY', true );
			}

			if ( ! defined( 'DONOTCDN' ) ) {
				define( 'DONOTCDN', true );
			}

			if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
				define( 'DONOTCACHEOBJECT', true );
			}

			nocache_headers();

			include Pronamic_WP_Pay_Plugin::$dirname . '/views/redirect-via-html.php';
		}

		exit;
	}

	/////////////////////////////////////////////////
	// Input fields
	/////////////////////////////////////////////////

	/**
	 * Get an issuer field
	 *
	 * @return mixed an array or null
	 */
	public function get_issuer_field() {
		return null;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the payment method to use on this gateway.
	 *
	 * @since 1.2.3
	 * @return string One of the PaymentMethods constants.
	 */
	public function get_payment_method() {
		return $this->payment_method;
	}

	/**
	 * Set the payment method to use on this gateway.
	 *
	 * @since 1.2.3
	 * @param string $payment_method One of the PaymentMethods constants.
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_method = $payment_method;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the input fields for this gateway
	 *
	 * This function will automatically add the issuer field to the
	 * input fields array of it's not empty
	 *
	 * @return array
	 */
	public function get_input_fields() {
		$fields = array();

		$issuer_field = $this->get_issuer_field();

		if ( ! empty( $issuer_field ) ) {
			$fields[] = $issuer_field;
		}

		return $fields;
	}

	/////////////////////////////////////////////////

	/**
	 * Get the input HTML
	 *
	 * This function will convert all input fields to an HTML notation
	 *
	 * @return string
	 */
	public function get_input_html() {
		$fields = $this->get_input_fields();

		return Pronamic_WP_Pay_Util::input_fields_html( $fields );
	}

	/**
	 * Get form HTML
	 *
	 * @return string
	 */
	public function get_form_html( Pronamic_Pay_Payment $payment, $auto_submit = false ) {
		$html = '';

		// Form
		$form_inner  = '';
		$form_inner .= $this->get_output_html();
		$form_inner .= sprintf(
			'<input class="btn btn-primary" type="submit" name="pay" value="%s" />',
			__( 'Pay', 'pronamic_ideal' )
		);

		$form = sprintf(
			'<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post" action="%s">%s</form>',
			esc_attr( $payment->get_action_url() ),
			$form_inner
		);

		// HTML
		$html .= $form;

		if ( $auto_submit ) {
			$html .= '<script type="text/javascript">document.pronamic_ideal_form.submit();</script>';
		}

		return $html;
	}

	/////////////////////////////////////////////////
	// Output fields
	/////////////////////////////////////////////////

	/**
	 * Get output inputs.
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public function get_output_fields() {
		return array();
	}

	/**
	 * Get the output HTML
	 *
	 * @return string
	 */
	public function get_output_html() {
		$fields = $this->get_output_fields();

		return Pronamic_IDeal_IDeal::htmlHiddenFields( $fields );
	}
}
