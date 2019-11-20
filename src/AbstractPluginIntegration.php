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

use Pronamic\WordPress\Pay\Dependencies\Dependencies;

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
	 * Dependencies.
	 *
	 * @var Dependencies
	 */
	private $dependencies;

	/**
	 * The name of the option we store the version of the plugin integration in.
	 *
	 * @link https://github.com/WordPress/WordPress/search?q=option_name&unscoped_q=option_name
	 * @var string|null
	 */
	private $version_option_name;

	/**
	 * Construct.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'version_option_name' => null,
			)
		);

		// Dependencies.
		$this->dependencies = new Dependencies();

		// Version option name.
		$this->set_version_option_name( $args['version_option_name'] );
	}

	/**
	 * Get the dependencies of this plugin integration.
	 *
	 * @return Dependencies
	 */
	public function get_dependencies() {
		return $this->dependencies;
	}

	/**
	 * Is active.
	 *
	 * @return bool True if dependencies are met, false othwerise.
	 */
	public function is_active() {
		return $this->dependencies->are_met();
	}

	/**
	 * Get version option name.
	 *
	 * @return string|null
	 */
	public function get_version_option_name() {
		return $this->version_option_name;
	}

	/**
	 * Set version option name.
	 *
	 * @param string $option_name Option name.
	 */
	public function set_version_option_name( $option_name ) {
		$this->version_option_name = $option_name;
	}

	/**
	 * Get list of update files.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.7.0/includes/class-wc-install.php#L368-L376
	 * @link https://github.com/woocommerce/woocommerce/blob/3.7.0/includes/wc-update-functions.php
	 * @return array<array<string>>
	 */
	public function get_update_files() {
		return array();
	}
}
