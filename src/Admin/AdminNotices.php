<?php
/**
 * Admin Notices
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin notices
 *
 * @author Remco Tolsma
 * @version 3.7.0
 * @since 3.7.0
 */
class AdminNotices {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initializes an notices object.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/admin/class-wc-admin-notices.php
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 11 );
	}

	/**
	 * Admin notices.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.3.1/wp-admin/admin-header.php#L245-L250
	 */
	public function admin_notices() {
		// Show notices only to options managers (administrators).
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Jetpack.
		$screen = get_current_screen();

		if ( null !== $screen && 'jetpack' === $screen->parent_base ) {
			return;
		}

		// License notice.
		if ( 'valid' !== get_option( 'pronamic_pay_license_status' ) ) {
			$class = \Pronamic\WordPress\Pay\Plugin::get_number_payments() > 20 ? 'error' : 'updated';

			$license = get_option( 'pronamic_pay_license_key' );

			if ( '' === $license ) {
				$notice = sprintf(
					/* translators: 1: Pronamic Pay settings page URL, 2: Pronamic.eu plugin page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>, please <a href="%2$s" target="_blank">get your key at pronamic.eu</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/'
				);
			} else {
				$notice = sprintf(
					/* translators: 1: Pronamic Pay settings page URL, 2: Pronamic.eu plugin page URL, 3: Pronamic.eu account page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>. Please <a href="%2$s" target="_blank">get your key at pronamic.eu</a> or login to <a href="%3$s" target="_blank">check your license status</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/',
					'https://www.pronamic.eu/account/'
				);
			}

			printf(
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				wp_kses_post( $notice )
			);
		}

		// Stored notices.
		$notices = get_option( 'pronamic_pay_admin_notices', array() );

		foreach ( $notices as $name ) {
			$file = __DIR__ . '/../../views/notice-' . $name . '.php';

			if ( is_readable( $file ) ) {
				include $file;
			}
		}
	}

	/**
	 * Add a notice to show.
	 *
	 * @param string $name Name.
	 */
	public function add_notice( $name ) {
		$notices = get_option( 'pronamic_pay_admin_notices', array() );

		$notices = is_array( $notices ) ? $notices : array();

		$notices = array_unique( array_merge( $notices, array( $name ) ) );

		update_option( 'pronamic_pay_admin_notices', $notices );
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @param string $name Name.
	 */
	public function remove_notice( $name ) {
		$notices = get_option( 'pronamic_pay_admin_notices', array() );

		$notices = is_array( $notices ) ? $notices : array();

		$notices = array_diff( $notices, array( $name ) );

		update_option( 'pronamic_pay_admin_notices', $notices );
	}
}
