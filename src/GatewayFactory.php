<?php

/**
 * Title: Gateway factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.3.2
 * @since 1.0.0
 */
class Pronamic_WP_Pay_GatewayFactory {
	private static $gateways = array();

	public static function register( $config_class, $gateway_class ) {
		self::$gateways[ $config_class ] = $gateway_class;
	}

	public static function create( Pronamic_WP_Pay_GatewayConfig $config = null ) {
		$gateway = null;

		if ( isset( $config ) ) {
			$config_class = get_class( $config );

			// @since 1.3.1
			if ( ! isset( self::$gateways[ $config_class ] ) ) {
				$config_class = get_parent_class( $config_class );
			}

			// @since 1.3.2
			if ( ! isset( self::$gateways[ $config_class ] ) ) {
				$config_class = get_parent_class( $config_class );
			}

			// @since 1.3.2
			if ( ! isset( self::$gateways[ $config_class ] ) ) {
				$config_class = get_parent_class( $config_class );
			}

			if ( isset( self::$gateways[ $config_class ] ) ) {
				$gateway_class = self::$gateways[ $config_class ];

				if ( class_exists( $gateway_class ) ) {
					$gateway = new $gateway_class( $config );
				}
			}
		}

		return $gateway;
	}
}
