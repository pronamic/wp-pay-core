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
use Pronamic\WordPress\Pay\Plugin;

/**
 * DonationBlock.php
 *
 * @author  Reüel van der Steege
 * @since   x.x.x
 * @version x.x.x
 */
class PaymentButtonBlock extends Block {
	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $type = 'pronamic-pay/payment-button';

	/**
	 * Scripts.
	 *
	 * @return array
	 */
	public function scripts() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		return array(
			array(
				'handle'   => 'pronamic_pay_payment_button_block',
				'src'      => plugins_url( '/js/block-payment-button' . $min . '.js', pronamic_pay_plugin()->get_file() ),
				'deps'     => array( 'wp-blocks', 'wp-element', 'wp-components' ),
				'callback' => array( $this, 'localize_script' ),
			),
		);
	}

	/**
	 * Localize script.
	 *
	 * @param array $script Script to localize.
	 */
	public function localize_script( $script = array() ) {
		$configurations = array();

		foreach ( Plugin::get_config_select_options() as $config_id => $title ) {
			$configurations[] = array(
				'label' => $title,
				'value' => $config_id,
			);
		}

		wp_localize_script(
			$script['handle'],
			'pronamic_payment_button',
			array(
				'title'               => __( 'Payment Button', 'pronamic_ideal' ),
				'configurations'      => $configurations,
				'label_configuration' => __( 'Configuration', 'pronamic_ideal' ),
				'label_amount'        => __( 'Amount', 'pronamic_ideal' ),
				'pay_now'             => __( 'Pay Now', 'pronamic_ideal' ),
			)
		);
	}

	/**
	 * Render block on frontend.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string|null
	 */
	public function render_block( $args = array() ) {
		ob_start();

		$money_parser = new Parser();

		$settings = array(
			'config_id' => $args['config'],
			'amount'    => $money_parser->parse( $args['amount'] )->get_cents(),
		);

		echo pronamic_pay_plugin()->forms_module->get_form_output( $settings ); // WPCS: XSS ok.

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Preview block.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function preview_block( $args = array() ) {
		ob_start();

		echo esc_html( sprintf( 'Hello, this is a %s block preview!', $this->get_type() ) );

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
