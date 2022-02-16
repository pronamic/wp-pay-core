<?php
/**
 * Site health
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
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
 * @author  Reüel van der Steege
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
		add_filter( 'debug_information', array( $this, 'debug_information' ) );
		add_filter( 'site_status_tests', array( $this, 'status_tests' ) );
	}

	/**
	 * Debug information.
	 *
	 * @param array $debug_information Debug information.
	 *
	 * @return array
	 */
	public function debug_information( $debug_information ) {
		$fields = array();

		// License key.
		$fields['license_key'] = array(
			'label'   => __( 'Support license key', 'pronamic_ideal' ),
			'value'   => esc_html( get_option( 'pronamic_pay_license_key', __( 'No license key found', 'pronamic_ideal' ) ) ),
			'private' => true,
		);

		// License status.
		$fields['license_status'] = array(
			'label' => __( 'License status', 'pronamic_ideal' ),
			'value' => esc_html( $this->plugin->license_manager->get_formatted_license_status() ),
		);

		// Next scheduled license check.
		$fields['next_license_check'] = array(
			'label' => __( 'Next scheduled license check', 'pronamic_ideal' ),
			'value' => esc_html( $this->plugin->license_manager->get_formatted_next_license_check() ),
		);

		// Time.
		$fields['time'] = array(
			'label' => __( 'Time (UTC)', 'pronamic_ideal' ),
			'value' => esc_html( gmdate( __( 'Y/m/d g:i:s A', 'pronamic_ideal' ) ) ),
		);

		// OpenSSL version.
		$openssl_version = __( 'Not available', 'pronamic_ideal' );

		if ( defined( 'OPENSSL_VERSION_TEXT' ) ) {
			$openssl_version = OPENSSL_VERSION_TEXT;
		}

		$fields['openssl_version'] = array(
			'label' => __( 'OpenSSL version', 'pronamic_ideal' ),
			'value' => esc_html( $openssl_version ),
		);

		// Active plugin integrations.
		$fields['active_plugin_integrations'] = array(
			'label' => __( 'Active plugin integrations', 'pronamic_ideal' ),
			'value' => $this->get_active_plugin_integrations_debug(),
		);

		// Active gateway integrations.
		$fields['active_gateway_integrations'] = array(
			'label' => __( 'Active gateway integrations', 'pronamic_ideal' ),
			'value' => $this->get_active_gateway_integrations_debug(),
		);

		// Add debug information section.
		$debug_information['pronamic-pay'] = array(
			'label'  => __( 'Pronamic Pay', 'pronamic_ideal' ),
			'fields' => $fields,
		);

		return $debug_information;
	}

	/**
	 * Get active plugin integrations debug.
	 *
	 * @return string
	 */
	private function get_active_plugin_integrations_debug() {
		$active = array();

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
		$active = array();

		$args = array(
			'post_type' => 'pronamic_gateway',
			'nopaging'  => true,
		);

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
		// Test valid license.
		$status_tests['direct']['pronamic_pay_valid_license'] = array(
			'label' => __( 'Pronamic Pay support license key test' ),
			'test'  => array( $this, 'test_valid_license' ),
		);

		// Test minimum required WordPress version.
		$status_tests['direct']['pronamic_pay_wordpress_version'] = array(
			'label' => __( 'Pronamic Pay WordPress version test' ),
			'test'  => array( $this, 'test_wordpress_version' ),
		);

		// Test memory limit.
		$status_tests['direct']['pronamic_pay_memory_limit'] = array(
			'label' => __( 'Pronamic Pay memory limit test' ),
			'test'  => array( $this, 'test_memory_limit' ),
		);

		// Test character set.
		$status_tests['direct']['pronamic_pay_character_set'] = array(
			'label' => __( 'Pronamic Pay UTF-8 character set test' ),
			'test'  => array( $this, 'test_character_set' ),
		);

		// Test hashing algorithms.
		$status_tests['direct']['pronamic_pay_hashing_algorithms'] = array(
			'label' => __( 'Pronamic Pay hashing algorithms test' ),
			'test'  => array( $this, 'test_hashing_algorithms' ),
		);

		// Test supported extensions.
		$status_tests['direct']['pronamic_pay_extensions_support'] = array(
			'label' => __( 'Pronamic Pay extensions support test' ),
			'test'  => array( $this, 'test_extensions_support' ),
		);

		return $status_tests;
	}

	/**
	 * Test if configuration exists.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_valid_license() {
		// Good.
		$result = array(
			'test'        => 'pronamic_pay_valid_license',
			'label'       => __( 'Pronamic Pay license key is valid', 'pronamic_ideal' ),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'A valid license is required for technical support and continued plugin updates.', 'pronamic_ideal' )
			),
			'badge'       => array(
				'label' => __( 'Security' ),
				'color' => 'blue',
			),
			'status'      => 'good',
			'actions'     => '',
		);

		// Recommendation.
		if ( 'valid' !== get_option( 'pronamic_pay_license_status' ) ) {
			$result['status'] = 'recommended';
			$result['label']  = __( 'No valid license key for Pronamic Pay', 'pronamic_ideal' );

			$result['actions'] = '<p>';

			if ( '' === get_option( 'pronamic_pay_license_key' ) ) {
				$result['actions'] .= sprintf(
					'<a href="%s">%s</a> - ',
					esc_url( 'https://www.pronamic.eu/plugins/pronamic-ideal/' ),
					__( 'Purchase license' )
				);
			}

			$result['actions'] .= sprintf(
				'<a href="%s">%s</a> - ',
				add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
				__( 'License settings' )
			);

			$result['actions'] .= sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://www.pronamic.eu/account/' ),
				__( 'Check existing license' )
			);

			$result['actions'] .= '</p>';
		}

		return $result;
	}

	/**
	 * Test WordPress version.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_wordpress_version() {
		// Good.
		$result = array(
			'test'        => 'pronamic_pay_wordpress_version',
			'label'       => sprintf(
				/* translators: %s: WordPress version number */
				__( 'WordPress version is supported by Pronamic Pay (%s)', 'pronamic_ideal' ),
				get_bloginfo( 'version' )
			),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay requires at least WordPress 4.7.', 'pronamic_ideal' ) ),
			'badge'       => array(
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			),
			'status'      => 'good',
			'actions'     => '',
		);

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
		$result = array(
			'test'        => 'pronamic_pay_memory_limit',
			'label'       => sprintf(
				/* translators: %s: WordPress memory limit */
				__( 'WordPress memory limit is sufficient (%s)', 'pronamic_ideal' ),
				size_format( $memory )
			),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay recommends setting the WordPress memory limit to at least 64 MB.', 'pronamic_ideal' ) ),
			'badge'       => array(
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			),
			'status'      => 'good',
			'actions'     => '',
		);

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
		$result = array(
			'test'        => 'pronamic_pay_character_set',
			'label'       => __( 'Character encoding is set to UTF-8', 'pronamic_ideal' ),
			'description' => sprintf( '<p>%s</p>', __( 'Pronamic Pay recommends to use the UTF-8 character encoding for payments.', 'pronamic_ideal' ) ),
			'badge'       => array(
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			),
			'status'      => 'good',
			'actions'     => '',
		);

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
		$result = array(
			'test'        => 'pronamic_pay_hashing_algorithms',
			'label'       => __( 'SHA1 hashing algorithm is available', 'pronamic_ideal' ),
			'description' => sprintf( '<p>%s</p>', __( 'Payment gateways often use the SHA1 hashing algorithm, therefore Pronamic Pay advises to enable this hashing algorithm.', 'pronamic_ideal' ) ),
			'badge'       => array(
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			),
			'status'      => 'good',
			'actions'     => '',
		);

		// Recommendation.
		$algorithms = hash_algos();

		if ( ! in_array( 'sha1', $algorithms, true ) ) {
			$result['status'] = 'recommended';

			$result['label'] = __( 'SHA1 hashing algorithm is not available for Pronamic Pay', 'pronamic_ideal' );
		}

		return $result;
	}

	/**
	 * Test extensions support.
	 *
	 * @return array<string, array<string,string>|string>
	 */
	public function test_extensions_support() {
		$extensions_json_path = \dirname( $this->plugin->get_file() ) . '/other/extensions.json';

		if ( ! \is_readable( $extensions_json_path ) ) {
			return array();
		}

		$data = \file_get_contents( $extensions_json_path, true );

		if ( false === $data ) {
			return array();
		}

		// Check supported extensions.
		$extensions = \json_decode( $data );

		$supported_extensions     = array();
		$untested_plugin_versions = array();
		$outdated_plugin_versions = array();

		$active_plugins = \get_option( 'active_plugins' );
		$plugins        = \get_plugins();

		$status = 'good';

		foreach ( $plugins as $file => $plugin ) {
			// Only test active plugins.
			if ( false === \array_search( $file, $active_plugins, true ) ) {
				continue;
			}

			foreach ( $extensions as $extension ) {
				// Requires at least.
				$requires_at_least = '0.0.0';

				if ( isset( $extension->requires_at_least ) ) {
					$requires_at_least = $extension->requires_at_least;
				}

				// Tested up to.
				$tested_up_to = '0.0.0';

				if ( isset( $extension->tested_up_to ) ) {
					$tested_up_to = $extension->tested_up_to;
				}

				if ( 0 === \strcasecmp( \dirname( $file ), $extension->slug ) ) {
					$is_below_tested_version   = \version_compare( $plugin['Version'], $tested_up_to, '<=' );
					$is_above_required_version = \version_compare( $plugin['Version'], $requires_at_least, '>=' );

					$description_text = sprintf(
						'– %1$s %2$s (requires at least version %3$s, tested up to %4$s)',
						\esc_html( $extension->name ),
						\esc_html( $plugin['Version'] ),
						\esc_html( $requires_at_least ),
						\esc_html( $tested_up_to )
					);

					if ( $is_below_tested_version && $is_above_required_version ) {
						// Plugin version is between minimum and tested versions.
						$supported_extensions[] = sprintf(
							'– %1$s %2$s',
							\esc_html( $extension->name ),
							\esc_html( $plugin['Version'] )
						);
					}

					if ( ! $is_above_required_version ) {
						// Plugin version is lower than minimum required version.
						$outdated_plugin_versions[] = sprintf(
							'– %1$s %2$s (requires at least version %3$s, tested up to %4$s)',
							\esc_html( $extension->name ),
							\esc_html( $plugin['Version'] ),
							\esc_html( $requires_at_least ),
							\esc_html( $tested_up_to )
						);
					}

					// Ignore patch version if plugin and tested versions are equal.
					if ( ! $is_below_tested_version ) {
						$plugin_parts = explode( '.', $plugin['Version'] );
						$tested_parts = explode( '.', $tested_up_to );

						$num_parts = count( $tested_parts );

						if ( $num_parts >= 2 && count( $plugin_parts ) === $num_parts ) {
							$short_plugin_version = sprintf( '%s.%s', $plugin_parts[0], $plugin_parts[1] );
							$short_tested_version = sprintf( '%s.%s', $tested_parts[0], $tested_parts[1] );

							if ( $short_plugin_version === $short_tested_version ) {
								$is_below_tested_version = true;
							}
						}
					}

					if ( ! $is_below_tested_version ) {
						// Plugin version is higher than tested version.
						$untested_plugin_versions[] = sprintf(
							'– %1$s %2$s (tested up to %3$s)',
							\esc_html( $extension->name ),
							\esc_html( $plugin['Version'] ),
							\esc_html( $tested_up_to )
						);
					}
				}
			}
		}

		// Extensions list text.
		$extensions_list_text = '';

		// Untested plugin versions.
		if ( 0 !== count( $untested_plugin_versions ) ) {
			$status = 'recommended';

			$extensions_list_text .= sprintf(
				'<p>%1$s</p><p>%2$s</p>',
				__( 'Untested plugin versions:', 'pronamic_ideal' ),
				\join( '</p><p>', $untested_plugin_versions )
			);
		}

		if ( 0 !== count( $outdated_plugin_versions ) ) {
			$status = 'critical';

			$extensions_list_text .= sprintf(
				'<p>%1$s</p><p>%2$s</p>',
				__( 'Outdated unsupported plugin versions for which payments can not be processed:', 'pronamic_ideal' ),
				\join( '</p><p>', $outdated_plugin_versions )
			);
		}

		// Supported extensions.
		if ( 0 !== count( $supported_extensions ) ) {
			$extensions_list_text .= sprintf(
				'<p>%1$s</p><p>%2$s</p>',
				__( 'Supported plugin versions:', 'pronamic_ideal' ),
				\join( '</p><p>', $supported_extensions )
			);
		}

		// Result.
		$label = sprintf(
			/* translators: %s: plugin name */
			__( '%s extensions are compatible', 'pronamic_ideal' ),
			__( 'Pronamic Pay', 'pronamic_ideal' )
		);

		$description_text = __( 'Pronamic Pay uses extensions to integrate with form, booking and other e-commerce plugins. All extensions support the currently activated plugin versions.', 'pronamic_ideal' );

		if ( 'good' !== $status ) {
			$label = sprintf(
				/* translators: %s: plugin name */
				__( '%s extensions are incompatible', 'pronamic_ideal' ),
				__( 'Pronamic Pay', 'pronamic_ideal' )
			);

			if ( 0 === count( $outdated_plugin_versions ) ) {
				$label = sprintf(
					/* translators: %s: plugin name */
					__( '%s extensions might be incompatible', 'pronamic_ideal' ),
					__( 'Pronamic Pay', 'pronamic_ideal' )
				);
			}

			$description_text = __( 'Pronamic Pay uses extensions to integrate with form, booking and other e-commerce plugins. We have found that not all extensions are tested with or support the version of the currently activated plugins. Usually you can still accept payments, however if you experience payment issues it is advised to check the \'Plugins\' page for available updates.', 'pronamic_ideal' );
		}

		$result = array(
			'test'        => 'pronamic_pay_extensions_support',
			'label'       => $label,
			'description' => sprintf( '<p>%s</p><p>%s</p>', \esc_html( $description_text ), $extensions_list_text ),
			'badge'       => array(
				'label' => __( 'Payments', 'pronamic_ideal' ),
				'color' => 'blue',
			),
			'status'      => $status,
			'actions'     => '',
		);

		return $result;
	}
}
