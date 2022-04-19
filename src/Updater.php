<?php
/**
 * Updater
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use WP_Error;

/**
 * Updater class
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.0.1
 */
class Updater {
	/**
	 * Plugins filter callback.
	 *
	 * @var callable|null
	 */
	private $plugins_filter_callback;

	/**
	 * Instance of this class.
	 *
	 * @since 1.1.0
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Construct updater.
	 *
	 * @param callable|null $plugins_filter_callback Plugins filter callback.
	 */
	private function __construct( $plugins_filter_callback ) {
		$this->plugins_filter_callback = $plugins_filter_callback;

		\add_filter( 'http_response', array( $this, 'http_response' ), 10, 3 );
	}

	/**
	 * HTTP Response.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.5/wp-includes/class-http.php#L437-L446
	 * @param array  $response    HTTP response.
	 * @param array  $parsed_args HTTP request arguments.
	 * @param string $url         The request URL.
	 * @return array
	 */
	public function http_response( $response, $parsed_args, $url ) {
		if ( ! \array_key_exists( 'method', $parsed_args ) ) {
			return $response;
		}

		if ( 'POST' !== $parsed_args['method'] ) {
			return $response;
		}

		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/' ) ) {
			$response = $this->extend_response_with_pronamic( $response, $parsed_args, 'plugins' );
		}

		return $response;
	}

	/**
	 * Extends WordPress.org API repsonse with Pronamic API response.
	 *
	 * @param array  $response    HTTP response.
	 * @param array  $parsed_args HTTP request arguments.
	 * @param string $type        Type.
	 * @return array
	 */
	public function extend_response_with_pronamic( $response, $parsed_args, $type ) {
		$data = \json_decode( \wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) ) {
			return $response;
		}

		$pronamic_data = $this->request_plugins_update_check( $parsed_args );

		if ( false === $pronamic_data ) {
			return $response;
		}

		if ( ! array_key_exists( $type, $data ) ) {
			$data[ $type ] = array();
		}

		if ( \is_array( $pronamic_data[ $type ] ) ) {
			$data[ $type ] = array_merge( $data[ $type ], $pronamic_data[ $type ] );
		}

		$response['body'] = \wp_json_encode( $data );

		return $response;
	}

	/**
	 * Remote post.
	 *
	 * @param string $url         URL to retrieve.
	 * @param array  $args        Request arguments.
	 * @param array  $parsed_args Parsed request arguments.
	 * @return array|WP_Error
	 */
	private function remote_post( $url, $args, $parsed_args ) {
		$keys = array(
			'timeout',
			'user-agent',
			'headers',
		);

		foreach ( $keys as $key ) {
			if ( \array_key_exists( $key, $parsed_args ) ) {
				$args[ $key ] = $parsed_args[ $key ];
			}
		}

		return \wp_remote_post( $url, $args );
	}

	/**
	 * Request plugins update check.
	 *
	 * @param array $parsed_args HTTP request arguments.
	 * @return array|false
	 */
	private function request_plugins_update_check( $parsed_args ) {
		$plugins = $this->get_plugins();

		if ( 0 === \count( $plugins ) ) {
			return false;
		}

		$raw_response = $this->remote_post(
			'https://api.pronamic.eu/plugins/update-check/1.2/',
			array(
				'body' => array(
					'plugins' => \wp_json_encode( $plugins ),
				),
			),
			$parsed_args
		);

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( \is_wp_error( $raw_response ) || '200' != \wp_remote_retrieve_response_code( $raw_response ) ) {
			return false;
		}

		$response = \json_decode( \wp_remote_retrieve_body( $raw_response ), true );

		if ( ! \is_array( $response ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get plugins.
	 *
	 * @link https://github.com/pronamic/wp-pronamic-client/blob/ce45ff5b1cde51aa3959750f2a03ad76c3be0463/includes/functions.php#L3-L25
	 * @return array
	 */
	private function get_plugins() {
		if ( ! function_exists( '\get_plugins' ) ) {
			return array();
		}

		$plugins = \get_plugins();

		if ( null !== $this->plugins_filter_callback ) {
			$plugins = \array_filter( $plugins, $this->plugins_filter_callback );
		}

		return $plugins;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @param callable $plugins_filter_callback Plugins filter callback.
	 * @return self A single instance of this class.
	 */
	public static function instance( $plugins_filter_callback = null ) {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self( $plugins_filter_callback );
		}

		return self::$instance;
	}
}
