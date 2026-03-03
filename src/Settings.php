<?php
/**
 * Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Title: WordPress iDEAL admin
 *
 * @version 2.0.5
 */
class Settings {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct and initialize settings object.
	 *
	 * @param Plugin $plugin The plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'init', $this->init( ... ) );
	}

	/**
	 * Initialize.
	 *
	 * @link https://make.wordpress.org/core/2016/10/26/registering-your-settings-in-wordpress-4-7/
	 * @link https://github.com/WordPress/WordPress/blob/4.6/wp-admin/includes/plugin.php#L1767-L1795
	 * @link https://github.com/WordPress/WordPress/blob/4.7/wp-includes/option.php#L1849-L1925
	 * @link https://github.com/WordPress/WordPress/blob/4.7/wp-includes/option.php#L1715-L1847
	 *
	 * @return void
	 */
	public function init() {
		register_setting(
			'pronamic_pay',
			'pronamic_pay_license_key',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			'pronamic_pay',
			'pronamic_pay_config_id',
			[
				'type'              => 'integer',
				'sanitize_callback' => [ self::class, 'sanitize_published_post_id' ],
			]
		);

		register_setting(
			'pronamic_pay',
			'pronamic_pay_uninstall_clear_data',
			[
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			]
		);

		\register_setting(
			'pronamic_pay',
			'pronamic_pay_debug_mode',
			[
				'type'              => 'boolean',
				'description'       => 'Setting that can be used to trigger the “debug” mode throughout Pronamic Pay.',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			]
		);

		\register_setting(
			'pronamic_pay',
			'pronamic_pay_subscriptions_processing_disabled',
			[
				'type'              => 'boolean',
				'description'       => 'Setting that can be used to disable processing of recurring payments.',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			]
		);

		/**
		 * Payment methods.
		 *
		 * @link https://developer.wordpress.org/reference/functions/register_setting/
		 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#objects
		 */
		\register_setting(
			'pronamic_pay',
			'pronamic_pay_payment_methods',
			[
				'type'         => 'object',
				'show_in_rest' => [
					'schema' => [
						'type'                 => 'object',
						'additionalProperties' => [
							'type'       => 'object',
							'properties' => [
								'status' => [
									'type' => 'string',
									'enum' => [
										'',
										'active',
										'inactive',
									],
								],
							],
						],
					],
				],
			]
		);

		$this->update_payment_method_statuses_from_option();
	}

	/**
	 * Update payment method statuses from option.
	 *
	 * @return void
	 */
	private function update_payment_method_statuses_from_option() {
		$payment_methods_option = \get_option( 'pronamic_pay_payment_methods', [] );

		if ( ! \is_array( $payment_methods_option ) ) {
			return;
		}

		foreach ( $this->plugin->get_payment_methods() as $payment_method ) {
			$payment_method_id = $payment_method->get_id();

			if ( isset( $payment_methods_option[ $payment_method_id ]['status'] ) ) {
				$payment_method->set_status( (string) $payment_methods_option[ $payment_method_id ]['status'] );
			}
		}
	}

	/**
	 * Sanitize published post ID.
	 *
	 * @param integer $value Check if the value is published post ID.
	 * @return int|null Post ID if value is published post ID, null otherwise.
	 */
	public static function sanitize_published_post_id( $value ) {
		if ( 'publish' === get_post_status( $value ) ) {
			return $value;
		}

		return null;
	}
}
