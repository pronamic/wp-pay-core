<?php
/**
 * Form Processor
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

use Exception;
use Pronamic\WordPress\Money\Parser as MoneyParser;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Address;
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
 * @version 3.7.0
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
	 * Constructs and initalize an form processor object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize.
	 *
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

		// Gateway.
		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		/*
		 * Start payment.
		 */
		$first_name = filter_input( INPUT_POST, 'pronamic_pay_first_name', FILTER_SANITIZE_STRING );
		$last_name  = filter_input( INPUT_POST, 'pronamic_pay_last_name', FILTER_SANITIZE_STRING );
		$email      = filter_input( INPUT_POST, 'pronamic_pay_email', FILTER_VALIDATE_EMAIL );
		$order_id   = time();

		$description = sprintf(
			/* translators: %s: order id */
			__( 'Payment Form %s', 'pronamic_ideal' ),
			$order_id
		);

		$payment = new Payment();

		$payment->title = sprintf(
			/* translators: %s: payment data title */
			__( 'Payment for %s', 'pronamic_ideal' ),
			$description
		);

		$payment->description = $description;
		$payment->config_id   = $config_id;
		$payment->order_id    = $order_id;
		$payment->source      = $source;
		$payment->source_id   = $source_id;

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
		$amount_method = get_post_meta( $source_id, '_pronamic_payment_form_amount_method', true );
		$amount        = filter_input( INPUT_POST, 'pronamic_pay_amount', FILTER_SANITIZE_STRING );

		if ( 'other' === $amount ) {
			$amount = filter_input( INPUT_POST, 'pronamic_pay_amount_other', FILTER_SANITIZE_STRING );

			$money_parser = new MoneyParser();

			$amount = $money_parser->parse( $amount )->get_value();
		} elseif ( empty( $amount_method ) || in_array( $amount_method, array( FormPostType::AMOUNT_METHOD_CHOICES_ONLY, FormPostType::AMOUNT_METHOD_CHOICES_AND_INPUT ), true ) ) {
			$amount /= 100;
		}

		$payment->set_total_amount(
			new TaxedMoney(
				$amount,
				'EUR'
			)
		);

		// Payment lines.
		$payment->lines = new PaymentLines();

		$line = $payment->lines->new_line();

		// Set line properties.
		$line->set_id( strval( $order_id ) );
		$line->set_name( $description );
		$line->set_quantity( 1 );
		$line->set_unit_price( $payment->get_total_amount() );
		$line->set_total_amount( $payment->get_total_amount() );

		// Start payment.
		$payment = Plugin::start_payment( $payment, $gateway );

		$error = $gateway->get_error();

		if ( $error instanceof WP_Error ) {
			Plugin::render_errors( $error );

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
