<?php
/**
 * Settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Title: WordPress iDEAL admin
 *
 * @author  Remco Tolsma
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
		add_action( 'init', [ $this, 'init' ] );
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
				'sanitize_callback' => [ $this, 'sanitize_published_post_id' ],
			]
		);

		register_setting(
			'pronamic_pay',
			'pronamic_pay_uninstall_clear_data',
			[
				'type'    => 'boolean',
				'default' => false,
			]
		);

		register_setting(
			'pronamic_pay',
			'pronamic_pay_google_analytics_property',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		\register_setting(
			'pronamic_pay',
			'pronamic_pay_debug_mode',
			[
				'type'        => 'boolean',
				'description' => 'Setting that can be used to trigger the “debug” mode throughout Pronamic Pay.',
				'default'     => false,
			]
		);

		\register_setting(
			'pronamic_pay',
			'pronamic_pay_subscriptions_processing_disabled',
			[
				'type'        => 'boolean',
				'description' => 'Setting that can be used to disable processing of recurring payments.',
				'default'     => false,
			]
		);

		// Pages.
		$pages = $this->plugin->get_pages();

		$pages['pronamic_pay_subscription_canceled_page_id'] = __( 'Subscription Canceled', 'pronamic_ideal' );

		foreach ( $pages as $id => $label ) {
			register_setting(
				'pronamic_pay',
				$id,
				[
					'type'              => 'integer',
					'sanitize_callback' => [ $this, 'sanitize_published_post_id' ],
				]
			);
		}
	}

	/**
	 * Sanitize published post ID.
	 *
	 * @param integer $value Check if the value is published post ID.
	 * @return int|null Post ID if value is published post ID, null otherwise.
	 */
	public function sanitize_published_post_id( $value ) {
		if ( 'publish' === get_post_status( $value ) ) {
			return $value;
		}

		return null;
	}
}
