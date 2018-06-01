<?php
/**
 * Gateway settings
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.0
 */
abstract class GatewaySettings {
	/**
	 * Return data for Pronamic\WordPress\Pay\Admin\GatewayPostType::save_post().
	 *
	 * @param array $data Data.
	 *
	 * @return array
	 */
	public function save_post( $data ) {
		return $data;
	}
}
