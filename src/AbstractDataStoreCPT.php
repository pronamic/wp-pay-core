<?php
/**
 * Abstract Data Store Custom Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use Exception;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeZone;

/**
 * Abstract Data Store Custom Post Type
 *
 * @link https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   1.0.0
 */
abstract class AbstractDataStoreCPT {
	/**
	 * Registered meta keys.
	 *
	 * @var array
	 */
	protected $meta = [];

	/**
	 * Meta key prefix.
	 *
	 * @var string
	 */
	public $meta_key_prefix = '';

	/**
	 * Register meta keys.
	 *
	 * @param string $meta_key Meta key to register.
	 * @param array  $args     Settings for meta key.
	 *
	 * @return void
	 */
	protected function register_meta_key( $meta_key, $args ) {
		$this->meta[ $meta_key ] = $args;
	}

	/**
	 * Get registered meta.
	 *
	 * @return array
	 */
	public function get_registered_meta() {
		return $this->meta;
	}

	/**
	 * Get a prefixed meta key for the specified key.
	 *
	 * @param string $key A key.
	 * @return string
	 */
	protected function get_meta_key( $key ) {
		return $this->meta_key_prefix . $key;
	}

	/**
	 * Get MySQL UTC datetime of the specified date.
	 *
	 * @param \DateTimeInterface $date The date.
	 * @return string
	 */
	protected function get_mysql_utc_date( \DateTimeInterface $date ) {
		$date = clone $date;

		if ( \method_exists( $date, 'setTimezone' ) ) {
			$date = $date->setTimezone( new DateTimeZone( 'UTC' ) );
		}

		return $date->format( DateTime::MYSQL );
	}

	/**
	 * Get meta for the specified post ID and key.
	 *
	 * @param int    $id  Post ID.
	 * @param string $key Key.
	 * @return string|null|false
	 */
	public function get_meta( $id, $key ) {
		$meta_key = $this->get_meta_key( $key );

		$value = get_post_meta( $id, $meta_key, true );

		if ( '' === $value ) {
			return null;
		}

		return $value;
	}

	/**
	 * Get date from meta.
	 *
	 * @param int    $id  Post ID.
	 * @param string $key Key.
	 *
	 * @return DateTime|null
	 */
	public function get_meta_date( $id, $key ) {
		$value = $this->get_meta( $id, $key );

		if ( empty( $value ) ) {
			return null;
		}

		try {
			$date = new DateTime( $value, new DateTimeZone( 'UTC' ) );
		} catch ( Exception $e ) {
			$date = null;
		}

		return $date;
	}

	/**
	 * Get string from meta.
	 *
	 * @param int    $id  Post ID.
	 * @param string $key Key.
	 *
	 * @return string|null
	 */
	public function get_meta_string( $id, $key ) {
		$value = $this->get_meta( $id, $key );

		if ( empty( $value ) ) {
			return null;
		}

		return strval( $value );
	}

	/**
	 * Get int from meta.
	 *
	 * @param int    $id  Post ID.
	 * @param string $key Key.
	 *
	 * @return int|null
	 */
	public function get_meta_int( $id, $key ) {
		$value = $this->get_meta( $id, $key );

		if ( empty( $value ) ) {
			return null;
		}

		return intval( $value );
	}

	/**
	 * Get bool from meta.
	 *
	 * Please note:
	 *
	 * ```
	 * update_post_meta( 1, '_test_bool', false );
	 * $test = get_post_meta( 1, 'test_bool', true );
	 * var_dump( $test );
	 * // string(0) ""
	 * ```
	 *
	 * ```
	 * delete_post_meta( 1, '_test_bool' );
	 * $test = get_post_meta( 1, 'test_bool', true );
	 * var_dump( $test );
	 * // string(0) ""
	 * ```
	 *
	 * ```
	 * delete_post_meta( 1, '_test_bool' );
	 * $test = get_post_meta( 1, 'test_bool' );
	 * var_dump( $test );
	 * // array(0) { }
	 * ```
	 *
	 * ```
	 * update_post_meta( 1, '_test_bool', true );
	 * $test = get_post_meta( 1, 'test_bool' );
	 * var_dump( $test );
	 * // array(1) { [0]=> string(0) "" }
	 * ```
	 *
	 * @param int    $id  Post ID.
	 * @param string $key Key.
	 *
	 * @return bool|null
	 */
	public function get_meta_bool( $id, $key ) {
		$meta_key = $this->get_meta_key( $key );

		$value = get_post_meta( $id, $meta_key );

		if ( empty( $value ) ) {
			return null;
		}

		$value = get_post_meta( $id, $meta_key, true );

		return boolval( $value );
	}

	/**
	 * Update meta.
	 *
	 * @param int    $id    Post ID.
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @return int|bool
	 */
	public function update_meta( $id, $key, $value ) {
		if ( empty( $value ) ) {
			return false;
		}

		if ( $value instanceof \DateTimeInterface ) {
			$value = $this->get_mysql_utc_date( $value );
		}

		// Use non-locale aware float value.
		// @link http://php.net/sprintf.
		if ( is_float( $value ) ) {
			$value = sprintf( '%F', $value );
		}

		$meta_key = $this->get_meta_key( $key );

		$result = update_post_meta( $id, $meta_key, $value );

		return $result;
	}
}
