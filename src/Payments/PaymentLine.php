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
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * The description.
	 *
	 * @var string|null
	 */
	private $description;

	/**
	 * The quantity.
	 *
	 * @var int|null
	 */
	private $quantity;

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
	 * Get the name of this payment line.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the name of this payment line.
	 *
	 * @param string|null $name Name.
	 */
	public function set_name( $name ) {
		$this->name = $name;
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
	 * Get total tax.
	 *
	 * @return Money|null
	 */
	public function get_total_tax() {
		return $this->total_tax;
	}

	/**
	 * Set total tax.
	 *
	 * @param Money|null $total_tax Total tax.
	 */
	public function set_total_tax( Money $total_tax = null ) {
		$this->total_tax = $total_tax;
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

		if ( property_exists( $json, 'name' ) ) {
			$line->set_name( $json->name );
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

		if ( isset( $json->total_tax ) ) {
			$line->set_total_tax( MoneyJsonTransformer::from_json( $json->total_tax ) );
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
			'name'         => $this->get_name(),
			'description'  => $this->get_description(),
			'quantity'     => $this->get_quantity(),
			'unit_price'   => MoneyJsonTransformer::to_json( $this->get_unit_price() ),
			'unit_tax'     => MoneyJsonTransformer::to_json( $this->get_unit_tax() ),
			'total_amount' => MoneyJsonTransformer::to_json( $this->get_total_amount() ),
			'total_tax'    => MoneyJsonTransformer::to_json( $this->get_total_tax() ),
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
