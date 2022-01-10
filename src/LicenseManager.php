<?php
/**
 * License Manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use WP_Error;

/**
 * License Manager
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.0.1
 */
class LicenseManager {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct and initialize an license manager object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'pronamic_pay_license_check', array( $this, 'license_check_event' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Filters.
		add_filter( sprintf( 'pre_update_option_%s', 'pronamic_pay_license_key' ), array( $this, 'pre_update_option_license_key' ), 10, 2 );
	}

	/**
	 * Admin notices.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.2.4/wp-admin/options.php#L205-L218
	 * @link https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/2.4.2/includes/class-edd-license-handler.php#L309-L369
	 * @return void
	 */
	public function admin_notices() {
		$data = get_transient( 'pronamic_pay_license_data' );

		if ( $data ) {
			include __DIR__ . '/../views/notice-license.php';

			delete_transient( 'pronamic_pay_license_data' );
		}
	}

	/**
	 * Pre update option 'pronamic_pay_license_key'.
	 *
	 * @param string $newvalue New value.
	 * @param string $oldvalue Old value.
	 * @return string
	 */
	public function pre_update_option_license_key( $newvalue, $oldvalue ) {
		$newvalue = trim( $newvalue );

		// Deactivate license on changed value.
		if ( $newvalue !== $oldvalue ) {
			delete_option( 'pronamic_pay_license_status' );

			if ( ! empty( $oldvalue ) ) {
				$this->deactivate_license( $oldvalue );
			}
		}

		delete_transient( 'pronamic_pay_license_data' );

		// Always try to activate the new license, it could be deactivated.
		if ( ! empty( $newvalue ) ) {
			$this->activate_license( $newvalue );
		}

		// Schedule daily license check.
		$time = time() + DAY_IN_SECONDS;

		wp_clear_scheduled_hook( 'pronamic_pay_license_check' );

		wp_schedule_event( $time, 'daily', 'pronamic_pay_license_check' );

		// Get and update license status.
		$old_status = \get_option( 'pronamic_pay_license_status' );

		$this->check_license( $newvalue );

		$new_status = \get_option( 'pronamic_pay_license_status' );

		// Don't show activated notice if option value and valid status have not changed.
		if ( $oldvalue === $newvalue && $old_status === $new_status && 'valid' === $new_status ) {
			delete_transient( 'pronamic_pay_license_data' );
		}

		return $newvalue;
	}

	/**
	 * License check event.
	 *
	 * @return void
	 */
	public function license_check_event() {
		$license = get_option( 'pronamic_pay_license_key' );
		$license = strval( $license );

		$this->check_license( $license );
	}

	/**
	 * Request license status.
	 *
	 * @param string $license License.
	 * @return string
	 */
	private function request_license_status( $license ) {
		if ( empty( $license ) ) {
			return 'invalid';
		}

		// Request.
		$args = array(
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => home_url(),
		);

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/check/1.0/' ),
			array(
				'timeout' => 20,
			)
		);

		// On errors we give benefit of the doubt.
		if ( $response instanceof WP_Error ) {
			return 'valid';
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( is_object( $data ) && isset( $data->license ) ) {
			return $data->license;
		}

		return 'valid';
	}

	/**
	 * Check license.
	 *
	 * @param string $license License.
	 * @return void
	 */
	public function check_license( $license ) {
		$status = $this->request_license_status( $license );

		update_option( 'pronamic_pay_license_status', $status );
	}

	/**
	 * Deactivate license.
	 *
	 * @param string $license License to deactivate.
	 * @return void
	 */
	public function deactivate_license( $license ) {
		$args = array(
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => home_url(),
		);

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/deactivate/1.0/' ),
			array(
				'timeout' => 20,
			)
		);
	}

	/**
	 * Activate license.
	 *
	 * @param string $license License to activate.
	 * @return void
	 */
	public function activate_license( $license ) {
		// Request.
		$args = array(
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => home_url(),
		);

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/activate/1.0/' ),
			array(
				'timeout' => 20,
			)
		);

		if ( $response instanceof WP_Error ) {
			return;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $data ) {
			set_transient( 'pronamic_pay_license_data', $data, 30 );
		}
	}

	/**
	 * Get license status text.
	 *
	 * @return string
	 */
	public function get_formatted_license_status() {
		$license_status = get_option( 'pronamic_pay_license_status' );

		switch ( $license_status ) {
			case 'valid':
				return __( 'Valid', 'pronamic_ideal' );

			case 'invalid':
				return __( 'Invalid', 'pronamic_ideal' );

			case 'site_inactive':
				return __( 'Site Inactive', 'pronamic_ideal' );
		}

		return $license_status;
	}

	/**
	 * Get next scheduled license check text.
	 *
	 * @return string
	 */
	public function get_formatted_next_license_check() {
		$next_license_check = esc_html__( 'Not scheduled', 'pronamic_ideal' );

		$timestamp = wp_next_scheduled( 'pronamic_pay_license_check' );

		if ( false !== $timestamp ) {
			try {
				$date = new DateTime( '@' . $timestamp, new DateTimeZone( 'UTC' ) );

				$next_license_check = $date->format_i18n();
			} catch ( \Exception $e ) {
				return $next_license_check;
			}
		}

		return $next_license_check;
	}
}
