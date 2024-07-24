<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Fields\Field;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Refunds\Refund;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Util as PayUtil;
use WP_Error;

/**
 * Title: Gateway
 * Description:
 * Copyright: 2005-2024 Pronamic
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
	 * @var string
	 */
	const MODE_TEST = 'test';

	/**
	 * Indicator for live mode
	 *
	 * @var string
	 */
	const MODE_LIVE = 'live';

	/**
	 * The method of this gateway
	 *
	 * @var int
	 */
	private $method;

	use ModeTrait;

	/**
	 * Supported features.
	 *
	 * Possible values:
	 *  - payment_status_request      Gateway can request current payment status.
	 */
	use SupportsTrait;

	/**
	 * Payment methods.
	 *
	 * @var PaymentMethodsCollection
	 */
	protected $payment_methods;

	/**
	 * Construct gateway.
	 */
	public function __construct() {
		$this->payment_methods = new PaymentMethodsCollection();
	}

	/**
	 * Register payment method.
	 *
	 * @param PaymentMethod $payment_method Payment method.
	 * @return void
	 */
	protected function register_payment_method( PaymentMethod $payment_method ) {
		$this->payment_methods->add( $payment_method );
	}

	/**
	 * Get payment method by ID.
	 *
	 * @param string $id ID.
	 * @return PaymentMethod|null
	 */
	public function get_payment_method( $id ) {
		return $this->payment_methods->get( $id );
	}

	/**
	 * First payment method field.
	 *
	 * @param string       $payment_method_id Payment method ID.
	 * @param class-string $field_class       Field class.
	 * @return Field|null
	 */
	public function first_payment_method_field( $payment_method_id, $field_class ) {
		$payment_method = $this->get_payment_method( $payment_method_id );

		if ( null === $payment_method ) {
			return null;
		}

		$fields = $payment_method->get_fields();

		foreach ( $fields as $field ) {
			if ( $field instanceof $field_class ) {
				return $field;
			}
		}

		return null;
	}

	/**
	 * Get payment methods.
	 *
	 * @param array $args Query arguments.
	 * @return PaymentMethodsCollection
	 */
	public function get_payment_methods( array $args = [] ): PaymentMethodsCollection {
		return $this->payment_methods->query( $args );
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
	 * Get available payment methods.
	 * Intended to be overridden by gateway if active payment methods for account can be determined.
	 *
	 * @since 1.3.0
	 * @return array|null
	 * @deprecated
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
	 * @deprecated
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
	 * Start transaction/payment
	 *
	 * @param Payment $payment The payment to start up at this gateway.
	 * @return void
	 */
	public function start( Payment $payment ) {
	}

	/**
	 * Create refund.
	 *
	 * @param Refund $refund Reund.
	 * @return void
	 * @throws \Exception Throws an exception if the refund could not be processed.
	 */
	public function create_refund( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is required for function signature.
		Refund $refund
	) {
		throw new \Exception( 'Gateway does not support refunds.' );
	}

	/**
	 * Redirect to the gateway action URL.
	 *
	 * @param Payment $payment The payment to redirect for.
	 * @return void
	 * @throws \Exception Throws exception when action URL for HTTP redirect is empty.
	 */
	public function redirect( Payment $payment ) {
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
			$this->output_form( $payment );
		} else {
			Core_Util::no_cache();

			include __DIR__ . '/../../views/redirect-via-html.php';
		}

		exit;
	}

	/**
	 * Output form.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 * @throws \Exception When payment action URL is empty.
	 */
	public function output_form( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is used in include.
		Payment $payment
	) {
		include __DIR__ . '/../../views/form.php';
	}

	/**
	 * Get output inputs.
	 *
	 * @param Payment $payment Payment.
	 *
	 * @return array
	 * @since 1.2.0
	 */
	public function get_output_fields( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is required for function signature.
		Payment $payment
	) {
		return [];
	}

	/**
	 * Update status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function update_status( Payment $payment ) {
	}
}
