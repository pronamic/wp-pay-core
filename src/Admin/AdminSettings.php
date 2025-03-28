<?php
/**
 * Admin Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Util;

/**
 * WordPress iDEAL admin
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.0.0
 */
class AdminSettings {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initialize an admin object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Settings - General.
		add_settings_section(
			'pronamic_pay_general',
			__( 'General', 'pronamic_ideal' ),
			function (): void {
			},
			'pronamic_pay'
		);

		// Default Config.
		add_settings_field(
			'pronamic_pay_config_id',
			__( 'Default Gateway', 'pronamic_ideal' ),
			[ $this, 'input_page' ],
			'pronamic_pay',
			'pronamic_pay_general',
			[
				'post_type'        => 'pronamic_gateway',
				'show_option_none' => __( '— Select a gateway —', 'pronamic_ideal' ),
				'label_for'        => 'pronamic_pay_config_id',
			]
		);

		// Remove data on uninstall.
		add_settings_field(
			'pronamic_pay_uninstall_clear_data',
			__( 'Remove Data', 'pronamic_ideal' ),
			[ $this, 'input_checkbox' ],
			'pronamic_pay',
			'pronamic_pay_general',
			[
				'legend'      => __( 'Remove Data', 'pronamic_ideal' ),
				'description' => __( 'Remove all plugin data on uninstall', 'pronamic_ideal' ),
				'label_for'   => 'pronamic_pay_uninstall_clear_data',
				'classes'     => 'regular-text',
				'type'        => 'checkbox',
			]
		);

		// Debug mode.
		$debug_mode_args = [
			'legend'      => \__( 'Debug Mode', 'pronamic_ideal' ),
			'description' => \__( 'Enable debug mode', 'pronamic_ideal' ),
			'label_for'   => 'pronamic_pay_debug_mode',
			'type'        => 'checkbox',
		];

		if ( defined( 'PRONAMIC_PAY_DEBUG' ) && PRONAMIC_PAY_DEBUG ) {
			$debug_mode_args['value']    = true;
			$debug_mode_args['disabled'] = \disabled( PRONAMIC_PAY_DEBUG, true, false );
		}

		\add_settings_field(
			'pronamic_pay_debug_mode',
			\__( 'Debug Mode', 'pronamic_ideal' ),
			[ $this, 'input_checkbox' ],
			'pronamic_pay',
			'pronamic_pay_general',
			$debug_mode_args
		);

		\add_settings_field(
			'pronamic_pay_subscriptions_processing_disabled',
			\__( 'Disable Recurring Payments', 'pronamic_ideal' ),
			[ $this, 'input_checkbox' ],
			'pronamic_pay',
			'pronamic_pay_general',
			[
				'legend'      => \__( 'Disable starting recurring payments at gateway', 'pronamic_ideal' ),
				'description' => \__( 'Disable starting recurring payments at gateway', 'pronamic_ideal' ),
				'label_for'   => 'pronamic_pay_subscriptions_processing_disabled',
				'type'        => 'checkbox',
			]
		);

		if ( version_compare( $this->plugin->get_version(), '10', '>=' ) ) {
			// Settings - Payment Methods.
			\add_settings_section(
				'pronamic_pay_payment_methods',
				\__( 'Payment Methods', 'pronamic_ideal' ),
				function (): void {
				},
				'pronamic_pay'
			);

			foreach ( $this->plugin->get_payment_methods() as $payment_method ) {
				$id = 'pronamic_pay_payment_method_' . $payment_method->get_id() . '_status';

				add_settings_field(
					$id,
					$payment_method->get_name(),
					[ $this, 'select_payment_method_status' ],
					'pronamic_pay',
					'pronamic_pay_payment_methods',
					[
						'label_for' => $id,
					]
				);
			}
		}
	}

	/**
	 * Input text.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_element( $args ) {
		$defaults = [
			'type'        => 'text',
			'classes'     => 'regular-text',
			'description' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		$name  = $args['label_for'];
		$value = get_option( $name );

		$element = new Element(
			'input',
			[
				'name'  => $name,
				'id'    => $name,
				'type'  => $args['type'],
				'class' => $args['classes'],
				'value' => $value,
			]
		);

		$element->output();

		if ( ! empty( $args['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Input checkbox.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9.1/wp-admin/options-writing.php#L60-L68
	 * @link https://github.com/WordPress/WordPress/blob/4.9.1/wp-admin/options-reading.php#L110-L141
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_checkbox( $args ) {
		$id     = $args['label_for'];
		$name   = $args['label_for'];
		$value  = \array_key_exists( 'value', $args ) ? $args['value'] : get_option( $name );
		$legend = $args['legend'];

		echo '<fieldset>';

		printf(
			'<legend class="screen-reader-text"><span>%s</span></legend>',
			esc_html( $legend )
		);

		printf(
			'<label for="%s">',
			esc_attr( $id )
		);

		$attributes = [
			'name'  => $name,
			'id'    => $id,
			'type'  => 'checkbox',
			'value' => '1',
		];

		if ( $value ) {
			$attributes['checked'] = 'checked';
		}

		if ( \array_key_exists( 'disabled', $args ) && $args['disabled'] ) {
			$attributes['disabled'] = 'disabled';
		}

		$element = new Element( 'input', $attributes );

		$element->output();

		echo esc_html( $args['description'] );

		echo '</label>';

		echo '</fieldset>';
	}

	/**
	 * Input page.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_page( $args ) {
		$name = $args['label_for'];

		$selected = get_option( $name, '' );

		if ( false === $selected ) {
			$selected = '';
		}

		wp_dropdown_pages(
			[
				'name'             => esc_attr( $name ),
				'post_type'        => esc_attr( isset( $args['post_type'] ) ? $args['post_type'] : 'page' ),
				'selected'         => esc_attr( $selected ),
				'show_option_none' => esc_attr( isset( $args['show_option_none'] ) ? $args['show_option_none'] : __( '— Select a page —', 'pronamic_ideal' ) ),
				'class'            => 'regular-text',
			]
		);
	}

	/**
	 * Select payment method status.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function select_payment_method_status( $args ) {
		$name = $args['label_for'];

		$selected = get_option( $name, '' );

		$statuses = [
			''         => '',
			'active'   => \__( 'Active', 'pronamic_ideal' ),
			'inactive' => \__( 'Inactive', 'pronamic_ideal' ),
		];

		\printf(
			'<select id="%s" name="%s">',
			\esc_attr( $name ),
			\esc_attr( $name )
		);

		foreach ( $statuses as $status => $label ) {
			\printf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $status ),
				\selected( $status, $selected, false ),
				\esc_html( $label )
			);
		}

		echo '</select>';
	}
}
