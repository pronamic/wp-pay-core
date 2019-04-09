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

		// Gateway.
		$id = filter_input( INPUT_POST, 'pronamic_pay_form_id', FILTER_VALIDATE_INT );

		$config_id = get_post_meta( $id, '_pronamic_payment_form_config_id', true );

		$encoded_id = filter_input( INPUT_POST, 'pronamic_pay_form_id', FILTER_SANITIZE_STRING );

		$source_id = $id;

		if ( false === $id && false !== strpos( $encoded_id, '-' ) ) {
			$id_parts = explode( '-', $encoded_id );

			$form_atts = base64_decode( $id_parts[1] );

			$form_atts = json_decode( $form_atts );

			$config_id = $form_atts->config_id;

			$source_id = $id_parts[0];
		}

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		// Data.
		$data = new PaymentFormData( $source_id );

		$payment = Plugin::start( $config_id, $gateway, $data );

		$error = $gateway->get_error();

		if ( $error instanceof WP_Error ) {
			Plugin::render_errors( $error );

			exit;
		}

		// @link https://github.com/WordImpress/Give/blob/1.1/includes/payments/functions.php#L172-L178.
		// @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/wc-user-functions.php#L36-L118.
		$first_name = filter_input( INPUT_POST, 'pronamic_pay_first_name', FILTER_SANITIZE_STRING );
		$last_name  = filter_input( INPUT_POST, 'pronamic_pay_last_name', FILTER_SANITIZE_STRING );
		$email      = filter_input( INPUT_POST, 'pronamic_pay_email', FILTER_VALIDATE_EMAIL );

		$user = get_user_by( 'email', $email );

		if ( ! empty( $email ) && ! $user ) {
			// Make a random string for password.
			$password = wp_generate_password( 10 );

			// Make a user with the username as the email.
			$user_id = wp_insert_user(
				array(
					'user_login' => $email,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => 'payer',
					'first_name' => $first_name,
					'last_name'  => $last_name,
				)
			);

			// User.
			$user = new WP_User( $user_id );
		}

		if ( is_object( $user ) ) {
			wp_update_post(
				array(
					'ID'          => $payment->post->ID,
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

		// Needs validation?
		$form_id = filter_input( INPUT_POST, 'pronamic_pay_form_id', FILTER_SANITIZE_STRING );

		if ( false !== strpos( $form_id, '-' ) ) {
			return true;
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
