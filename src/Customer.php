<?php
/**
 * Contact.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\Util;
use stdClass;

/**
 * Contact.
 *
 * @author  Reüel van der Steege.
 * @since   2.0.8
 * @version 2.0.8
 */
class Customer {
	/**
	 * Contact name.
	 *
	 * @var ContactName|null
	 */
	private $name;

	/**
	 * Gender.
	 *
	 * @var string|null
	 */
	private $gender;

	/**
	 * Date of birth.
	 *
	 * @var DateTime|null
	 */
	private $birth_date;

	/**
	 * Email address.
	 *
	 * @var string|null
	 */
	private $email;

	/**
	 * Telephone number.
	 *
	 * @var string|null
	 */
	private $phone;

	/**
	 * IP address.
	 *
	 * @var string|null
	 */
	private $ip_address;

	/**
	 * User agent.
	 *
	 * @var string|null
	 */
	private $user_agent;

	/**
	 * Language.
	 *
	 * @var string|null
	 */
	private $language;

	/**
	 * Locale.
	 *
	 * @var string|null
	 */
	private $locale;

	/**
	 * Contact constructor.
	 */
	public function __construct() {
		$this->set_name( new ContactName() );
	}

	/**
	 * Complement customer.
	 */
	public function complement() {
		// Locale.
		if ( null === $this->get_locale() && is_user_logged_in() ) {
			$locale = get_user_locale();

			$this->set_locale( $locale );
		}

		// Language.
		if ( null === $this->get_language() && null !== $this->get_locale() ) {
			$language = substr( $this->get_locale(), 0, 2 );

			$this->set_language( $language );
		}

		// User Agent.
		if ( null === $this->get_user_agent() ) {
			// User Agent (@see https://github.com/WordPress/WordPress/blob/4.9.4/wp-includes/comment.php#L1962-L1965).
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null; // WPCS: input var ok.

			$this->set_user_agent( $user_agent );
		}

		// User IP.
		if ( null === $this->get_ip_address() ) {
			// IP (@see https://github.com/WordPress/WordPress/blob/4.9.4/wp-includes/comment.php#L1957-L1960).
			$remote_address = Util::get_remote_address();

			if ( ! empty( $remote_address ) ) {
				$ip_address = sanitize_text_field( wp_unslash( $remote_address ) );

				$this->set_ip_address( $ip_address );
			}
		}
	}

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
	 * @param ContactName $name Contact name.
	 */
	public function set_name( ContactName $name ) {
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
	 * @param string $gender Gender.
	 */
	public function set_gender( $gender ) {
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
	 * @param DateTime $birth_date Date of birth.
	 */
	public function set_birth_date( $birth_date ) {
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
	 * @param string $email Email adress.
	 */
	public function set_email( $email ) {
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
	 * @param string $phone Telephone number.
	 */
	public function set_phone( $phone ) {
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
	 * @param string $ip_address IP address.
	 */
	public function set_ip_address( $ip_address ) {
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
	 * @param string $user_agent User agent.
	 */
	public function set_user_agent( $user_agent ) {
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
	 * @param string $language Language.
	 */
	public function set_language( $language ) {
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
	 * @param string $locale Locale.
	 */
	public function set_locale( $locale ) {
		$this->locale = $locale;
	}

	/**
	 * Get JSON.
	 *
	 * @return object|null
	 */
	public function get_json() {
		$data = array(
			'name'       => $this->get_name()->get_json(),
			'gender'     => $this->get_gender(),
			'birth_date' => $this->get_birth_date(),
			'email'      => $this->get_email(),
			'phone'      => $this->get_phone(),
			'ip_address' => $this->get_ip_address(),
			'user_agent' => $this->get_user_agent(),
			'language'   => $this->get_language(),
			'locale'     => $this->get_locale(),
		);

		$data = array_filter( $data );

		if ( empty( $data ) ) {
			return null;
		}

		return (object) $data;
	}

	/**
	 * Create address from object.
	 *
	 * @param stdClass $object Object.
	 *
	 * @return Customer
	 */
	public static function from_object( stdClass $object ) {
		$contact = new self();

		foreach ( $object as $key => $value ) {
			$method = sprintf( 'set_%s', $key );

			if ( is_callable( array( $contact, $method ) ) ) {
				if ( 'name' === $key ) {
					$value = ContactName::from_object( $value );
				}

				call_user_func( array( $contact, $method ), $value );
			}
		}

		return $contact;
	}

	/**
	 * Create string representation of customer.
	 *
	 * @return string
	 */
	public function __toString() {
		$pieces = array(
			$this->get_name(),
			$this->get_email(),
			$this->get_phone(),
			$this->get_gender(),
			$this->get_birth_date(),
			$this->get_user_agent(),
			$this->get_ip_address(),
			$this->get_language(),
			$this->get_locale(),
		);

		$pieces = array_filter( $pieces );

		$string = implode( PHP_EOL, $pieces );

		return $string;
	}
}
