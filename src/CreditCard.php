<?php
/**
 * Credit Card
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Credit card class
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 1.4.0
 */
class CreditCard {
	/**
	 * Credit card number.
	 *
	 * @var string
	 */
	private $number;

	/**
	 * Credit card expiration month.
	 *
	 * @var int
	 */
	private $expiration_month;

	/**
	 * Credit card expiration year.
	 *
	 * @var int
	 */
	private $expiration_year;

	/**
	 * Credit card security code.
	 *
	 * @var string
	 */
	private $security_code;

	/**
	 * Credit card holder name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Constructs and initializes an credit card object.
	 */
	public function __construct() {

	}

	/**
	 * Get credit card number.
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * Set credit card number.
	 *
	 * @param string $number Number.
	 * @return void
	 */
	public function set_number( $number ) {
		$this->number = $number;
	}

	/**
	 * Get expiration month.
	 *
	 * @return int
	 */
	public function get_expiration_month() {
		return $this->expiration_month;
	}

	/**
	 * Set expiration month
	 *
	 * @param int $month Month.
	 * @return void
	 */
	public function set_expiration_month( $month ) {
		$this->expiration_month = $month;
	}

	/**
	 * Get expiration year.
	 *
	 * @return int
	 */
	public function get_expiration_year() {
		return $this->expiration_year;
	}

	/**
	 * Set expiration year
	 *
	 * @param int $year Year.
	 * @return void
	 */
	public function set_expiration_year( $year ) {
		$this->expiration_year = $year;
	}

	/**
	 * Get expiration date.
	 *
	 * @link http://php.net/manual/en/datetime.formats.relative.php
	 * @link http://php.net/manual/en/datetime.setdate.php
	 * @return \DateTime|null
	 */
	public function get_expiration_date() {
		if ( empty( $this->expiration_year ) || empty( $this->expiration_month ) ) {
			return null;
		}

		$date = new \DateTime();

		$date->setDate( $this->expiration_year, $this->expiration_month, 1 );
		$date->setTime( 0, 0 );

		return $date;
	}

	/**
	 * Get security code.
	 *
	 * @return string
	 */
	public function get_security_code() {
		return $this->security_code;
	}

	/**
	 * Set security code.
	 *
	 * @param string $security_code Security code.
	 * @return void
	 */
	public function set_security_code( $security_code ) {
		$this->security_code = $security_code;
	}

	/**
	 * Get credit card holder name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set credit card holder name.
	 *
	 * @param string $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}
}
