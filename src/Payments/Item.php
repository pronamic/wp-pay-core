<?php
/**
 * Item
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use InvalidArgumentException;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use stdClass;

/**
 * Item.
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class Item {
	/**
	 * The ID.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * The description.
	 *
	 * @var string|null
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
	 * @deprecated
	 * @var float
	 */
	private $price;

	/**
	 * The unit price of this item.
	 *
	 * @var Money|null
	 */
	private $unit_price;

	/**
	 * The unit tax of this item.
	 *
	 * @var Money|null
	 */
	private $unit_tax;

	/**
	 * Total amount of this item including tax.
	 *
	 * @var Money|null
	 */
	private $total_amount;

	/**
	 * Tax rate.
	 *
	 * 100% = 1.00
	 *  21% = 0.21
	 *   6% = 0.06
	 *
	 * @var float
	 */
	private $tax_rate;

	/**
	 * Constructs and initialize a iDEAL basic item.
	 */
	public function __construct() {
		$this->id          = null;
		$this->description = null;
		$this->quantity    = 1;
		$this->price       = 0;
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
			'getNumber'      => 'get_id',
			'setNumber'      => 'set_id',
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

			return call_user_func_array( array( $this, $new_method ), $arguments );
		}

		trigger_error( esc_html( 'Call to undefined method ' . __CLASS__ . '::' . $name . '()' ), E_USER_ERROR );
	}

	/**
	 * Get the id / identifier of this item.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the id / identifier of this item.
	 *
	 * @param string|null $id Number.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Set the id / identifier of this item.
	 *
	 * @param string|null $id Number.
	 *
	 * @deprecated 2.0.8
	 */
	public function set_number( $id ) {
		_deprecated_function( esc_html( __METHOD__ ), '2.0.8', esc_html( __CLASS__ . '::set_id()' ) );

		$this->set_id( $id );
	}

	/**
	 * Get the description of this item.
	 *
	 * @return string|null
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set the description of this item.
	 *
	 * @param string|null $description Description.
	 */
	public function set_description( $description ) {
		$this->description = $description;
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
		_deprecated_function( esc_html( __METHOD__ ), '2.1.0', esc_html( __CLASS__ . '::get_unit_price' ) );

		return $this->price;
	}

	/**
	 * Set the price of this item.
	 *
	 * @param float|Money $price Price.
	 */
	public function set_price( $price ) {
		_deprecated_function( esc_html( __METHOD__ ), '2.1.0', esc_html( __CLASS__ . '::set_unit_price' ) );

		if ( $price instanceof Money ) {
			$price = $price->get_amount();
		}

		$this->price = $price;
	}

	/**
	 * Get the amount.
	 *
	 * @deprecated
	 * @return float
	 */
	public function get_amount() {
		_deprecated_function( esc_html( __METHOD__ ), '2.1.0', esc_html( __CLASS__ . '::get_total_amount' ) );

		return $this->price * $this->quantity;
	}

	/**
	 * Get unit price including tax.
	 *
	 * @return Money|null
	 */
	public function get_unit_price() {
		return $this->unit_price;
	}

	/**
	 * Set unit price including tax.
	 *
	 * @param Money|null $unit_price Unit price.
	 */
	public function set_unit_price( Money $unit_price = null ) {
		$this->unit_price = $unit_price;
	}

	/**
	 * Get unit tax.
	 *
	 * @return Money|null
	 */
	public function get_unit_tax() {
		return $this->unit_tax;
	}

	/**
	 * Set unit tax.
	 *
	 * @param Money|null $unit_tax Unit tax.
	 */
	public function set_unit_tax( Money $unit_tax = null ) {
		$this->unit_tax = $unit_tax;
	}

	/**
	 * Get total amount including tax.
	 *
	 * @return Money|null
	 */
	public function get_total_amount() {
		return $this->total_amount;
	}

	/**
	 * Set total amount including tax.
	 *
	 * @param Money|null $total_amount Total amount.
	 */
	public function set_total_amount( Money $total_amount = null ) {
		$this->total_amount = $total_amount;
	}

	/**
	 * Get tax rate.
	 *
	 * @return float|null
	 */
	public function get_tax_rate() {
		return $this->tax_rate;
	}

	/**
	 * Set tax rate.
	 *
	 * @param float $tax_rate Tax rate.
	 */
	public function set_tax_rate( $tax_rate ) {
		$this->tax_rate = $tax_rate;
	}

	/**
	 * Create item from object.
	 *
	 * @param mixed $json JSON.
	 * @return Item
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$item = new self();

		if ( property_exists( $json, 'id' ) ) {
			$item->set_id( $json->id );
		}

		if ( property_exists( $json, 'description' ) ) {
			$item->set_description( $json->description );
		}

		if ( property_exists( $json, 'quantity' ) ) {
			$item->set_quantity( $json->quantity );
		}

		if ( property_exists( $json, 'price' ) ) {
			$item->set_price( $json->price );
		}

		if ( property_exists( $json, 'unit_price' ) ) {
			$item->set_unit_price( MoneyJsonTransformer::from_json( $json->unit_price ) );
		}

		if ( property_exists( $json, 'unit_tax' ) ) {
			$item->set_unit_tax( MoneyJsonTransformer::from_json( $json->unit_tax ) );
		}

		if ( property_exists( $json, 'total_amount' ) ) {
			$item->set_total_amount( MoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( property_exists( $json, 'tax_rate' ) ) {
			$item->set_tax_rate( $json->tax_rate );
		}

		return $item;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		return (object) array(
			'id'           => $this->get_id(),
			'description'  => $this->get_description(),
			'quantity'     => $this->get_quantity(),
			'price'        => $this->get_price(),
			'unit_price'   => MoneyJsonTransformer::to_json( $this->get_unit_price() ),
			'unit_tax'     => MoneyJsonTransformer::to_json( $this->get_unit_tax() ),
			'total_amount' => MoneyJsonTransformer::to_json( $this->get_total_amount() ),
			'tax_rate'     => $this->get_tax_rate(),
		);
	}

	/**
	 * Create string representation of order item.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf(
			/* translators: 1: item id, 2: item description, 3: item quantity, 4: item price, 5: item amount */
			'%1$s %2$s %3$d %4$01.2F %5$0.2F',
			$this->get_id(),
			$this->get_description(),
			$this->get_quantity(),
			$this->get_price(),
			$this->get_amount()
		);
	}
}
