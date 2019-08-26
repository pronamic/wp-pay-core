<?php
/**
 * Forms Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

use Exception;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Forms Module
 *
 * @author Remco Tolsma
 * @version 3.7.0
 * @since 3.7.0
 */
class FormsModule {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Form post type.
	 *
	 * @var FormPostType
	 */
	private $form_post_type;

	/**
	 * Form processor.
	 *
	 * @var FormProcessor
	 */
	private $processor;

	/**
	 * Form scripts.
	 *
	 * @var FormScripts
	 */
	private $scripts;

	/**
	 * Form shortcode.
	 *
	 * @var FormShortcode
	 */
	private $shortcode;

	/**
	 * Constructs and initalize a forms module object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Form Post Type.
		$this->form_post_type = new FormPostType( $plugin );

		// Processor.
		$this->processor = new FormProcessor( $plugin );

		// Scripts.
		$this->scripts = new FormScripts( $plugin );

		// Shortcode.
		$this->shortcode = new FormShortcode( $this );

		// Actions.
		add_filter( 'the_content', array( $this, 'maybe_add_form_to_content' ) );

		add_filter( 'pronamic_payment_source_url_' . FormsSource::PAYMENT_FORM, array( $this, 'source_url' ), 10, 2 );
		add_filter( 'pronamic_payment_source_text_' . FormsSource::PAYMENT_FORM, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . FormsSource::PAYMENT_FORM, array( $this, 'source_description' ), 10, 2 );
	}

	/**
	 * Maybe add form to content.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/the_content/
	 * @param string $content Post content to maybe extend with a payment form.
	 * @return string
	 */
	public function maybe_add_form_to_content( $content ) {
		if ( is_singular( 'pronamic_pay_form' ) && 'pronamic_pay_form' === get_post_type() ) {
			$content .= $this->get_form_output_by_id( (int) get_the_ID() );
		}

		return $content;
	}

	/**
	 * Get form output.
	 *
	 * @param int $id Form ID or form settings.
	 *
	 * @return string
	 */
	public function get_form_output_by_id( $id ) {
		$args = array(
			'amount_method' => get_post_meta( $id, '_pronamic_payment_form_amount_method', true ),
			'amounts'       => get_post_meta( $id, '_pronamic_payment_form_amount_choices', true ),
			'button_text'   => get_post_meta( $id, '_pronamic_payment_form_button_text', true ),
			'config_id'     => get_post_meta( $id, '_pronamic_payment_form_config_id', true ),
			'html_id'       => sprintf( 'pronamic-pay-form-%s', $id ),
			'source'        => FormsSource::PAYMENT_FORM,
			'source_id'     => $id,
			'title'         => ( is_singular( 'pronamic_pay_form' ) ? null : get_the_title( $id ) ),
		);

		return $this->get_form_output( $args );
	}

	/**
	 * Get form output.
	 *
	 * @param array $args Form settings.
	 *
	 * @return string
	 *
	 * @throws Exception When output buffering is not working as expected.
	 */
	public function get_form_output( $args ) {
		if ( ! is_array( $args ) ) {
			return '';
		}

		// Amount(s).
		$amounts = array( 0 );

		if ( isset( $args['amounts'] ) && is_array( $args['amounts'] ) ) {
			$amounts = $args['amounts'];
		} elseif ( isset( $args['amount'] ) ) {
			$amounts = array( $args['amount'] );
		}

		// Form settings.
		$defaults = array(
			'amount_method' => FormPostType::AMOUNT_METHOD_INPUT_FIXED,
			'amounts'       => $amounts,
			'button_text'   => __( 'Pay Now', 'pronamic_ideal' ),
			'config_id'     => get_option( 'pronamic_pay_config_id' ),
			'form_id'       => null,
			'html_id'       => 'pronamic-pay-form',
			'source'        => null,
			'source_id'     => null,
			'title'         => null,
		);

		$settings = wp_parse_args( $args, $defaults );

		// Load template.
		ob_start();

		include __DIR__ . '/../../views/form.php';

		$output = ob_get_clean();

		if ( false === $output ) {
			throw new Exception( 'Output buffering is not active.' );
		}

		return $output;
	}

	/**
	 * Source text filter.
	 *
	 * @param string  $text    The source text to filter.
	 * @param Payment $payment The payment for the specified source text.
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		$text = __( 'Payment Form', 'pronamic_ideal' );

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
	 * @return string
	 */
	public function source_description( $text, Payment $payment ) {
		$text = __( 'Payment Form', 'pronamic_ideal' ) . '<br />';

		return $text;
	}

	/**
	 * Source URL.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_edit_post_link/
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
