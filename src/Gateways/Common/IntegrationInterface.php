<?php

namespace Pronamic\WordPress\Pay\Gateways\Common;

/**
 * Title: Integration Interface
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/GatewayInterface.php
 */
interface IntegrationInterface {
	public function get_id();

	public function get_name();

	public function get_config_factory_class();

	public function get_settings_class();
}
