<?php

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Gateway config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class GatewayConfigFactory {
	abstract public function get_config( $post_id );
}
