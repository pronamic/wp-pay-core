<?php
/**
 * Refund lines
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Refunds;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;

/**
 * Refund lines
 *
 * @author     Remco Tolsma
 * @version    2.5.1
 * @since      2.1.0
 * @implements \IteratorAggregate<int, RefundLine>
 */
class RefundLines implements Countable, IteratorAggregate, JsonSerializable {
	/**
	 * The lines.
	 *
	 * @var array
	 */
	private $lines;

	/**
	 * Constructs and initialize a payment lines object.
	 */
	public function __construct() {
		$this->lines = [];
	}

	/**
	 * Get iterator.
	 *
	 * @return ArrayIterator<int, RefundLine>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->lines );
	}

	/**
	 * Get array.
	 *
	 * @return array<int, RefundLine>
	 */
	public function get_array() {
		return $this->lines;
	}

	/**
	 * Add line.
	 *
	 * @param RefundLine $line The line to add.
	 * @return void
	 */
	public function add_line( RefundLine $line ) {
		$this->lines[] = $line;
	}

	/**
	 * New line.
	 *
	 * @return RefundLine
	 */
	public function new_line() {
		$line = new RefundLine();

		$this->add_line( $line );

		return $line;
	}

	/**
	 * Count lines.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->lines );
	}

	/**
	 * Calculate the total amount of all lines.
	 *
	 * @return TaxedMoney
	 */
	public function get_amount() {
		$total    = new Money();
		$tax      = new Money();
		$currency = null;

		foreach ( $this->lines as $line ) {
			// Total.
			$line_total = $line->get_total_amount();

			$total = $total->add( $line_total );

			// Tax.
			if ( $line_total instanceof TaxedMoney ) {
				$line_tax = $line_total->get_tax_amount();

				if ( null !== $line_tax ) {
					$tax = $tax->add( $line_tax );
				}
			}

			// Currency.
			if ( null === $currency ) {
				$currency = $line_total->get_currency();
			}
		}

		// Currency.
		if ( null === $currency ) {
			$currency = 'EUR';
		}

		// Return payment lines amount.
		return new TaxedMoney(
			$total->get_value(),
			$currency,
			$tax->get_value()
		);
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->lines;
	}

	/**
	 * Create items from object.
	 *
	 * @param mixed  $json   JSON.
	 * @param Refund $refund Refund.
	 *
	 * @return RefundLines
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an array.
	 */
	public static function from_json( $json, Refund $refund ) {
		if ( ! is_array( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an array.' );
		}

		$object = new self();

		$lines = array_map(
			/**
			 * Get payment line from object.
			 *
			 * @param object $value Object.
			 * @return PaymentLine
			 */
			function ( $value ) use ( $refund ) {
				return RefundLine::from_json( $value, $refund );
			},
			$json
		);

		foreach ( $lines as $line ) {
			$object->add_line( $line );
		}

		return $object;
	}

	/**
	 * Create string representation the payment lines.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = array_map( 'strval', $this->lines );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
