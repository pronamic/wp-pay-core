<?php
/**
 * Items
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Pronamic\WordPress\Money\Money;
use stdClass;

/**
 * Items
 *
 * @author  Remco Tolsma
 * @version 2.0.6
 */
class Items implements Countable, IteratorAggregate {
	/**
	 * The items.
	 *
	 * @var array
	 */
	private $items;

	/**
	 * Constructs and initialize a iDEAL basic object.
	 */
	public function __construct() {
		$this->items = array();
	}

	/**
	 * Get iterator.
	 *
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new ArrayIterator( $this->items );
	}

	/**
	 * Add item.
	 *
	 * @param Item $item The item to add.
	 *
	 * @deprecated 2.0.8
	 */
	public function addItem( Item $item ) {
		_deprecated_function( __FUNCTION__, '2.0.8', 'Pronamic\WordPress\Pay\Payments\Items::add_item()' );

		$this->add_item( $item );
	}

	/**
	 * Add item.
	 *
	 * @param Item $item The item to add.
	 */
	public function add_item( Item $item ) {
		$this->items[] = $item;
	}

	/**
	 * Count items.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Calculate the total amount of all items.
	 *
	 * @return Money
	 */
	public function get_amount() {
		$amount = 0;

		$use_bcmath = extension_loaded( 'bcmath' );

		foreach ( $this->items as $item ) {
			if ( $use_bcmath ) {
				// Use non-locale aware float value.
				// @link http://php.net/sprintf.
				$item_amount = sprintf( '%F', $item->get_amount() );

				$amount = bcadd( $amount, $item_amount, 8 );
			} else {
				$amount += $item->get_amount();
			}
		}

		return new Money( $amount );
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$objects = array_map(
			function( $item ) {
					return $item->get_json();
			},
			$this->items
		);

		return $objects;
	}

	/**
	 * Create items from object.
	 *
	 * @param mixed $json JSON.
	 * @return Items
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an array.
	 */
	public static function from_json( $json ) {
		if ( ! is_array( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$object = new self();

		$items = array_map(
			function( $object ) {
					return Item::from_json( $object );
			},
			$json
		);

		foreach ( $items as $item ) {
			$object->add_item( $item );
		}

		return $object;
	}

	/**
	 * Create string representation of order items.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = array_map( 'strval', $this->items );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
