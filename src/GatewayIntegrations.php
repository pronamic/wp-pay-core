<?php
/**
 * Gateway integrations.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Title: WordPress gateway integrations class.
 *
 * @author     Reüel van der Steege
 * @version    2.2.6
 * @since      1.0.0
 * @implements IteratorAggregate<string, AbstractGatewayIntegration>
 */
class GatewayIntegrations implements IteratorAggregate {
	/**
	 * Integrations.
	 *
	 * @var AbstractGatewayIntegration[]
	 */
	private $integrations = [];

	/**
	 * Construct gateway integrations.
	 *
	 * @param array $integrations Integrations.
	 */
	public function __construct( $integrations ) {
		foreach ( $integrations as $integration ) {
			if ( is_string( $integration ) && class_exists( $integration ) ) {
				$integration = new $integration();
			}

			/**
			 * Invalid integrations are ignored for now.
			 *
			 * @todo Consider throwing exception?
			 */
			if ( ! ( $integration instanceof AbstractGatewayIntegration ) ) {
				continue;
			}

			/**
			 * Only add active integrations.
			 */
			if ( $integration->is_active() ) {
				$this->integrations[ $integration->get_id() ] = $integration;
			}
		}
	}

	/**
	 * Get integration by ID.
	 *
	 * @param string $id Integration ID.
	 * @return AbstractGatewayIntegration|null
	 */
	public function get_integration( $id ) {
		if ( array_key_exists( $id, $this->integrations ) ) {
			return $this->integrations[ $id ];
		}

		return null;
	}

	/**
	 * Get iterator.
	 *
	 * @return \ArrayIterator<string, AbstractGatewayIntegration>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->integrations );
	}
}
