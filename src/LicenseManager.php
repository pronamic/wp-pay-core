<?php
/**
 * License Manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;
use Pronamic\WordPress\Html\Element;
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
	 * Instance of this class.
	 *
	 * @since 4.7.1
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct license manager.
	 */
	public function __construct() {
		// Actions.
		\add_action( 'admin_init', [ $this, 'admin_init' ], 9 );
		\add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		\add_action( 'pronamic_pay_license_check', [ $this, 'license_check_event' ] );

		// Filters.
		\add_filter( sprintf( 'pre_update_option_%s', 'pronamic_pay_license_key' ), [ $this, 'pre_update_option_license_key' ], 10, 2 );
		\add_filter( 'debug_information', [ $this, 'debug_information' ], 15 );
		\add_filter( 'site_status_tests', [ $this, 'site_status_tests' ] );
	}

	/**
	 * Get home URL.
	 *
	 * @return string
	 */
	private function get_home_url() {
		/**
		 * We use the `get_option( 'home' )` here and not `home_url()` to
		 * bypass the `home_url` filter. The WPML plugin hooks into the
		 * `home_url` filter and this causes the notice to be displayed
		 * unnecessarily. That's why we decided to compare on the
		 * unfiltered home URL directly from the options.
		 *
		 * @link https://github.com/pronamic/wp-pay-core/issues/136
		 */
		return \get_option( 'home' );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		// License key setting.
		\add_settings_field(
			'pronamic_pay_license_key',
			\__( 'Support License Key', 'pronamic_ideal' ),
			[ $this, 'input_license_key' ],
			'pronamic_pay',
			'pronamic_pay_general',
			[
				'label_for' => 'pronamic_pay_license_key',
				'classes'   => 'regular-text code',
			]
		);

		// License check.
		if ( ! \wp_next_scheduled( 'pronamic_pay_license_check' ) ) {
			\wp_schedule_event( time(), 'daily', 'pronamic_pay_license_check' );
		}
	}

	/**
	 * Input license key.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_license_key( $args ) {
		/**
		 * Perform license check.
		 */
		\do_action( 'pronamic_pay_license_check' );

		$args = \wp_parse_args(
			$args,
			[
				'type'    => 'text',
				'classes' => 'regular-text',
			]
		);

		$name = $args['label_for'];

		$element = new Element(
			'input',
			[
				'name'  => $name,
				'id'    => $name,
				'type'  => $args['type'],
				'class' => $args['classes'],
				'value' => \get_option( $name ),
			]
		);

		$element->output();

		$status = \get_option( 'pronamic_pay_license_status' );

		$icon = 'valid' === $status ? 'yes' : 'no';

		printf( '<span class="dashicons dashicons-%s" style="vertical-align: text-bottom;"></span>', \esc_attr( $icon ) );
	}

	/**
	 * Admin notices.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.2.4/wp-admin/options.php#L205-L218
	 * @link https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/2.4.2/includes/class-edd-license-handler.php#L309-L369
	 * @return void
	 */
	public function admin_notices() {
		// Show notices only to options managers (administrators).
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// License activation notice.
		$data = get_transient( 'pronamic_pay_license_data' );

		if ( $data ) {
			include __DIR__ . '/../views/notice-license.php';

			delete_transient( 'pronamic_pay_license_data' );
		}

		// License status notice.
		if ( 'valid' !== get_option( 'pronamic_pay_license_status' ) ) {
			$class = Plugin::get_number_payments() > 20 ? 'error' : 'updated';

			$license = get_option( 'pronamic_pay_license_key' );

			if ( '' === $license ) {
				$notice = sprintf(
				/* translators: 1: Pronamic Pay settings page URL, 2: The pronamicpay.com plugin page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>, please <a href="%2$s" target="_blank">get your key at pronamicpay.com</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamicpay.com/'
				);
			} else {
				$notice = sprintf(
				/* translators: 1: Pronamic Pay settings page URL, 2: The pronamicpay.com plugin page URL, 3: The pronamic.shop account page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>. Please <a href="%2$s" target="_blank">get your key at pronamicpay.com</a> or login to <a href="%3$s" target="_blank">check your license status</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamicpay.com/',
					'https://www.pronamic.shop/'
				);
			}

			printf(
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				wp_kses_post( $notice )
			);
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
		$args = [
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => $this->get_home_url(),
		];

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/check/1.0/' ),
			[
				'timeout' => 20,
			]
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
		$args = [
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => $this->get_home_url(),
		];

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/deactivate/1.0/' ),
			[
				'timeout' => 20,
			]
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
		$args = [
			'license' => $license,
			'name'    => 'Pronamic Pay',
			'url'     => $this->get_home_url(),
		];

		$args = urlencode_deep( $args );

		$response = wp_remote_get(
			add_query_arg( $args, 'https://api.pronamic.eu/licenses/activate/1.0/' ),
			[
				'timeout' => 20,
			]
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

	/**
	 * Site Health debug information.
	 *
	 * @param array $debug_information Debug information.
	 * @return array
	 */
	public function debug_information( $debug_information ) {
		// Add debug information section.
		if ( ! \array_key_exists( 'pronamic-pay', $debug_information ) ) {
			$debug_information['pronamic-pay'] = [
				'label'  => __( 'Pronamic Pay', 'pronamic_ideal' ),
				'fields' => [],
			];
		}

		$fields = [
			// License key.
			'license_key'        => [
				'label'   => __( 'Support license key', 'pronamic_ideal' ),
				'value'   => esc_html( get_option( 'pronamic_pay_license_key', __( 'No license key found', 'pronamic_ideal' ) ) ),
				'private' => true,
			],

			// License status.
			'license_status'     => [
				'label' => __( 'License status', 'pronamic_ideal' ),
				'value' => esc_html( $this->get_formatted_license_status() ),
			],

			// Next scheduled license check.
			'next_license_check' => [
				'label' => __( 'Next scheduled license check', 'pronamic_ideal' ),
				'value' => esc_html( $this->get_formatted_next_license_check() ),
			],
		];

		if ( \array_key_exists( 'fields', $debug_information['pronamic-pay'] ) ) {
			$fields = \array_merge( $fields, $debug_information['pronamic-pay']['fields'] );
		}

		$debug_information['pronamic-pay']['fields'] = $fields;

		return $debug_information;
	}


	/**
	 * Site status tests.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/site_status_tests/
	 * @param array $status_tests Status tests.
	 * @return array
	 */
	public function site_status_tests( $status_tests ) {
		// Test valid license.
		$status_tests['direct']['pronamic_pay_valid_license'] = [
			'label' => \__( 'Pronamic Pay support license key test', 'pronamic_ideal' ),
			'test'  => [ $this, 'test_valid_license' ],
		];

		return $status_tests;
	}

	/**
	 * Test if configuration exists.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_valid_license() {
		// Good.
		$result = [
			'test'        => 'pronamic_pay_valid_license',
			'label'       => \__( 'Pronamic Pay license key is valid', 'pronamic_ideal' ),
			'description' => \sprintf(
				'<p>%s</p>',
				\__( 'A valid license is required for technical support and continued plugin updates.', 'pronamic_ideal' )
			),
			'badge'       => [
				'label' => \__( 'Security', 'pronamic_ideal' ),
				'color' => 'blue',
			],
			'status'      => 'good',
			'actions'     => '',
		];

		// Recommendation.
		if ( 'valid' !== \get_option( 'pronamic_pay_license_status' ) ) {
			$result['status'] = 'recommended';
			$result['label']  = \__( 'No valid license key for Pronamic Pay', 'pronamic_ideal' );

			$result['actions'] = '<p>';

			if ( '' === \get_option( 'pronamic_pay_license_key' ) ) {
				$result['actions'] .= \sprintf(
					'<a href="%s">%s</a> - ',
					\esc_url( 'https://www.pronamic.eu/plugins/pronamic-ideal/' ),
					\__( 'Purchase license', 'pronamic_ideal' )
				);
			}

			$result['actions'] .= \sprintf(
				'<a href="%s">%s</a> - ',
				\add_query_arg( 'page', 'pronamic_pay_settings', \get_admin_url( null, 'admin.php' ) ),
				\__( 'License settings', 'pronamic_ideal' )
			);

			$result['actions'] .= \sprintf(
				'<a href="%s">%s</a>',
				\esc_url( 'https://www.pronamic.eu/account/' ),
				\__( 'Check existing license', 'pronamic_ideal' )
			);

			$result['actions'] .= '</p>';
		}

		return $result;
	}
}
