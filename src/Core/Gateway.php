<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Util as PayUtil;
use ReflectionClass;
use WP_Error;

/**
 * Title: Gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   1.0.0
 */
abstract class Gateway {
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
	 * Config
	 *
	 * @var mixed
	 */
	protected $config;

	/**
	 * The slug of this gateway
	 *
	 * @var string
	 */
	private $slug;

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
	 * @var string
	 */
	private $payment_method;

	/**
	 * Supported features on this gateway.
	 *
	 * @since 1.3.9
	 * @var array
	 */
	protected $supports;

	/**
	 * Error
	 *
	 * @var WP_Error|null
	 */
	public $error;

	/**
	 * Constructs and initializes an gateway
	 *
	 * @param GatewayConfig $config Gateway configuration object.
	 */
	public function __construct( GatewayConfig $config ) {
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
	 * @param string $slug Unique gateway slug.
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Get the error
	 *
	 * @return WP_Error|null
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
	 * @param WP_Error|null $error WordPress error object or null.
	 */
	public function set_error( WP_Error $error = null ) {
		$this->error = $error;
	}

	/**
	 * Set the method.
	 *
	 * @param int $method HTML form or HTTP redirect method.
	 */
	public function set_method( $method ) {
		$this->method = $method;
	}

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

	/**
	 * Set has feedback.
	 *
	 * @param boolean $has_feedback Feedback from gateway indicator.
	 *
	 * @deprecated 2.0.5 Not in use anymore.
	 */
	public function set_has_feedback( $has_feedback ) {
	}

	/**
	 * Set the minimum amount required
	 *
	 * @param float $amount Minimum payment amount.
	 *
	 * @deprecated 2.0.5 Not in use anymore.
	 */
	public function set_amount_minimum( $amount ) {
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

	/**
	 * Get the credit card issuers transient.
	 *
	 * @return array|null
	 */
	public function get_transient_credit_card_issuers() {
		$issuers = null;

		// Transient name. Expected to not be SQL-escaped. Should be 45 characters or less in length.
		$transient = 'pronamic_pay_credit_card_issuers_' . $this->config->id;

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
	 * @return array
	 */
	public function get_available_payment_methods() {
		return $this->get_supported_payment_methods();
	}

	/**
	 * Get the payment methods transient
	 *
	 * @since 1.3.0
	 * @return array|null
	 */
	public function get_transient_available_payment_methods() {
		// Transient name. Expected to not be SQL-escaped. Should be 45 characters or less in length.
		$transient = 'pronamic_gateway_payment_methods_' . $this->config->id;

		$methods = get_transient( $transient );

		if ( is_wp_error( $methods ) || false === $methods ) {
			$methods = $this->get_available_payment_methods();

			if ( is_array( $methods ) ) {
				set_transient( $transient, $methods, DAY_IN_SECONDS );
			}
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

		$payment_methods = $this->get_transient_available_payment_methods();

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
	 */
	public function start( Payment $payment ) {

	}

	/**
	 * Handle subscription update.
	 *
	 * @param Payment $payment The payment to handle subscription update for.
	 */
	public function update_subscription( Payment $payment ) {

	}

	/**
	 * Handle subscription cancellation.
	 *
	 * @param Subscription $subscription The subscipriont to handle cancellation for.
	 */
	public function cancel_subscription( Subscription $subscription ) {

	}

	/**
	 * Redirect to the gateway action URL.
	 *
	 * @param Payment $payment The payment to redirect for.
	 */
	public function redirect( Payment $payment ) {
		switch ( $this->method ) {
			case self::METHOD_HTTP_REDIRECT:
				return $this->redirect_via_http( $payment );
			case self::METHOD_HTML_FORM:
				return $this->redirect_via_html( $payment );
			default:
				// No idea how to redirect to the gateway.
		}
	}

	/**
	 * Redirect via HTTP.
	 *
	 * @param Payment $payment The payment to redirect for.
	 */
	public function redirect_via_http( Payment $payment ) {
		if ( headers_sent() ) {
			$this->redirect_via_html( $payment );
		}

		// Redirect, See Other.
		// http://en.wikipedia.org/wiki/HTTP_303.
		wp_redirect( $payment->get_action_url(), 303 );

		exit;
	}

	/**
	 * Redirect via HTML.
	 *
	 * @param Payment $payment The payment to redirect for.
	 */
	public function redirect_via_html( Payment $payment ) {
		if ( headers_sent() ) {
			echo $this->get_form_html( $payment, true ); // WPCS: XSS ok.
		} else {
			// @link https://github.com/woothemes/woocommerce/blob/2.3.11/includes/class-wc-cache-helper.php.
			// @link https://www.w3-edge.com/products/w3-total-cache/.
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
						'id'       => 'pronamic_ideal_issuer_id',
						'name'     => 'pronamic_ideal_issuer_id',
						'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
						'required' => true,
						'type'     => 'select',
						'choices'  => $issuers,
					);
				}

				break;
			case PaymentMethods::CREDIT_CARD:
				$issuers = $this->get_credit_card_issuers();

				if ( ! empty( $issuers ) ) {
					$field = array(
						'id'       => 'pronamic_credit_card_issuer_id',
						'name'     => 'pronamic_credit_card_issuer_id',
						'label'    => __( 'Choose your credit card issuer', 'pronamic_ideal' ),
						'required' => true,
						'type'     => 'select',
						'choices'  => $issuers,
					);
				}

				break;
		}

		return $field;
	}

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
	 *
	 * @param string $payment_method One of the PaymentMethods constants.
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
		$fields = array();

		$issuer_field = $this->get_issuer_field();

		if ( ! empty( $issuer_field ) ) {
			$fields[] = $issuer_field;
		}

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
		$payment_method = $this->get_payment_method();

		$first_payment_method = PaymentMethods::get_first_payment_method( $payment_method );

		$this->set_payment_method( $first_payment_method );

		$fields = $this->get_input_fields();

		$this->set_payment_method( $payment_method );

		return Util::input_fields_html( $fields );
	}

	/**
	 * Get form HTML.
	 *
	 * @param Payment $payment     Payment to get form HTML for.
	 * @param bool    $auto_submit Flag to auto submit.
	 * @return string
	 */
	public function get_form_html( Payment $payment, $auto_submit = false ) {
		$form_inner = $this->get_output_html();

		$form_inner .= sprintf(
			'<input class="pronamic-pay-btn" type="submit" name="pay" value="%s" />',
			__( 'Pay', 'pronamic_ideal' )
		);

		$html = sprintf(
			'<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post" action="%s">%s</form>',
			esc_attr( $payment->get_action_url() ),
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

		return PayUtil::html_hidden_fields( $fields );
	}
}
