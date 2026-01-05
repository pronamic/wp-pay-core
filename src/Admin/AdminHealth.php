<?php
/**
 * Site health
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Class SiteHealth
 *
 * @link https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @version 2.5.0
 * @since   2.2.4
 */
class AdminHealth {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Site health constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Filters.
		add_filter( 'debug_information', $this->debug_information( ... ) );
		add_filter( 'site_status_tests', $this->status_tests( ... ) );
	}

	/**
	 * Debug information.
	 *
	 * @param array $debug_information Debug information.
	 *
	 * @return array
	 */
	public function debug_information( $debug_information ) {
		$fields = [];

		// Time.
		$fields['time'] = [
			'label' => __( 'Time (UTC)', 'pronamic_ideal' ),
			'value' => esc_html( gmdate( __( 'Y/m/d g:i:s A', 'pronamic_ideal' ) ) ),
		];

		// OpenSSL version.
		$openssl_version = __( 'Not available', 'pronamic_ideal' );

		if ( defined( 'OPENSSL_VERSION_TEXT' ) ) {
			$openssl_version = OPENSSL_VERSION_TEXT;
		}

		$fields['openssl_version'] = [
			'label' => __( 'OpenSSL version', 'pronamic_ideal' ),
			'value' => esc_html( $openssl_version ),
		];

		// Active plugin integrations.
		$fields['active_plugin_integrations'] = [
			'label' => __( 'Active plugin integrations', 'pronamic_ideal' ),
			'value' => $this->get_active_plugin_integrations_debug(),
		];

		// Active gateway integrations.
		$fields['active_gateway_integrations'] = [
			'label' => __( 'Active gateway integrations', 'pronamic_ideal' ),
			'value' => $this->get_active_gateway_integrations_debug(),
		];

		// Add debug information section.
		$debug_information['pronamic-pay'] = [
			'label'  => __( 'Pronamic Pay', 'pronamic_ideal' ),
			'fields' => $fields,
		];

		return $debug_information;
	}

	/**
	 * Get active plugin integrations debug.
	 *
	 * @return string
	 */
	private function get_active_plugin_integrations_debug() {
		$active = [];

		// Check integrations.
		foreach ( $this->plugin->plugin_integrations as $integration ) {
			if ( ! $integration->is_active() ) {
				continue;
			}

			$active[] = $integration->get_name();
		}

		// Default result no active integrations.
		if ( empty( $active ) ) {
			$active[] = __( 'None', 'pronamic_ideal' );
		}

		$result = \implode( ', ', $active );

		return $result;
	}

	/**
	 * Get active gateway integrations debug.
	 *
	 * @return string
	 */
	private function get_active_gateway_integrations_debug() {
		$active = [];

		$args = [
			'post_type' => 'pronamic_gateway',
			'nopaging'  => true,
		];

		$query = new \WP_Query( $args );

		foreach ( $query->posts as $post ) {
			if ( ! \is_object( $post ) ) {
				continue;
			}

			$gateway_id = \get_post_meta( $post->ID, '_pronamic_gateway_id', true );

			$integration = $this->plugin->gateway_integrations->get_integration( $gateway_id );

			$active[] = ( null === $integration ) ? $gateway_id : $integration->get_name();
		}

		$active = \array_unique( $active );

		\sort( $active );

		// Default result no active integrations.
		if ( empty( $active ) ) {
			$active[] = __( 'None', 'pronamic_ideal' );
		}

		$result = \implode( ', ', $active );

		return $result;
	}

	/**
	 * Status tests.
	 *
	 * @param array $status_tests Status tests.
	 *
	 * @return array
	 */
	public function status_tests( $status_tests ) {
		// Test minimum required WordPress version.
		$status_tests['direct']['pronamic_pay_wordpress_version'] = [
			'label' => __( 'Pronamic Pay WordPress version test', 'pronamic_ideal' ),
			'test'  => $this->test_wordpress_version( ... ),
		];

		// Test memory limit.
		$status_tests['direct']['pronamic_pay_memory_limit'] = [
			'label' => __( 'Pronamic Pay memory limit test', 'pronamic_ideal' ),
			'test'  => $this->test_memory_limit( ... ),
		];

		// Test character set.
		$status_tests['direct']['pronamic_pay_character_set'] = [
			'label' => __( 'Pronamic Pay UTF-8 character set test', 'pronamic_ideal' ),
			'test'  => $this->test_character_set( ... ),
		];

		// Test hashing algorithms.
		$status_tests['direct']['pronamic_pay_hashing_algorithms'] = [
			'label' => __( 'Pronamic Pay hashing algorithms test', 'pronamic_ideal' ),
			'test'  => $this->test_hashing_algorithms( ... ),
		];

		return $status_tests;
	}

	/**
	 * Test WordPress version.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_wordpress_version() {
		// Good.
		$result = [
			'test'        => 'pronamic_pay_wordpress_version',
			'label'       => sprintf(
				/* translators: %s: WordPress version number */
				__( 'WordPress version is supported by Pronamic Pay (%s)', 'pronamic_ideal' ),
				get_bloginfo( 'version' )
			),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay requires at least WordPress 4.7.', 'pronamic_ideal' ) ),
			'badge'       => [
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			],
			'status'      => 'good',
			'actions'     => '',
		];

		// Recommendation.
		if ( version_compare( get_bloginfo( 'version' ), '4.7', '<' ) ) {
			$result['status'] = 'recommended';
			$result['label']  = __( 'Pronamic Pay requires at least WordPress 4.7', 'pronamic_ideal' );
		}

		return $result;
	}

	/**
	 * Test WordPress memory limit.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_memory_limit() {
		$memory_limit = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '';

		$memory = pronamic_pay_let_to_num( strval( $memory_limit ) );

		// Good.
		$result = [
			'test'        => 'pronamic_pay_memory_limit',
			'label'       => sprintf(
				/* translators: %s: WordPress memory limit */
				__( 'WordPress memory limit is sufficient (%s)', 'pronamic_ideal' ),
				size_format( $memory )
			),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay recommends setting the WordPress memory limit to at least 64 MB.', 'pronamic_ideal' ) ),
			'badge'       => [
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			],
			'status'      => 'good',
			'actions'     => '',
		];

		// Recommendation.
		if ( $memory < 67108864 ) {
			$result['status'] = 'recommended';

			$result['label'] = sprintf(
				/* translators: %s: WordPress memory limit */
				__( 'Increase WordPress memory limit (%s) to at least 64 MB', 'pronamic_ideal' ),
				size_format( $memory )
			);
		}

		return $result;
	}

	/**
	 * Test UTF-8 character set.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_character_set() {
		// Good.
		$result = [
			'test'        => 'pronamic_pay_character_set',
			'label'       => __( 'Character encoding is set to UTF-8', 'pronamic_ideal' ),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay recommends to use the UTF-8 character encoding for payments.', 'pronamic_ideal' ) ),
			'badge'       => [
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			],
			'status'      => 'good',
			'actions'     => '',
		];

		// Recommendation.
		if ( 0 !== strcasecmp( get_bloginfo( 'charset' ), 'UTF-8' ) ) {
			$result['status'] = 'recommended';

			$result['label'] = __( 'Character encoding is not set to UTF-8', 'pronamic_ideal' );
		}

		return $result;
	}

	/**
	 * Test registered hashing algorithms.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_hashing_algorithms() {
		// Good.
		$result = [
			'test'        => 'pronamic_pay_hashing_algorithms',
			'label'       => __( 'SHA1 hashing algorithm is available', 'pronamic_ideal' ),
			'description' => sprintf( '<p>%s</p>', __( 'Payment gateways often use the SHA1 hashing algorithm, therefore Pronamic Pay advises to enable this hashing algorithm.', 'pronamic_ideal' ) ),
			'badge'       => [
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			],
			'status'      => 'good',
			'actions'     => '',
		];

		// Recommendation.
		$algorithms = hash_algos();

		if ( ! in_array( 'sha1', $algorithms, true ) ) {
			$result['status'] = 'recommended';

			$result['label'] = __( 'SHA1 hashing algorithm is not available for Pronamic Pay', 'pronamic_ideal' );
		}

		return $result;
	}
}
