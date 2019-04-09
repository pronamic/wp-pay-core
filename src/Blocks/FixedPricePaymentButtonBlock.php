<?php
/**
 * DonationBlock.php.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Money\Parser;

/**
 * DonationBlock.php
 *
 * @author  Reüel van der Steege
 * @since   2.1.7
 * @version 2.1.7
 */
class FixedPricePaymentButtonBlock extends Block {
	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $type = 'pronamic-pay/fixed-price-payment-button';

	/**
	 * Register block.
	 *
	 * @return array
	 */
	public function register_block() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		// Register editor script.
		wp_register_script(
			'pronamic-fixed-price-payment-button-editor',
			plugins_url( '/js/block-payment-button' . $min . '.js', pronamic_pay_plugin()->get_file() ),
			array( 'wp-blocks', 'wp-components', 'wp-editor', 'wp-element' ),
			pronamic_pay_plugin()->get_version()
		);

		// Localize script.
		$this->localize_script();

		// Return block registration arguments.
		return array(
			'editor_script' => 'pronamic-fixed-price-payment-button-editor',
			'attributes'    => array(
				'amount' => array(
					'type'    => 'string',
					'default' => '0',
				),
			),
		);
	}

	/**
	 * Localize script.
	 */
	public function localize_script() {
		wp_localize_script(
			'pronamic-fixed-price-payment-button-editor',
			'pronamic_fixed_price_payment_button',
			array(
				'title'        => __( 'Fixed Price Payment Button', 'pronamic_ideal' ),
				'label_amount' => __( 'Amount', 'pronamic_ideal' ),
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

		$money_parser = new Parser();

		$settings = array(
			'amount' => $money_parser->parse( $attributes['amount'] )->get_cents(),
		);

		echo pronamic_pay_plugin()->forms_module->get_form_output( $settings ); // WPCS: XSS ok.

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
