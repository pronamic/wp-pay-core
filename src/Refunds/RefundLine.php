<?php
/**
 * Refund line
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Refunds;

use InvalidArgumentException;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;

/**
 * Refund line.
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   2.1.0
 */
class RefundLine {
	/**
	 * The ID.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * The quantity.
	 *
	 * @var int|null
	 */
	private $quantity;

	/**
	 * Total amount of this payment line.
	 *
	 * @var Money
	 */
	private $total_amount;

	/**
	 * Payment
	 *
	 * @var PaymentLine|null
	 */
	private $payment_line;

    /**
     * Refund.
     * 
     * @var Refund|null
     */
    private $refund;

	/**
	 * Meta.
	 *
	 * @var array
	 */
	public array $meta;

	/**
	 * Payment line constructor.
	 */
	public function __construct() {
		$this->set_total_amount( new Money() );

		$this->meta = [];
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
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the quantity of this payment line.
	 *
	 * @return int|null
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * Set the quantity of this payment line.
	 *
	 * @param int|null $quantity Quantity.
	 * @return void
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = $quantity;
	}

	/**
	 * Get tax amount.
	 *
	 * @return Money|null
	 */
	public function get_tax_amount() {
		if ( ! $this->total_amount instanceof TaxedMoney ) {
			return null;
		}

		$tax_value = $this->total_amount->get_tax_value();

		if ( null === $tax_value ) {
			return null;
		}

		return new Money(
			$tax_value,
			$this->get_total_amount()->get_currency()
		);
	}

	/**
	 * Get total amount.
	 *
	 * @return Money
	 */
	public function get_total_amount() {
		return $this->total_amount;
	}

	/**
	 * Set total amount.
	 *
	 * @param Money $total_amount Total amount.
	 * @return void
	 */
	public function set_total_amount( Money $total_amount ) {
		$this->total_amount = $total_amount;
	}

	/**
	 * Get refund.
	 *
	 * @return null|Refund
	 */
	public function get_refund() {
		return $this->refund;
	}

	/**
	 * Set refund.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function set_refund( Refund $refund ) {
		$this->refund = $refund;
	}

	/**
	 * Get payment line.
	 *
	 * @return null|PaymentLine
	 */
	public function get_payment_line() {
		return $this->payment_line;
	}

	/**
	 * Set payment line.
	 *
	 * @param PaymentLine $payment_line Payment line.
	 * @return void
	 */
	public function set_payment_line( PaymentLine $payment_line ) {
		$this->payment_line = $payment_line;
	}

	/**
	 * Get the meta value of this specified meta key.
	 *
	 * @param string $key Meta key.
	 * @return mixed
	 */
	public function get_meta( $key ) {
		if ( \array_key_exists( $key, $this->meta ) ) {
			return $this->meta[ $key ];
		}

		return null;
	}

	/**
	 * Set meta data.
	 *
	 * @param  string $key   A meta key.
	 * @param  mixed  $value A meta value.
	 * @return void
	 */
	public function set_meta( $key, $value ) {
		$this->meta[ $key ] = $value;
	}

	/**
	 * Delete meta data.
	 *
	 * @param string $key Meta key.
	 * @return void
	 */
	public function delete_meta( $key ) {
		unset( $this->meta[ $key ] );
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

		if ( property_exists( $json, 'quantity' ) ) {
			$line->set_quantity( $json->quantity );
		}

		if ( isset( $json->total_amount ) ) {
			$line->set_total_amount( MoneyJsonTransformer::from_json( $json->total_amount ) );
		}

		if ( property_exists( $json, 'meta' ) ) {
			$line->meta = (array) $json->meta;
		}

		return $line;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$properties = [
			'id'           => $this->get_id(),
			'quantity'     => $this->get_quantity(),
			'total_amount' => $this->total_amount->jsonSerialize(),
			'meta'         => $this->meta,
		];

		$properties = array_filter( $properties );

		return (object) $properties;
	}

	/**
	 * Create string representation of the payment line.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = [
			$this->get_id(),
			$this->get_quantity(),
		];

		$parts = array_map( 'strval', $parts );

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( ' - ', $parts );

		return $string;
	}
}
