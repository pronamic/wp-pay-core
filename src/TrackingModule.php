<?php
/**
 * Tracking module.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Tracking module
 *
 * @author  Reüel van der Steege
 * @since   2.2.6
 * @version 2.2.6
 */
class TrackingModule {
	/**
	 * URL parameters.
	 *
	 * @var null|array
	 */
	private $parameters;

	/**
	 * Get tracking URL.
	 *
	 * @param string $url URL to add tracking parameters to.
	 * @return string
	 */
	public function get_tracking_url( $url ) {
		if ( null === $this->parameters ) {
			$this->build_parameters();
		}

		return \add_query_arg( $this->parameters, $url );
	}

	/**
	 * Build URL parameters.
	 *
	 * @return void
	 */
	private function build_parameters() {
		// General parameters.
		$params = [
			'locale' => \get_locale(),
			'php'    => \str_replace( PHP_EXTRA_VERSION, '', \strval( \phpversion() ) ),
		];

		// Add extensions parameters.
		$plugins = \get_plugins();

		$extensions = \array_merge(
			[
				'pronamic-ideal',
				'contact-form-7',
				'wpforms',
			],
			$this->get_supported_extensions()
		);

		foreach ( $plugins as $slug => $plugin ) {
			foreach ( $extensions as $extension ) {
				if ( false === \stristr( $slug, $extension ) ) {
					continue;
				}

				// Add plugin to URL parameters.
				$slug = dirname( $slug );

				$params[ $slug ] = $plugin['Version'];
			}
		}

		// Set parameters.
		$this->parameters = $params;
	}

	/**
	 * Get supported extensions.
	 *
	 * @return array
	 */
	public function get_supported_extensions() {
		$extensions = [];

		$extensions_json_path = \dirname( \pronamic_pay_plugin()->get_file() ) . '/other/extensions.json';

		if ( \is_readable( $extensions_json_path ) ) {
			$data = \file_get_contents( $extensions_json_path, true );

			if ( false !== $data ) {
				$data = \json_decode( $data );

				if ( null !== $data ) {
					$extensions = \wp_list_pluck( $data, 'slug' );
				}
			}
		}

		return $extensions;
	}
}
