<?php
/**
 * Abstract plugin integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay;

/**
 * Title: Abstract plugin integration
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class AbstractPluginIntegration {
	/**
	 * Get list of database update files.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.7.0/includes/class-wc-install.php#L368-L376
	 * @link https://github.com/woocommerce/woocommerce/blob/3.7.0/includes/wc-update-functions.php
	 * @return array<string>
	 */
	public function get_db_update_files() {
		return array();
	}
}
