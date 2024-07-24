<?php
/**
 * VAT Number validity
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\VatNumbers;

/**
 * VAT Number validity
 *
 * @link    https://ec.europa.eu/taxation_customs/vies/?locale=en
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   1.4.0
 */
class VatNumberValidity {
	/**
	 * VAT Number.
	 *
	 * @var VatNumber
	 */
	private $vat_number;

	/**
	 * Request date.
	 *
	 * @var \DateTimeInterface
	 */
	private $request_date;

	/**
	 * Valid flag.
	 *
	 * @var bool
	 */
	private $valid;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Address.
	 *
	 * @var string|null
	 */
	private $address;

	/**
	 * Validation service indicator.
	 *
	 * @var string|null
	 */
	private $service;

	/**
	 * Construct VAT number object.
	 *
	 * @param VatNumber          $vat_number   VAT identification number.
	 * @param \DateTimeInterface $request_date Request date.
	 * @param bool               $valid        True if valid, false otherwise.
	 */
	public function __construct( VatNumber $vat_number, \DateTimeInterface $request_date, $valid ) {
		$this->vat_number   = $vat_number;
		$this->request_date = $request_date;
		$this->valid        = $valid;
	}

	/**
	 * Get VAT number.
	 *
	 * @return VatNumber
	 */
	public function get_vat_number() {
		return $this->vat_number;
	}

	/**
	 * Get request date.
	 *
	 * @return \DateTimeInterface
	 */
	public function get_request_date() {
		return $this->request_date;
	}

	/**
	 * Is valid.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public function is_valid() {
		return $this->valid;
	}

	/**
	 * Set valid.
	 *
	 * @param bool $valid Valid.
	 * @return void
	 */
	public function set_valid( $valid ) {
		$this->valid = $valid;
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
	 * Get address.
	 *
	 * @return string|null
	 */
	public function get_address() {
		return $this->address;
	}

	/**
	 * Set address.
	 *
	 * @param string|null $address Address.
	 * @return void
	 */
	public function set_address( $address ) {
		$this->address = $address;
	}

	/**
	 * Get service.
	 *
	 * @return string|null
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Set service.
	 *
	 * @param string|null $service Service.
	 * @return void
	 */
	public function set_service( $service ) {
		$this->service = $service;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'vat_number'   => $this->vat_number->get_value(),
			'request_date' => $this->request_date->format( 'Y-m-d' ),
			'valid'        => $this->valid,
		];

		if ( null !== $this->name ) {
			$data['name'] = $this->name;
		}

		if ( null !== $this->address ) {
			$data['address'] = $this->address;
		}

		if ( null !== $this->service ) {
			$data['service'] = $this->service;
		}

		return (object) $data;
	}

	/**
	 * Create from object.
	 *
	 * @param mixed $json JSON.
	 * @return VatNumberValidity
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( ! property_exists( $json, 'vat_number' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not contain `vat_number` property.' );
		}

		if ( ! property_exists( $json, 'request_date' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not contain `request_date` property.' );
		}

		if ( ! property_exists( $json, 'valid' ) ) {
			throw new \InvalidArgumentException( 'JSON object does not contain `valid` property.' );
		}

		$validity = new self(
			VatNumber::from_json( $json->vat_number ),
			new \DateTimeImmutable( $json->request_date ),
			\boolval( $json->valid )
		);

		if ( property_exists( $json, 'name' ) ) {
			$validity->set_name( $json->name );
		}

		if ( property_exists( $json, 'address' ) ) {
			$validity->set_address( $json->address );
		}

		if ( property_exists( $json, 'service' ) ) {
			$validity->set_service( $json->service );
		}

		return $validity;
	}
}
