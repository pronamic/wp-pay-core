<?php
/**
 * Refund
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Refunds
 */

namespace Pronamic\WordPress\Pay\Refunds;

use JsonSerializable;
use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\DateTime\DateTimeInterface;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\MoneyJsonTransformer;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Refund
 * Description:
 * Copyright: 2005-2023 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 4.9.0
 * @since   4.9.0
 */
class Refund implements JsonSerializable {
	/**
	 * Created at.
	 *
	 * @var DateTimeInterface
	 */
	public DateTimeInterface $created_at;

	/**
	 * Payment.
	 *
	 * @var Payment Payment.
	 */
	private Payment $payment;

	/**
	 * Amount to refund.
	 *
	 * @var Money Amount.
	 */
	private Money $amount;

	/**
	 * Description.
	 *
	 * @var string
	 */
	private string $description = '';

	/**
	 * Refund lines.
	 *
	 * @var RefundLines
	 */
	public RefundLines $lines;

	/**
	 * Payment service provider ID.
	 *
	 * @var string
	 */
	public string $psp_id = '';

	/**
	 * Metadata.
	 *
	 * @var array
	 */
	public array $meta = [];

	/**
	 * Construct a refund.
	 *
	 * @param Payment $payment Payment.
	 * @param Money   $amount  Amount to refund.
	 */
	public function __construct( Payment $payment, Money $amount ) {
		$this->created_at = new DateTimeImmutable();
		$this->payment    = $payment;
		$this->amount     = $amount;
		$this->lines      = new RefundLines();
	}

	/**
	 * Get payment.
	 *
	 * @return Payment
	 */
	public function get_payment(): Payment {
		return $this->payment;
	}

	/**
	 * Get amount to refund.
	 *
	 * @return Money
	 */
	public function get_amount(): Money {
		return $this->amount;
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Set description.
	 *
	 * @param string $description Description.
	 * @return void
	 */
	public function set_description( string $description ): void {
		$this->description = $description;
	}

	/**
	 * Get refund lines.
	 *
	 * @return RefundLines
	 */
	public function get_lines(): RefundLines {
		return $this->lines;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return object
	 */
	public function jsonSerialize() {
		return (object) [
			'created_at'  => $this->created_at->format( \DATE_ATOM ),
			'amount'      => $this->amount,
			'description' => $this->description,
			'lines'       => $this->lines,
			'psp_id'      => $this->psp_id,
			'meta'        => $this->meta,
		];
	}

	/**
	 * Get refund from JSON.
	 *
	 * @param object  $json    JSON.
	 * @param Payment $payment Payment.
	 * @return Refund
	 */
	public static function from_json( $json, Payment $payment ) {
		$refund = new self(
			$payment,
			MoneyJsonTransformer::from_json( $json->amount )
		);

		if ( \property_exists( $json, 'created_at' ) ) {
			$refund->created_at = new DateTimeImmutable( $json->created_at );
		}

		if ( \property_exists( $json, 'description' ) ) {
			$refund->description = $json->description;
		}

		if ( isset( $json->lines ) ) {
			$refund->lines = RefundLines::from_json( $json->lines, $refund );
		}

		if ( \property_exists( $json, 'psp_id' ) ) {
			$refund->psp_id = $json->psp_id;
		}

		if ( \property_exists( $json, 'meta' ) ) {
			$refund->meta = (array) $json->meta;
		}

		return $refund;
	}
}
