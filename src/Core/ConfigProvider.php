<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Config provider
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class ConfigProvider {
	private static $factories = array();

	public static function register( $name, $class_name ) {
		self::$factories[ $name ] = $class_name;
	}

	public static function get_config( $name, $post_id ) {
		$config = null;

		if ( isset( self::$factories[ $name ] ) ) {
			$class_name = self::$factories[ $name ];

			if ( class_exists( $class_name ) ) {
				$factory = new $class_name();

				if ( $factory instanceof GatewayConfigFactory ) {
					$config = $factory->get_config( $post_id );

					$config->id = $post_id;
				}
			}
		}

		return $config;
	}
}
