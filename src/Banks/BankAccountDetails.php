<?php
/**
 * Bank details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Banks;

/**
 * Bank details
 *
 * @author  Reüel van der Steege
 * @since   2.2.6
 * @version 2.2.6
 */
class BankAccountDetails {
	/**
	 * Bank name.
	 *
	 * @var string|null
	 */
	private $bank_name;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * IBAN.
	 *
	 * @var string|null
	 */
	private $iban;
	/**
	 * BIC.
	 *
	 * @var string|null
	 */
	private $bic;

	/**
	 * Account number.
	 *
	 * @var string|null
	 */
	private $account_number;

	/**
	 * Account holder city.
	 *
	 * @var string|null
	 */
	private $city;

	/**
	 * Account holder country.
	 *
	 * @var string|null
	 */
	private $country;

	/**
	 * Get bank name.
	 *
	 * @return string|null
	 */
	public function get_bank_name() {
		return $this->bank_name;
	}

	/**
	 * Set bank name.
	 *
	 * @param string|null $bank_name Bank name.
	 * @return void
	 */
	public function set_bank_name( $bank_name ) {
		$this->bank_name = $bank_name;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get IBAN.
	 *
	 * @return string|null
	 */
	public function get_iban() {
		return $this->iban;
	}

	/**
	 * Set IBAN.
	 *
	 * @param string|null $iban IBAN.
	 * @return void
	 */
	public function set_iban( $iban ) {
		$this->iban = $iban;
	}

	/**
	 * Get BIC.
	 *
	 * @return string|null
	 */
	public function get_bic() {
		return $this->bic;
	}

	/**
	 * Set BIC.
	 *
	 * @param string|null $bic Bic.
	 * @return void
	 */
	public function set_bic( $bic ) {
		$this->bic = $bic;
	}

	/**
	 * Get account number.
	 *
	 * @return string|null
	 */
	public function get_account_number() {
		return $this->account_number;
	}

	/**
	 * Set account number.
	 *
	 * @param string|null $account_number Account number.
	 * @return void
	 */
	public function set_account_number( $account_number ) {
		$this->account_number = $account_number;
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function get_city() {
		return $this->city;
	}

	/**
	 * Set city.
	 *
	 * @param string|null $city City.
	 * @return void
	 */
	public function set_city( $city ) {
		$this->city = $city;
	}

	/**
	 * Get country.
	 *
	 * @return string|null
	 */
	public function get_country() {
		return $this->country;
	}

	/**
	 * Set country.
	 *
	 * @param string|null $country Country.
	 * @return void
	 */
	public function set_country( $country ) {
		$this->country = $country;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'name'           => $this->get_name(),
			'account_number' => $this->get_account_number(),
			'iban'           => $this->get_iban(),
			'bic'            => $this->get_bic(),
			'bank_name'      => $this->get_bank_name(),
			'city'           => $this->get_city(),
			'country'        => $this->get_country(),
		];

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create bank account details from object.
	 *
	 * @param mixed                   $json                 JSON.
	 * @param BankAccountDetails|null $bank_account_details Bank account details.
	 *
	 * @return BankAccountDetails
	 *
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, $bank_account_details = null ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( null === $bank_account_details ) {
			$bank_account_details = new self();
		}

		if ( isset( $json->name ) ) {
			$bank_account_details->set_name( $json->name );
		}

		if ( isset( $json->iban ) ) {
			$bank_account_details->set_iban( $json->iban );
		}

		if ( isset( $json->bic ) ) {
			$bank_account_details->set_bic( $json->bic );
		}

		if ( isset( $json->account_number ) ) {
			$bank_account_details->set_account_number( $json->account_number );
		}

		if ( isset( $json->bank_name ) ) {
			$bank_account_details->set_bank_name( $json->bank_name );
		}

		if ( isset( $json->city ) ) {
			$bank_account_details->set_city( $json->city );
		}

		if ( isset( $json->country ) ) {
			$bank_account_details->set_country( $json->country );
		}

		return $bank_account_details;
	}

	/**
	 * Create an string representation of this object
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = [
			\trim( (string) $this->get_name() ),
			\trim( (string) $this->get_bank_name() ),
			\trim( (string) $this->get_iban() ),
			\trim( (string) $this->get_bic() ),
			\trim( (string) $this->get_account_number() ),
			\trim( (string) $this->get_city() ),
			\trim( (string) $this->get_country() ),
		];

		$pieces = \array_filter( $pieces );

		$string = \implode( PHP_EOL, $pieces );

		return $string;
	}
}
