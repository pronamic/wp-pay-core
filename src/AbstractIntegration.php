<?php
/**
 * Abstract integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Dependencies\Dependencies;
use Pronamic\WordPress\Pay\Upgrades\Upgrades;

/**
 * Title: Abstract integration
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class AbstractIntegration {
	/**
	 * ID.
	 *
	 * @var string|null
	 */
	public $id;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	public $name;

	/**
	 * Version.
	 *
	 * @var string|null
	 */
	private $version;

	/**
	 * Deprecated boolean flag to mark an integration as deprecated.
	 *
	 * @var boolean
	 */
	public $deprecated;

	/**
	 * Dependencies.
	 *
	 * @var Dependencies
	 */
	private $dependencies;

	/**
	 * Upgrades.
	 *
	 * @var Upgrades
	 */
	private $upgrades;

	/**
	 * The name of the option we store the version of the integration in.
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
	public function __construct( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'id'                  => null,
				'name'                => null,
				'version'             => null,
				'version_option_name' => null,
				'deprecated'          => false,
			]
		);

		// ID.
		$this->set_id( $args['id'] );

		// Name.
		$this->set_name( $args['name'] );

		// Version.
		$this->set_version( $args['version'] );

		// Version option name.
		$this->set_version_option_name( $args['version_option_name'] );

		// Deprecated.
		$this->deprecated = $args['deprecated'];

		// Dependencies.
		$this->dependencies = new Dependencies();

		// Upgrades.
		$this->upgrades = new Upgrades();
	}

	/**
	 * Get ID.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param string|null $id ID.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get version.
	 *
	 * @return string|null
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Set version.
	 *
	 * @param string|null $version Version.
	 * @return void
	 */
	public function set_version( $version ) {
		$this->version = $version;
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
	 * @return bool True if dependencies are met, false otherwise.
	 */
	public function is_active() {
		return $this->dependencies->are_met();
	}

	/**
	 * Setup integration.
	 * Called from `plugins_loaded` with priority `0`, intended to be overridden.
	 *
	 * @see Plugin::plugins_loaded()
	 * @return void
	 */
	public function setup() {
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
	 * @return void
	 */
	public function set_version_option_name( $option_name ) {
		$this->version_option_name = $option_name;
	}

	/**
	 * Get version option.
	 *
	 * @return string|null
	 */
	public function get_version_option() {
		if ( null === $this->version_option_name ) {
			return null;
		}

		return \get_option( $this->version_option_name, null );
	}

	/**
	 * Update database version option.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/4.0.0/includes/class-wc-install.php#L396-L402
	 * @return void
	 */
	public function update_version_option() {
		if ( null === $this->version_option_name ) {
			return;
		}

		if ( null === $this->version ) {
			return;
		}

		\update_option( $this->version_option_name, $this->version );
	}

	/**
	 * Get upgrades.
	 *
	 * @return Upgrades
	 */
	public function get_upgrades() {
		return $this->upgrades;
	}
}
