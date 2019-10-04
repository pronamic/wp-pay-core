<?php
/**
 * Gateway integrations.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use ArrayIterator;
use IteratorAggregate;
use Pronamic\WordPress\Pay\Gateways\Common\AbstractIntegration;

/**
 * Title: WordPress gateway integrations class.
 *
 * @author  Reüel van der Steege
 * @version 2.2.2
 * @since   1.0.0
 */
class GatewayIntegrations implements IteratorAggregate {
	/**
	 * Integrations.
	 *
	 * @var AbstractIntegration[]
	 */
	private $integrations = array();

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
			if ( ! ( $integration instanceof AbstractIntegration ) ) {
				continue;
			}

			$this->integrations[ $integration->get_id() ] = $integration;
		}
	}

	/**
	 * Get integration by ID.
	 *
	 * @param string $id Integration ID.
	 *
	 * @return AbstractIntegration|null
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
	 * @see IteratorAggregate::getIterator()
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator( $this->integrations );
	}
}
