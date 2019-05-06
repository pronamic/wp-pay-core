<?php
/**
 * Simple payment form block.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Money\Parser;
use Pronamic\WordPress\Pay\Forms\FormsSource;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Simple payment form block.
 *
 * @author  Reüel van der Steege
 * @since   2.1.7
 * @version 2.1.7
 */
class SimplePaymentFormBlock {
	/**
	 * Register block.
	 *
	 * @return void
	 */
	public function __construct() {
		// Source text and description.
		add_filter( 'pronamic_payment_source_url_' . FormsSource::BLOCK_SIMPLE_PAYMENT_FORM, array( $this, 'source_url' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . FormsSource::BLOCK_SIMPLE_PAYMENT_FORM, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . FormsSource::BLOCK_SIMPLE_PAYMENT_FORM, array( $this, 'source_description' ), 10, 2 );

		// Register editor script.
		$min = SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'pronamic-simple-payment-form-editor',
			plugins_url( '/js/block-simple-payment-form' . $min . '.js', pronamic_pay_plugin()->get_file() ),
			array( 'wp-blocks', 'wp-components', 'wp-editor', 'wp-element' ),
			pronamic_pay_plugin()->get_version(),
			false
		);

		// Localize script.
		wp_localize_script(
			'pronamic-simple-payment-form-editor',
			'pronamic_simple_payment_form',
			array(
				'title'        => __( 'Simple Payment Form', 'pronamic_ideal' ),
				'label_amount' => __( 'Amount', 'pronamic_ideal' ),
			)
		);

		// Return block registration arguments.
		register_block_type(
			'pronamic-pay/simple-payment-form',
			array(
				'render_callback' => array( $this, 'render_block' ),
				'editor_script'   => 'pronamic-simple-payment-form-editor',
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
	 * Render block.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return string|null
	 */
	public function render_block( $attributes = array() ) {
		ob_start();

		// Amount.
		$money_parser = new Parser();

		$amount = $money_parser->parse( $attributes['amount'] );

		// Form settings.
		$args = array(
			'amount'      => $amount->get_cents(),
			'button_text' => sprintf(
				/* translators: %s: formatted amount */
				__( 'Pay %s', 'pronamic_ideal' ),
				$amount->format_i18n()
			),
			'html_id'     => sprintf( 'pronamic-pay-simple-payment-form-%s', get_the_ID() ),
			'source'      => FormsSource::BLOCK_SIMPLE_PAYMENT_FORM,
			'source_id'   => get_the_ID(),
		);

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo pronamic_pay_plugin()->forms_module->get_form_output( $args );

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
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
		$text = __( 'Simple Payment Form Block', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			get_edit_post_link( $payment->source_id ),
			strval( $payment->source_id )
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
		$text = __( 'Simple Payment Form Block', 'pronamic_ideal' ) . '<br />';

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
		return get_edit_post_link( $payment->source_id );
	}
}
