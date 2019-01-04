<?php
/**
 * Gateway config factory
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway config factory
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.8
 * @since   1.0.0
 */
abstract class GatewayConfigFactory {
	/**
	 * Get config with specified post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return GatewayConfig|null
	 */
	abstract public function get_config( $post_id );

	/**
	 * Get meta value.
	 *
	 * @since 2.0.8
	 *
	 * @param string|int $post_id Post ID.
	 * @param string     $key     Shortened meta key.
	 *
	 * @return string
	 */
	protected function get_meta( $post_id, $key ) {
		if ( empty( $post_id ) || empty( $key ) ) {
			return '';
		}

		$post_id = intval( $post_id );

		$meta_key = sprintf( '_pronamic_gateway_%s', $key );

		// Get post meta.
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		if ( false === $meta_value ) {
			$meta_value = '';
		}

		return $meta_value;
	}
}
