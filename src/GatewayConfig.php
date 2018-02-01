<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway config
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class GatewayConfig {
	public $id;

	public $mode;

	public function get_gateway_class() {
		$class = get_class( $this );

		$namespace = substr( $class, 0, strrpos( $class, '\\' ) );

		return $namespace . '\Gateway';
	}
}
