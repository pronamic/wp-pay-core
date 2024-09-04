<?php
/**
 * Payment methods collection
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Payment methods collection class
 *
 * @implements IteratorAggregate<string, PaymentMethod>
 */
class PaymentMethodsCollection implements IteratorAggregate, Countable {
	/**
	 * Items.
	 *
	 * @var PaymentMethod[]
	 */
	private $items = [];

	/**
	 * Add payment method.
	 *
	 * @param PaymentMethod $payment_method Payment method.
	 * @return void
	 */
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

	/**
	 * Query items.
	 *
	 * @param array $args Arguments.
	 * @return self
	 */
	public function query( $args ) {
		$items = $this->items;

		if ( \array_key_exists( 'id', $args ) ) {
			$id_list = \wp_parse_list( $args['id'] );

			$items = \array_filter(
				$items,
				function ( $payment_method ) use ( $id_list ) {
					return \in_array( $payment_method->get_id(), $id_list, true );
				}
			);
		}

		if ( \array_key_exists( 'status', $args ) ) {
			$status_list = \wp_parse_list( $args['status'] );

			$items = \array_filter(
				$items,
				function ( $payment_method ) use ( $status_list ) {
					return \in_array( $payment_method->get_status(), $status_list, true );
				}
			);
		}

		if ( \array_key_exists( 'supports', $args ) ) {
			$feature = $args['supports'];

			$items = \array_filter(
				$items,
				function ( $payment_method ) use ( $feature ) {
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
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->items );
	}

	/**
	 * Get array.
	 *
	 * @return array<string, PaymentMethod>
	 */
	public function get_array() {
		return $this->items;
	}

	/**
	 * Count items.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->items );
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
