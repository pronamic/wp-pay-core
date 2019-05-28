<?php
/**
 * Items
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use ArrayIterator;
use IteratorAggregate;
use Pronamic\WordPress\Money\Money;

/**
 * Items
 *
 * @deprecated Use `PaymentLines`.
 * @author     Remco Tolsma
 * @version    2.0.6
 */
class Items implements IteratorAggregate {
	/**
	 * The items.
	 *
	 * @var Item[]
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
	 * Calculate the total amount of all items.
	 *
	 * @return Money
	 */
	public function get_amount() {
		$amount = new Money( 0, 'EUR' );

		foreach ( $this->items as $item ) {
			$amount = $amount->add( $item->get_amount() );
		}

		return $amount;
	}
}
