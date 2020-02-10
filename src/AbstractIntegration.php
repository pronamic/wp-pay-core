<?php
/**
 * Abstract integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Dependencies\Dependencies;
use Pronamic\WordPress\Pay\Upgrades\Upgrades;

/**
 * Title: Abstract integration
 * Description:
 * Copyright: 2005-2020 Pronamic
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
	protected $id;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	protected $name;

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
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'                  => null,
				'name'                => null,
				'version_option_name' => null,
			)
		);

		// ID.
		$this->set_id( $args['id'] );
		$this->set_name( $args['name'] );

		// Dependencies.
		$this->dependencies = new Dependencies();

		// Upgrades.
		$this->upgrades = new Upgrades();

		// Version option name.
		$this->set_version_option_name( $args['version_option_name'] );
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
	 * @return void
	 */
	public function set_version_option_name( $option_name ) {
		$this->version_option_name = $option_name;
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
