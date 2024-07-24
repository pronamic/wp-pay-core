<?php
/**
 * Bank transfer details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Banks;

/**
 * Bank transfer details
 *
 * @author  Reüel van der Steege
 * @since   2.2.6
 * @version 2.2.6
 */
class BankTransferDetails {
	/**
	 * Bank account details.
	 *
	 * @var BankAccountDetails|null
	 */
	private $bank_account;

	/**
	 * Reference.
	 *
	 * @var string|null
	 */
	private $reference;

	/**
	 * Get bank account.
	 *
	 * @return BankAccountDetails|null
	 */
	public function get_bank_account() {
		return $this->bank_account;
	}

	/**
	 * Set bank account.
	 *
	 * @param BankAccountDetails|null $bank_account Bank account.
	 * @return void
	 */
	public function set_bank_account( $bank_account ) {
		$this->bank_account = $bank_account;
	}

	/**
	 * Get reference.
	 *
	 * @return string|null
	 */
	public function get_reference() {
		return $this->reference;
	}

	/**
	 * Set reference.
	 *
	 * @param string|null $reference Reference.
	 * @return void
	 */
	public function set_reference( $reference ) {
		$this->reference = $reference;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [];

		// Bank account.
		$bank_account = $this->get_bank_account();

		if ( null !== $bank_account ) {
			$data['bank_account'] = $bank_account->get_json();
		}

		// Reference.
		$data['reference'] = $this->get_reference();

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create bank account details from object.
	 *
	 * @param mixed                    $json                  JSON.
	 * @param BankTransferDetails|null $bank_transfer_details Bank account details.
	 *
	 * @return BankTransferDetails
	 *
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, $bank_transfer_details = null ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( null === $bank_transfer_details ) {
			$bank_transfer_details = new self();
		}

		if ( isset( $json->bank_account ) ) {
			$bank_transfer_details->set_bank_account( BankAccountDetails::from_json( $json->bank_account ) );
		}

		if ( isset( $json->reference ) ) {
			$bank_transfer_details->set_reference( $json->reference );
		}

		return $bank_transfer_details;
	}

	/**
	 * Create an string representation of this object
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = [
			$this->get_bank_account(),
			$this->get_reference(),
		];

		$pieces = array_map( 'strval', $pieces );

		$pieces = array_map( 'trim', $pieces );

		$pieces = array_filter( $pieces );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
