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
	public static function create( GatewayConfig $config = null ) {
		$gateway = null;

		if ( isset( $config ) ) {
			$gateway_class = $config->get_gateway_class();

			if ( class_exists( $gateway_class, false ) ) {
				$gateway = new $gateway_class( $config );
			}
		}

		return $gateway;
	}
}
