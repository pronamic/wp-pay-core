<?php
/**
 * Failure reason.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Failure reason.
 *
 * @author  Reüel van der Steege
 * @since   2.2.8
 * @version 2.2.8
 */
class FailureReason {
	/**
	 * Code.
	 *
	 * @var string
	 */
	public $code;

	/**
	 * Message.
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Set code.
	 *
	 * @param string $code Code.
	 */
	public function set_code( $code ) {
		$this->code = $code;
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Set message.
	 *
	 * @param string $message Message.
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = array(
			'code'    => $this->get_code(),
			'message' => $this->get_message(),
		);

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create failure reason from object.
	 *
	 * @param mixed $json JSON.
	 * @return FailureReason
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		$failure_reason = new self();

		if ( isset( $json->code ) ) {
			$failure_reason->set_code( $json->code );
		}

		if ( isset( $json->message ) ) {
			$failure_reason->set_message( $json->message );
		}

		return $failure_reason;
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	public function __toString() {
		$code    = $this->get_code();
		$message = $this->get_message();

		if ( null === $code && null === $message ) {
			return '';
		}

		if ( null === $code ) {
			return $message;
		}

		if ( null === $message ) {
			return $code;
		}

		return sprintf( '%1$s (`%2$s`)', $message, $code );
	}
}
