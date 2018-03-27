<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.4
 * @since 1.0.0
 */
class GatewayFactory {
	/**
	 * Created gateways.
	 *
	 * @var array
	 */
	static protected $gateways = array();

	/**
	 * Create gateway.
	 *
	 * @param GatewayConfig|null $config Gateway configuration.
	 *
	 * @return Gateway|null
	 */
	public static function create( GatewayConfig $config = null ) {
		$gateway = null;

		if ( null === $config ) {
			return $gateway;
		}

		// Return existing gateway for configuration if it exists.
		if ( isset( self::$gateways[ $config->id ] ) ) {
			return self::$gateways[ $config->id ];
		}

		// Create new gateway from gateway class.
		$gateway_class = $config->get_gateway_class();

		if ( class_exists( $gateway_class, true ) ) {
			$gateway = new $gateway_class( $config );

			self::$gateways[ $config->id ] = $gateway;
		}

		return $gateway;
	}
}
