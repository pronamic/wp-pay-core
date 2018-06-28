<?php
/**
 * Gateway integrations.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Title: WordPress gateway integrations class.
 *
 * @author Reüel van der Steege
 * @version 1.0
 */
class GatewayIntegrations {
	/**
	 * Register gateway integrations.
	 *
	 * @return array
	 */
	public function register_integrations() {
		$integrations = $this->get_integrations();

		// Register config providers.
		foreach ( $integrations as $integration ) {
			Core\ConfigProvider::register( $integration->get_id(), $integration->get_config_factory_class() );
		}

		return $integrations;
	}

	/**
	 * Get gateway integrations.
	 *
	 * @return array
	 */
	private function get_integrations() {
		$integrations = array();

		// Set integrations.
		foreach ( Plugin::$gateways as $integration ) {
			$integrations[ $integration->get_id() ] = $integration;
		}

		return $integrations;
	}
}
