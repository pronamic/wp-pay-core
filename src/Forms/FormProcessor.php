<?php
/**
 * Form Processor
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

use Exception;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Pronamic\WordPress\Pay\Plugin;
use WP_Error;
use WP_User;

/**
 * Form Processor
 *
 * @author Remco Tolsma
 * @version 2.7.1
 * @since 3.7.0
 */
class FormProcessor {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initialize an form processor object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Get amount.
	 *
	 * @return Money
	 */
	private function get_amount() {
		$amount_string = \filter_input( INPUT_POST, 'pronamic_pay_amount', FILTER_SANITIZE_STRING );

		if ( 'other' === $amount_string ) {
			$amount_string = \filter_input( INPUT_POST, 'pronamic_pay_amount_other', FILTER_SANITIZE_STRING );
		}

		$number = Number::from_string( $amount_string );

		$money = new Money( $number, 'EUR' );

		return $money;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 * @throws Exception When processing form fails on creating WordPress user.
	 */
	public function init() {
		global $pronamic_pay_errors;

		$pronamic_pay_errors = array();

		// Nonce.
		if ( ! filter_has_var( INPUT_POST, 'pronamic_pay_nonce' ) ) {
			return;
		}

		$nonce = filter_input( INPUT_POST, 'pronamic_pay_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'pronamic_pay' ) ) {
			return;
		}

		// Validate.
		$valid = $this->validate();

		if ( ! $valid ) {
			return;
		}

		// Source.
		$source    = filter_input( INPUT_POST, 'pronamic_pay_source', FILTER_SANITIZE_STRING );
		$source_id = filter_input( INPUT_POST, 'pronamic_pay_source_id', FILTER_SANITIZE_STRING );

		if ( ! FormsSource::is_valid( $source ) ) {
			return;
		}

		// Config ID.
		$config_id = filter_input( INPUT_POST, 'pronamic_pay_config_id', FILTER_SANITIZE_STRING );

		if ( FormsSource::PAYMENT_FORM === $source ) {
			$config_id = get_post_meta( $source_id, '_pronamic_payment_form_config_id', true );
		}

		/*
		 * Start payment.
		 */
		$first_name = filter_input( INPUT_POST, 'pronamic_pay_first_name', FILTER_SANITIZE_STRING );
		$last_name  = filter_input( INPUT_POST, 'pronamic_pay_last_name', FILTER_SANITIZE_STRING );
		$email      = filter_input( INPUT_POST, 'pronamic_pay_email', FILTER_VALIDATE_EMAIL );
		$order_id   = (string) time();

		$description = null;

		if ( FormsSource::PAYMENT_FORM === $source ) {
			$description = get_post_meta( $source_id, '_pronamic_payment_form_description', true );

			if ( ! empty( $description ) ) {
				$description = sprintf( '%s %s', $description, $order_id );
			}
		}

		if ( empty( $description ) ) {
			$description = sprintf(
				/* translators: %s: order id */
				__( 'Payment Form %s', 'pronamic_ideal' ),
				$order_id
			);
		}

		$payment = new Payment();

		$payment->title = sprintf(
			/* translators: %s: payment data title */
			__( 'Payment for %s', 'pronamic_ideal' ),
			$description
		);

		$payment->set_config_id( $config_id );
		$payment->set_description( $description );
		$payment->set_origin_id( $source_id );

		$payment->order_id  = $order_id;
		$payment->source    = $source;
		$payment->source_id = $source_id;

		// Customer.
		$customer = array(
			'name'  => (object) array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
			),
			'email' => $email,
		);

		$customer = array_filter( $customer );

		if ( ! empty( $customer ) ) {
			$customer = Customer::from_json( (object) $customer );

			$payment->set_customer( $customer );
		}

		// Amount.
		$payment->set_total_amount( $this->get_amount() );

		// Payment lines.
		$payment->lines = new PaymentLines();

		$line = $payment->lines->new_line();

		// Set line properties.
		$line->set_id( strval( $order_id ) );
		$line->set_name( $description );
		$line->set_quantity( 1 );
		$line->set_unit_price( $payment->get_total_amount() );
		$line->set_total_amount( $payment->get_total_amount() );

		// Gateway.
		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return;
		}

		// Set default payment method if required.
		if ( $gateway->payment_method_is_required() ) {
			$payment->set_payment_method( PaymentMethods::IDEAL );
		}

		// Start payment.
		try {
			$payment = Plugin::start_payment( $payment );
		} catch ( \Exception $e ) {
			Plugin::render_exception( $e );

			exit;
		}

		// @link https://github.com/WordImpress/Give/blob/1.1/includes/payments/functions.php#L172-L178.
		// @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/wc-user-functions.php#L36-L118.
		$user = get_user_by( 'email', $email );

		if ( ! empty( $email ) && ! $user ) {
			// Make a random string for password.
			$password = wp_generate_password( 10 );

			// Make a user with the username as the email.
			$result = wp_insert_user(
				array(
					'user_login' => $email,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => 'payer',
					'first_name' => $first_name,
					'last_name'  => $last_name,
				)
			);

			if ( $result instanceof WP_Error ) {
				throw new Exception( $result->get_error_message() );
			}

			// User.
			$user = new WP_User( $result );
		}

		if ( is_object( $user ) ) {
			wp_update_post(
				array(
					'ID'          => $payment->get_id(),
					'post_author' => $user->ID,
				)
			);
		}

		$gateway->redirect( $payment );

		exit;
	}

	/**
	 * Validate.
	 *
	 * @return boolean True if valid, false otherwise.
	 */
	private function validate() {
		global $pronamic_pay_errors;

		// Amount.
		try {
			$amount = $this->get_amount();
		} catch ( \Exception $e ) {
			$pronamic_pay_errors['amount'] = __( 'Please enter a valid amount', 'pronamic_ideal' );
		}

		// First Name.
		$first_name = filter_input( INPUT_POST, 'pronamic_pay_first_name', FILTER_SANITIZE_STRING );

		if ( empty( $first_name ) ) {
			$pronamic_pay_errors['first_name'] = __( 'Please enter your first name', 'pronamic_ideal' );
		}

		// E-mail.
		$email = filter_input( INPUT_POST, 'pronamic_pay_email', FILTER_VALIDATE_EMAIL );

		if ( empty( $email ) ) {
			$pronamic_pay_errors['email'] = __( 'Please enter a valid email address', 'pronamic_ideal' );
		}

		return empty( $pronamic_pay_errors );
	}
}
