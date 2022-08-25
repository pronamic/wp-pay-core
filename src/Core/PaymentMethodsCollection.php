<?php
/**
 * Payment methods collection
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use ArrayIterator;
use IteratorAggregate;

/**
 * Payment methods collection class
 */
class PaymentMethodsCollection implements IteratorAggregate {
	private $items = [];

	public function add( PaymentMethod $payment_method ) {
		$id = $payment_method->get_id();

		$this->items[ $id ] = $payment_method;
	}

	/**
	 * Get payment method by ID.
	 *
	 * @param string $id ID.
	 * @return PaymentMethod|null
	 */
	public function get( $id ) {
		if ( array_key_exists( $id, $this->items ) ) {
			return $this->items[ $id ];
		}

		return null;
	}

	public function query( $args ) {
		$items = $this->items;

		if ( \array_key_exists( 'status', $args ) ) {
			$status_list = \wp_parse_list( $args['status'] );

			$items = array_filter(
				$items,
				function( $payment_method ) use ( $status_list ) {
					return \in_array( $payment_method->get_status(), $status_list, true );
				}
			);
		}

		if ( \array_key_exists( 'supports', $args ) ) {
			$feature = $args['supports'];

			$items = array_filter(
				$items,
				function( $payment_method ) use ( $feature ) {
					return $payment_method->supports( $feature );
				}
			);
		}

		$collection = new self();

		$collection->items = $items;

		return $collection;
	}

	/**
	 * Get iterator.
	 *
	 * @return ArrayIterator<string, PaymentMethod>
	 */
	public function getIterator() {
		return new ArrayIterator( $this->items );
	}

	/**
	 * Is active.
	 *
	 * @param string $id Payment method ID.
	 * @return bool True if status is active, false otherwise.
	 */
	public function is_active( $id ) {
		$payment_method = $this->get( $id );

		if ( null === $payment_method ) {
			return false;
		}

		return ( 'active' === $payment_method->get_status() );
	}
}
