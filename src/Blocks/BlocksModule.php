<?php
/**
 * Editor Blocks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Number\Parser as NumberParser;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Forms\FormsSource;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use WP_Error;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @since   2.5.0
 * @version 2.1.7
 */
class BlocksModule {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		global $wp_version;

		// Initialize.
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'init', array( $this, 'register_block_types' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles' ) );

		add_filter( 'block_categories_all', array( $this, 'block_categories' ), 10, 2 );

		if ( \version_compare( $wp_version, '5.8', '<' ) ) {
			add_filter( 'block_categories', array( $this, 'block_categories' ), 10, 2 );
		}

		// Source text and description.
		add_filter( 'pronamic_payment_source_url_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_url' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . FormsSource::BLOCK_PAYMENT_FORM, array( $this, 'source_description' ), 10, 2 );
	}

	/**
	 * Block categories.
	 *
	 * @param array    $categories Block categories.
	 * @param \WP_Post $post       Post being loaded.
	 *
	 * @return array
	 */
	public function block_categories( $categories, $post ) {
		$categories[] = array(
			'slug'  => 'pronamic-pay',
			'title' => __( 'Pronamic Pay', 'pronamic-ideal' ),
			'icon'  => null,
		);

		return $categories;
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
				'title'          => _x( 'Payment Form', 'Block', 'pronamic_ideal' ),
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
				'title'           => __( 'Payment Form', 'pronamic_ideal' ),
				'render_callback' => array( $this, 'render_payment_form_block' ),
				'editor_script'   => 'pronamic-payment-form-editor',
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
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		\wp_enqueue_style( 'pronamic-pay-forms' );
	}

	/**
	 * Render payment form block.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return string
	 *
	 * @throws \Exception When output buffering is not working as expected.
	 */
	public function render_payment_form_block( $attributes = array() ) {
		// Amount.
		$amounts = array();

		if ( ! empty( $attributes['amount'] ) ) {
			try {
				$amounts[] = Number::from_mixed( $attributes['amount'] );
			} catch ( \Exception $e ) {
				/**
				 * It is possible that in the past localized numbers were stored in the amount attribute.
				 */
				try {
					$parser = new NumberParser();

					$amounts[] = $parser->parse( $attributes['amount'] );
				} catch ( \Exception $e ) {
					return '';
				}
			}
		}

		// Form settings.
		$args = array(
			'amounts'   => $amounts,
			'html_id'   => sprintf( 'pronamic-pay-payment-form-%s', get_the_ID() ),
			'source'    => FormsSource::BLOCK_PAYMENT_FORM,
			'source_id' => get_the_ID(),
		);

		// Check valid gateway.
		$config_id = get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			ob_start();

			Plugin::render_errors(
				new WP_Error(
					'pay_error',
					__( 'Unable to process payments with default gateway.', 'pronamic_ideal' )
				)
			);

			$output = ob_get_clean();

			if ( false === $output ) {
				throw new \Exception( 'Output buffering is not active.' );
			}

			return $output;
		}

		$this->enqueue_styles();

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
