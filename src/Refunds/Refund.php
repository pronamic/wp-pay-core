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

use Pronamic\WordPress\Money\Money;
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
class Refund {
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
	 * @var string|null
	 */
	private ?string $description;

	/**
	 * Refund lines.
	 *
	 * @var RefundLines|null
	 */
	public ?RefundLines $lines;

	/**
	 * Construct a refund.
	 *
	 * @param Payment $payment Payment.
	 * @param Money   $amount  Amount to refund.
	 */
	public function __construct( Payment $payment, Money $amount ) {
		$this->payment = $payment;
		$this->amount  = $amount;
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
	 * @return string|null
	 */
	public function get_description(): ?string {
		return $this->description;
	}

	/**
	 * Set description.
	 *
	 * @param string|null $description Description.
	 * @return void
	 */
	public function set_description( ?string $description ): void {
		$this->description = $description;
	}

	/**
	 * Get refund lines.
	 *
	 * @return RefundLines|null
	 */
	public function get_lines(): ?RefundLines {
		return $this->lines;
	}

	/**
	 * Set payment lines.
	 *
	 * @param RefundLines|null $lines Payment lines.
	 */
	public function set_lines( ?RefundLines $lines ): void {
		$this->lines = $lines;
	}
}
