<?php
/**
 * Payment lines
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;

/**
 * Payment lines
 *
 * @author     Remco Tolsma
 * @version    2.5.1
 * @since      2.1.0
 * @implements \IteratorAggregate<int, PaymentLine>
 */
class PaymentLines implements Countable, IteratorAggregate {
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
	 * @return ArrayIterator<int, PaymentLine>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->lines );
	}

	/**
	 * Get array.
	 *
	 * @return array<int, PaymentLine>
	 */
	public function get_array() {
		return $this->lines;
	}

	/**
	 * Get name.
	 * 
	 * @link https://github.com/pronamic/wp-pronamic-pay-woocommerce/issues/43
	 * @return string
	 */
	public function get_name() {
		$names = \array_map(
			function ( PaymentLine $line ) {
				return (string) $line->get_name();
			},
			$this->get_array()
		);

		return \implode( ', ', $names );
	}

	/**
	 * Add line.
	 *
	 * @param PaymentLine $line The line to add.
	 * @return void
	 */
	public function add_line( PaymentLine $line ) {
		$this->lines[] = $line;
	}

	/**
	 * New line.
	 *
	 * @return PaymentLine
	 */
	public function new_line() {
		$line = new PaymentLine();

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
	 * Get first line with the specified ID.
	 *
	 * @param string $id ID.
	 * @return null|PaymentLine
	 */
	public function first( $id ) {
		$lines = \array_filter(
			$this->lines,
			function ( PaymentLine $line ) use ( $id ) {
				return ( $id === $line->get_id() );
			}
		);

		$line = \reset( $lines );

		if ( false === $line ) {
			return null;
		}

		return $line;
	}

	/**
	 * Get JSON.
	 *
	 * @return array
	 */
	public function get_json() {
		$objects = array_map(
			/**
			 * Get JSON for payment line.
			 *
			 * @param PaymentLine $line Payment line.
			 * @return object
			 */
			function ( PaymentLine $line ) {
				return $line->get_json();
			},
			$this->lines
		);

		return $objects;
	}

	/**
	 * Create items from object.
	 *
	 * @param mixed            $json         JSON.
	 * @param PaymentInfo|null $payment_info Payment info.
	 *
	 * @return PaymentLines
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an array.
	 */
	public static function from_json( $json, PaymentInfo $payment_info = null ) {
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
			function ( $value ) {
				return PaymentLine::from_json( $value );
			},
			$json
		);

		foreach ( $lines as $line ) {
			// Set payment.
			if ( $payment_info instanceof Payment ) {
				$line->set_payment( $payment_info );
			}

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
