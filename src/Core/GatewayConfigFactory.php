<?php
/**
 * Gateway config factory
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
abstract class GatewayConfigFactory {
	/**
	 * Get config with specified post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return GatewayConfig|null
	 */
	abstract public function get_config( $post_id );
}
