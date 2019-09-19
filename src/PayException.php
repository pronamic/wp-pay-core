<?php
/**
 * Pronamic Pay Exception.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Exception;
use Pronamic\WordPress\Pay\Payments\Payment;

class PayException extends Exception {
	/**
	 * Error code.
	 *
	 * @var string
	 */
	protected $error_code;

	/**
	 * Data.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * Payment.
	 *
	 * @var null|Payment
	 */
	protected $payment;

	/**
	 * Constructor.
	 *
	 * @param string $error_code Exception code.
	 * @param string $message    Message.
	 * @param mixed  $data       Data.
	 */
	public function __construct( $error_code, $message, $data = null ) {
		$this->error_code = $error_code;
		$this->data       = $data;

		return parent::__construct( $message );
	}

	/**
	 * Get error code.
	 *
	 * @return null|string
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get message (alias for `getMessage()`).
	 *
	 * @return null|string
	 */
	public function get_message() {
		return $this->getMessage();
	}

	/**
	 * Get data.
	 *
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Set payment.
	 *
	 * @param Payment $payment Payment.
	 */
	public function set_payment( Payment $payment ) {
		$this->payment = $payment;
	}

	/**
	 * Get payment.
	 *
	 * @return null|Payment
	 */
	public function get_payment() {
		return $this->payment;
	}

	/**
	 * Render exception.
	 */
	public function render() {
		require pronamic_pay_plugin()->get_plugin_dir_path() . '/views/exception.php';
	}
}
