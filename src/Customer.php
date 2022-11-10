<?php
/**
 * Contact.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\VatNumbers\VatNumber;

/**
 * Contact.
 *
 * @author  Reüel van der Steege.
 * @version 2.4.0
 * @since   2.1.0
 */
class Customer {
	/**
	 * Contact name.
	 */
	private ?ContactName $name = null;

	/**
	 * Gender.
	 */
	private ?string $gender = null;

	/**
	 * Date of birth.
	 */
	private ?DateTime $birth_date = null;

	/**
	 * Email address.
	 */
	private ?string $email = null;

	/**
	 * Telephone number.
	 */
	private ?string $phone = null;

	/**
	 * IP address.
	 */
	private ?string $ip_address = null;

	/**
	 * User agent.
	 */
	private ?string $user_agent = null;

	/**
	 * Language.
	 */
	private ?string $language = null;

	/**
	 * Locale.
	 */
	private ?string $locale = null;

	/**
	 * WordPress user ID.
	 */
	private ?int $user_id = null;

	/**
	 * Company name.
	 */
	private ?string $company_name = null;

	/**
	 * VAT Number.
	 */
	private ?VatNumber $vat_number = null;

	/**
	 * Get contact name.
	 *
	 * @return ContactName|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set contact name.
	 *
	 * @param ContactName|null $name Contact name.
	 * @return void
	 */
	public function set_name( ContactName $name = null ): void {
		$this->name = $name;
	}

	/**
	 * Get gender.
	 *
	 * @return string|null
	 */
	public function get_gender() {
		return $this->gender;
	}

	/**
	 * Set gender.
	 *
	 * @param string|null $gender Gender.
	 * @return void
	 */
	public function set_gender( $gender ): void {
		$this->gender = $gender;
	}

	/**
	 * Get birth date.
	 *
	 * @return DateTime|null
	 */
	public function get_birth_date() {
		return $this->birth_date;
	}

	/**
	 * Set birth date.
	 *
	 * @param DateTime|null $birth_date Date of birth.
	 * @return void
	 */
	public function set_birth_date( $birth_date ): void {
		$this->birth_date = $birth_date;
	}

	/**
	 * Get email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set email address.
	 *
	 * @param string|null $email Email address.
	 * @return void
	 */
	public function set_email( $email ): void {
		$this->email = $email;
	}

	/**
	 * Get phone.
	 *
	 * @return string|null
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * Set phone.
	 *
	 * @param string|null $phone Telephone number.
	 * @return void
	 */
	public function set_phone( $phone ): void {
		$this->phone = $phone;
	}

	/**
	 * Get ip address.
	 *
	 * @return string|null
	 */
	public function get_ip_address() {
		return $this->ip_address;
	}

	/**
	 * Set ip address.
	 *
	 * @param string|null $ip_address IP address.
	 * @return void
	 */
	public function set_ip_address( $ip_address ): void {
		$this->ip_address = $ip_address;
	}

	/**
	 * Get user agent.
	 *
	 * @return string|null
	 */
	public function get_user_agent() {
		return $this->user_agent;
	}

	/**
	 * Set user agent.
	 *
	 * @param string|null $user_agent User agent.
	 * @return void
	 */
	public function set_user_agent( $user_agent ): void {
		$this->user_agent = $user_agent;
	}

	/**
	 * Get language.
	 *
	 * @return string|null
	 */
	public function get_language() {
		return $this->language;
	}

	/**
	 * Set language.
	 *
	 * @param string|null $language Language.
	 * @return void
	 */
	public function set_language( $language ): void {
		$this->language = $language;
	}

	/**
	 * Get locale.
	 *
	 * @return string|null
	 */
	public function get_locale() {
		return $this->locale;
	}

	/**
	 * Set locale.
	 *
	 * @param string|null $locale Locale.
	 * @return void
	 */
	public function set_locale( $locale ): void {
		$this->locale = $locale;
	}

	/**
	 * Get WordPress user ID.
	 *
	 * @return int|null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Set WordPress user ID.
	 *
	 * @param int|null $user_id WordPress user ID.
	 * @return void
	 */
	public function set_user_id( $user_id ): void {
		$this->user_id = $user_id;
	}

	/**
	 * Get company name.
	 *
	 * @return string|null
	 */
	public function get_company_name() {
		return $this->company_name;
	}

	/**
	 * Set company name.
	 *
	 * @param string|null $company_name Company name.
	 * @return void
	 */
	public function set_company_name( $company_name = null ): void {
		$this->company_name = $company_name;
	}

	/**
	 * Get VAT number.
	 *
	 * @return VatNumber|null
	 */
	public function get_vat_number() {
		return $this->vat_number;
	}

	/**
	 * Set VAT number.
	 *
	 * @param VatNumber|string|null $vat_number VAT number.
	 * @return void
	 */
	public function set_vat_number( $vat_number = null ): void {
		if ( \is_string( $vat_number ) ) {
			$vat_number = new VatNumber( $vat_number );
		}

		$this->vat_number = $vat_number;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = [
			'name'         => ( null === $this->name ) ? null : $this->name->get_json(),
			'gender'       => $this->get_gender(),
			'birth_date'   => ( null === $this->birth_date ) ? null : $this->birth_date->format( DATE_RFC3339 ),
			'email'        => $this->get_email(),
			'phone'        => $this->get_phone(),
			'ip_address'   => $this->get_ip_address(),
			'user_agent'   => $this->get_user_agent(),
			'language'     => $this->get_language(),
			'locale'       => $this->get_locale(),
			'user_id'      => $this->get_user_id(),
			'company_name' => $this->get_company_name(),
			'vat_number'   => ( null === $this->vat_number ) ? null : $this->vat_number->get_json(),
		];

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create address from object.
	 *
	 * @param mixed $json JSON.
	 * @return Customer
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an array.' );
		}

		$customer = new self();

		$properties = (array) $json;

		foreach ( $properties as $key => $value ) {
			$method = sprintf( 'set_%s', $key );

			$callable = [ $customer, $method ];

			if ( is_callable( $callable ) ) {
				if ( 'name' === $key ) {
					$value = ContactName::from_json( $value );
				}

				if ( 'birth_date' === $key ) {
					$value = new DateTime( $value );
				}

				if ( 'vat_number' === $key ) {
					$value = VatNumber::from_json( $value );
				}

				call_user_func( $callable, $value );
			}
		}

		return $customer;
	}

	/**
	 * Create string representation of customer.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = [
			$this->get_name(),
			$this->get_email(),
			$this->get_phone(),
			$this->get_gender(),
			( null === $this->birth_date ) ? null : $this->birth_date->format( DATE_RFC3339 ),
			$this->get_user_agent(),
			$this->get_ip_address(),
			$this->get_language(),
			$this->get_locale(),
		];

		$pieces = array_map( 'strval', $pieces );

		$pieces = array_filter( $pieces );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
