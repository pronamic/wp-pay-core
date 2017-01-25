<?php

/**
 * Title: Gateway factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_GatewayFactory {
	public static function create( Pronamic_WP_Pay_GatewayConfig $config = null ) {
		$gateway = null;

		if ( isset( $config ) ) {
			$gateway_class = $config->get_gateway_class();

			if ( class_exists( $gateway_class ) ) {
				$gateway = new $gateway_class( $config );
			}
		}

		return $gateway;
	}
}
