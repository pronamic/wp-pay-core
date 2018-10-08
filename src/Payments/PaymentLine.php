<?php
/**
 * Payment line
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
 * Payment line.
 *
 * @author Remco Tolsma
 * @version 1.0
 */
class PaymentLine {
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
	 * The unit price of this payment line.
	 *
	 * @var Money|null
	 */
	private $unit_price;

	/**
	 * The unit tax of this payment line.
	 *
	 * @var Money|null
	 */
	private $unit_tax;

	/**
	 * Total amount of this payment line including tax.
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
	 * Constructs and initialize a payment line.
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
	 * Get the id / identifier of this payment line.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the id / identifier of this payment line.
	 *
	 * @param string|null $id Number.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Set the id / identifier of this payment line.
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
	 * Get the description of this payment line.
	 *
	 * @return string|null
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set the description of this payment line.
	 *
	 * @param string|null $description Description.
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Get the quantity of this payment line.
	 *
	 * @return int
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * Set the quantity of this payment line.
	 *
	 * @param int $quantity Quantity.
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = $quantity;
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
	 * Create payment line from object.
	 *
	 * @param mixed $json JSON.
	 * @return PaymentLine
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$line = new self();

		if ( property_exists( $json, 'id' ) ) {
			$line->set_id( $json->id );
		}

		if ( property_exists( $json, 'description' ) ) {
			$line->set_description( $json->description );
		}

		if ( property_exists( $json, 'quantity' ) ) {
			$line->set_quantity( $json->quantity );
		}

		if ( isset( $json->unit_price ) ) {
			$line->set_unit_price( MoneyJsonTransformer::from_json( $json->unit_price ) );
		}

		if ( isset( $json->unit_tax ) ) {
			$line->set_unit_tax( MoneyJsonTransformer::from_json( $json->unit_tax ) );
		}

		if ( isset( $json->total_amount ) ) {
			$line->set_total_amount( MoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( property_exists( $json, 'tax_rate' ) ) {
			$line->set_tax_rate( $json->tax_rate );
		}

		return $line;
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
			'unit_price'   => MoneyJsonTransformer::to_json( $this->get_unit_price() ),
			'unit_tax'     => MoneyJsonTransformer::to_json( $this->get_unit_tax() ),
			'total_amount' => MoneyJsonTransformer::to_json( $this->get_total_amount() ),
			'tax_rate'     => $this->get_tax_rate(),
		);
	}

	/**
	 * Create string representation of the payment line.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = array(
			$this->get_id(),
			$this->get_description(),
			$this->get_quantity()
		);

		$parts = array_map( 'strval', $parts );

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( ' - ', $parts );

		return $string;
	}
}
