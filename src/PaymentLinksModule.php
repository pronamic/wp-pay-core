<?php
/**
 * Payment links module.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Money\Parser;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Payment links module
 *
 * @author  Reüel van der Steege
 * @since   2.3.2
 * @version 2.3.2
 */
class PaymentLinksModule {
	/**
	 * Endpoint.
	 *
	 * @var null|string
	 */
	private $endpoint;

	/**
	 * Endpoint option name.
	 *
	 * @var string
	 */
	const OPTION_ENDPOINT = 'pronamic_pay_payment_links_endpoint';

	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		$this->endpoint = get_option( self::OPTION_ENDPOINT );

		// Actions.
		\add_action( 'init', array( $this, 'init' ) );
		\add_action( 'admin_init', array( $this, 'admin_init' ), 11 );
		\add_action( 'update_option_' . self::OPTION_ENDPOINT, array( $this, 'update_option_endpoint' ), 10, 0 );

		\add_action( 'template_redirect', array( $this, 'maybe_handle_payment_link' ) );

		// Filters.
		\add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		\add_filter( 'pronamic_payment_source_text_payment_link', array( $this, 'source_text' ), 10, 2 );
		\add_filter( 'pronamic_payment_source_description_payment_link', array( $this, 'source_description' ), 10, 0 );
	}

	/**
	 * Initialize module.
	 */
	public function init() {
		/*
		 * Payment link endpoint.
		 */
		register_setting(
			'pronamic_pay',
			'pronamic_pay_payment_links_endpoint',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$this->maybe_add_rewrite_rules();
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		add_settings_section(
			'pronamic_pay_payment_links',
			/* translators: Translate 'notification' the same as in the Adyen dashboard. */
			__( 'Payment Links', 'pronamic_ideal' ),
			array( $this, 'settings_section_payment_links' ),
			'pronamic_pay'
		);

		$endpoint = $this->endpoint;

		if ( empty( $endpoint ) ) {
			$endpoint = __( 'pay', 'pronamic_ideal' );
		}

		add_settings_field(
			'pronamic_pay_payment_links_endpoint',
			__( 'Endpoint', 'pronamic_ideal' ),
			array( __CLASS__, 'input_element' ),
			'pronamic_pay',
			'pronamic_pay_payment_links',
			array(
				'description' => sprintf(
					/* translators: 1: endpoint example URL with amount, 2: endpoint example URL with amount and description */
					__( 'Use as %1$s or %2$s', 'pronamic_ideal' ),
					\home_url( $endpoint . '/{amount}/' ),
					\home_url( $endpoint . '/{amount}/{description}/' )
				),
				'label_for'   => 'pronamic_pay_payment_links_endpoint',
				'classes'     => 'regular-text',
			)
		);
	}

	/**
	 * Settings section payment links.
	 *
	 * @return void
	 */
	public function settings_section_payment_links() {
		printf(
			'<p>%s</p>',
			esc_html__(
				'Set the endpoint for payment links.',
				'pronamic_ideal'
			)
		);
	}

	/**
	 * Input element for settings field.
	 *
	 * @param array<string,string> $args Arguments.
	 * @return void
	 */
	public static function input_element( $args ) {
		$name = $args['label_for'];

		$value = get_option( $name );
		$value = strval( $value );

		printf(
			'<input name="%s" id="%s" value="%s" type="text" class="regular-text" />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( $value )
		);

		if ( ! empty( $args['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Update option endpoint.
	 *
	 * @return void
	 */
	public function update_option_endpoint() {
		$this->endpoint = \get_option( self::OPTION_ENDPOINT );

		$this->maybe_add_rewrite_rules();

		\flush_rewrite_rules();
	}

	/**
	 * Maybe add rewrite rules.
	 *
	 * @return void
	 */
	public function maybe_add_rewrite_rules() {
		$this->endpoint = \get_option( self::OPTION_ENDPOINT );

		if ( empty( $this->endpoint ) ) {
			return;
		}

		\add_rewrite_rule(
			$this->endpoint . '/([^/]+)/?$',
			'index.php?pay_amount=$matches[1]',
			'top'
		);

		\add_rewrite_rule(
			$this->endpoint . '/([^/]+)/([^/]+)/?$',
			'index.php?pay_amount=$matches[1]&pay_description=$matches[2]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'pay_amount';
		$vars[] = 'pay_description';

		return $vars;
	}

	/**
	 * Maybe handle payment link.
	 *
	 * @return void
	 */
	public function maybe_handle_payment_link() {
		$amount = \get_query_var( 'pay_amount' );

		if ( empty( $amount ) ) {
			return;
		}

		$description = \get_query_var( 'pay_description' );

		$description = \urldecode( $description );

		$this->handle_payment_link( $amount, $description );
	}

	/**
	 * Handle payment link.
	 *
	 * @param string       $amount      Amount.
	 * @param string|false $description Description.
	 *
	 * @throws \Exception Throws error on invalid amount.
	 */
	public function handle_payment_link( $amount, $description ) {
		$order_id = \date_i18n( 'YmdHis' );

		$payment = new Payment();

		$payment->config_id = get_option( 'pronamic_pay_config_id' );
		$payment->order_id  = $order_id;

		// Source.
		$payment->source    = 'payment_link';
		$payment->source_id = $order_id;

		$payment->title = sprintf(
			/* translators: %s: order id */
			__( 'Payment for payment link %s', 'pronamic_ideal' ),
			$order_id
		);

		// Amount.
		$money_parser = new Parser();

		$money = $money_parser->parse( $amount );

		$total_amount = new TaxedMoney( $money->get_value() );

		$payment->set_total_amount( $total_amount );

		// Description.
		$payment->description = __( 'Payment link', 'pronamic_ideal' );

		if ( ! empty( $description ) ) {
			$payment->description = \ucfirst( $description );
		}

		// Start payment.
		try {
			$payment = Plugin::start_payment( $payment );
		} catch ( \Exception $e ) {
			Plugin::render_exception( $e );

			exit;
		}

		// Redirect.
		$gateway = Plugin::get_gateway( $payment->get_config_id() );

		$gateway->redirect( $payment );
	}

	/**
	 * Source column
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		$text = __( 'Payment Link', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf( '%s', $payment->get_source_id() );

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @return string
	 */
	public function source_description() {
		return __( 'Payment Link', 'pronamic_ideal' );
	}
}
