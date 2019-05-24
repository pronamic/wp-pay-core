<?php
/**
 * Integration Interface
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay\Gateways\Common;

/**
 * Title: Integration Interface
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/GatewayInterface.php
 */
interface IntegrationInterface {
	/**
	 * Get ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get config factory class.
	 *
	 * @return string
	 */
	public function get_config_factory_class();

	/**
	 * Get settings class.
	 *
	 * @return null|string|array
	 */
	public function get_settings_class();
}
