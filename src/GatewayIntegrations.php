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
 * @author  Reüel van der Steege
 * @version 2.0.3
 * @since   1.0.0
 */
class GatewayIntegrations {
	/**
	 * Integrations.
	 *
	 * @var array
	 */
	private $integrations = array();

	/**
	 * Construct gateway integrations.
	 *
	 * @param array $gateways Gateways.
	 */
	public function __construct( $gateways ) {
		if ( ! is_array( $gateways ) ) {
			return;
		}

		foreach ( $gateways as $gateway ) {
			$integration = null;

			if ( is_string( $gateway ) ) {
				$integration = new $gateway();
			} elseif ( is_array( $gateway ) ) {
				if ( ! isset( $gateway['class'] ) ) {
					continue;
				}

				$integration = new $gateway['class']();

				// Call callback.
				if ( isset( $gateway['callback'] ) ) {
					call_user_func( $gateway['callback'], $integration );
				}
			}

			if ( ! isset( $integration ) ) {
				continue;
			}

			$this->integrations[ $integration->get_id() ] = $integration;
		}
	}

	/**
	 * Register gateway integrations.
	 *
	 * @return array
	 */
	public function register_integrations() {
		// Register config providers.
		foreach ( $this->integrations as $integration ) {
			Core\ConfigProvider::register( $integration->get_id(), $integration->get_config_factory_class() );
		}

		return $this->integrations;
	}
}
