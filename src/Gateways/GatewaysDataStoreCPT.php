<?php
/**
 * Gateways Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways
 */

namespace Pronamic\WordPress\Pay\Gateways;

use Pronamic\WordPress\Pay\AbstractDataStoreCPT;
use Pronamic\WordPress\Pay\Core\Gateway;

/**
 * Title: Gateways data store CPT
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 4.0.0
 * @since   4.0.0
 */
class GatewaysDataStoreCPT extends AbstractDataStoreCPT {
	/**
	 * Gateways.
	 *
	 * @var Gateway[]
	 */
	private $gateways;

	/**
	 * Construct gateways data store CPT object.
	 */
	public function __construct() {
		$this->meta_key_prefix = '_pronamic_gateway_';

		$this->gateways = [];
	}

	/**
	 * Get gateway by ID.
	 *
	 * @param int $post_id Gateway configuration post ID.
	 * @return Gateway|null
	 */
	public function get_gateway( $post_id ) {
		if ( ! isset( $this->gateways[ $post_id ] ) ) {
			// Check post type.
			$post_type = get_post_type( $post_id );

			if ( 'pronamic_gateway' !== $post_type ) {
				return null;
			}

			// Check if trashed.
			if ( 'trash' === get_post_status( $post_id ) ) {
				return null;
			}

			// Get integration.
			$gateway_id = \get_post_meta( $post_id, '_pronamic_gateway_id', true );

			if ( empty( $gateway_id ) ) {
				return null;
			}

			$integration = pronamic_pay_plugin()->gateway_integrations->get_integration( $gateway_id );

			if ( null === $integration ) {
				return null;
			}

			// Get gateway from integration for configuration post ID.
			$gateway = $integration->get_gateway( $post_id );

			if ( null !== $gateway ) {
				$this->gateways[ $post_id ] = $gateway;
			}
		}

		return $this->gateways[ $post_id ];
	}
}
