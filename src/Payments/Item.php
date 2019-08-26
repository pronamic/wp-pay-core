<?php
/**
 * Item
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Money\Money;

/**
 * Item.
 *
 * @deprecated Use `PaymentLine`.
 * @author     Remco Tolsma
 * @version    1.0
 */
class Item {
	/**
	 * The number.
	 *
	 * @var string
	 */
	private $number;

	/**
	 * The description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * The quantity.
	 *
	 * @var int
	 */
	private $quantity;

	/**
	 * The price.
	 *
	 * @var float
	 */
	private $price;

	/**
	 * Constructs and initialize a iDEAL basic item.
	 */
	public function __construct() {
		$this->number      = '';
		$this->description = '';
		$this->quantity    = 1;
		$this->price       = 0.0;
	}

	/**
	 * Call.
	 *
	 * @link http://php.net/manual/de/language.oop5.magic.php
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments.
	 * @return string|int|float
	 */
	public function __call( $name, $arguments ) {
		$map = array(
			'getNumber'      => 'get_number',
			'setNumber'      => 'set_number',
			'setDescription' => 'set_description',
			'getQuantity'    => 'get_quantity',
			'setQuantity'    => 'set_quantity',
			'getPrice'       => 'get_price',
			'setPrice'       => 'set_price',
		);

		if ( isset( $map[ $name ] ) ) {
			$old_method = $name;
			$new_method = $map[ $name ];

			_deprecated_function( esc_html( __CLASS__ . '::' . $old_method ), '2.0.1', esc_html( __CLASS__ . '::' . $new_method ) );

			$callable = array( $this, $new_method );

			if ( is_callable( $callable ) ) {
				return call_user_func_array( $callable, $arguments );
			}
		}

		trigger_error( esc_html( 'Call to undefined method ' . __CLASS__ . '::' . $name . '()' ), E_USER_ERROR );
	}

	/**
	 * Get the number / identifier of this item.
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * Set the number / identifier of this item.
	 *
	 * @param string $number Number.
	 */
	public function set_number( $number ) {
		$this->number = $number;
	}

	/**
	 * Get the description of this item.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set the description of this item.
	 * AN..max32 (AN = Alphanumeric, free text).
	 *
	 * @param string $description Description.
	 */
	public function set_description( $description ) {
		$this->description = substr( $description, 0, 32 );
	}

	/**
	 * Get the quantity of this item.
	 *
	 * @return int
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * Set the quantity of this item
	 *
	 * @param int $quantity Quantity.
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = $quantity;
	}

	/**
	 * Get the price of this item.
	 *
	 * @return float
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * Set the price of this item.
	 *
	 * @param float $price Price.
	 */
	public function set_price( $price ) {
		$this->price = $price;
	}

	/**
	 * Get the amount.
	 *
	 * @return Money
	 */
	public function get_amount() {
		$money = new Money( $this->get_price() );

		$amount = $money->multiply( $this->get_quantity() );

		return $amount;
	}
}
