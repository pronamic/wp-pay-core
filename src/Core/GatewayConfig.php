<?php
/**
 * Gateway config
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway config
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
abstract class GatewayConfig {
	/**
	 * ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Mode
	 *
	 * @var string
	 */
	public $mode;

	/**
	 * Get gateway class.
	 *
	 * @return string
	 */
	public function get_gateway_class() {
		$class = get_class( $this );

		$namespace = substr( $class, 0, strrpos( $class, '\\' ) );

		return $namespace . '\Gateway';
	}
}
