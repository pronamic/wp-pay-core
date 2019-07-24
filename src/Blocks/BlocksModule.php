<?php
/**
 * Editor Blocks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Exception;
use Pronamic\WordPress\Money\Parser;
use Pronamic\WordPress\Pay\Forms\FormsSource;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use WP_Error;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @since   2.1.7
 * @version 2.1.7
 */
class BlocksModule {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		// Initialize.
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'init', array( $this, 'register_block_types' ) );

		// Source text and description.
		add_filter( 'pronamic_payment_source_url_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_url' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_description' ), 10, 2 );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register_scripts() {
		// Register editor script.
		$min = SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'pronamic-payment-form-editor',
			plugins_url( '/js/dist/block-payment-form' . $min . '.js', dirname( dirname( __FILE__ ) ) ),
			array( 'wp-blocks', 'wp-components', 'wp-editor', 'wp-element' ),
			pronamic_pay_plugin()->get_version(),
			false
		);

		// Localize script.
		wp_localize_script(
			'pronamic-payment-form-editor',
			'pronamic_payment_form',
			array(
				'title'          => __( 'Payment Form', 'pronamic_ideal' ),
				'label_add_form' => __( 'Add form', 'pronamic_ideal' ),
				'label_amount'   => __( 'Amount', 'pronamic_ideal' ),
			)
		);
	}

	/**
	 * Register block types.
	 *
	 * @return void
	 */
	public function register_block_types() {
		register_block_type(
			'pronamic-pay/payment-form',
			array(
				'render_callback' => array( $this, 'render_payment_form_block' ),
				'editor_script'   => 'pronamic-payment-form-editor',
				'style'           => array( 'pronamic-pay-forms' ),
				'attributes'      => array(
					'amount' => array(
						'type'    => 'string',
						'default' => '0',
					),
				),
			)
		);
	}

	/**
	 * Render payment form block.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return string
	 *
	 * @throws Exception When output buffering is not working as expected.
	 */
	public function render_payment_form_block( $attributes = array() ) {
		// Amount.
		$money_parser = new Parser();

		$amount = '';

		if ( ! empty( $attributes['amount'] ) ) {
			$amount = $money_parser->parse( $attributes['amount'] );
		}

		// Form settings.
		$args = array(
			'amount'    => $amount,
			'html_id'   => sprintf( 'pronamic-pay-payment-form-%s', get_the_ID() ),
			'source'    => FormsSource::BLOCK_PAYMENT_FORM,
			'source_id' => get_the_ID(),
		);

		// Check valid gateway.
		$config_id = get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) :
			ob_start();

			Plugin::render_errors(
				new WP_Error(
					'pay_error',
					__( 'Unable to process payments with default gateway.', 'pronamic_ideal' )
				)
			);

			$output = ob_get_clean();

			if ( false === $output ) {
				throw new Exception( 'Output buffering is not active.' );
			}

			return $output;
		endif;

		// Return form output.
		return pronamic_pay_plugin()->forms_module->get_form_output( $args );
	}

	/**
	 * Source text filter.
	 *
	 * @param string  $text    The source text to filter.
	 * @param Payment $payment The payment for the specified source text.
	 *
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		$text = __( 'Payment Form Block', 'pronamic_ideal' );

		if ( empty( $payment->source_id ) ) {
			return $text;
		}

		$link = get_edit_post_link( intval( $payment->source_id ) );

		if ( null === $link ) {
			return $text;
		}

		$text .= '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html( strval( $payment->source_id ) )
		);

		return $text;
	}

	/**
	 * Source description filter.
	 *
	 * @param string  $text    The source text to filter.
	 * @param Payment $payment The payment for the specified source text.
	 *
	 * @return string
	 */
	public function source_description( $text, Payment $payment ) {
		$text = __( 'Payment Form Block', 'pronamic_ideal' ) . '<br />';

		return $text;
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function source_url( $url, Payment $payment ) {
		if ( empty( $payment->source_id ) ) {
			return $url;
		}

		$link = get_edit_post_link( intval( $payment->source_id ) );

		if ( null === $link ) {
			return $url;
		}

		return $link;
	}
}
