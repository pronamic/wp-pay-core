<?php
/**
 * Gender.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Gender.
 *
 * @author  Remco Tolsma
 * @since   2.1.0
 * @version 2.0.8
 */
class Gender {
	/**
	 * Female.
	 *
	 * @var string
	 */
	const FEMALE = 'F';

	/**
	 * Male.
	 *
	 * @var string
	 */
	const MALE = 'M';

	/**
	 * Other.
	 *
	 * @link https://en.wikipedia.org/wiki/Legal_recognition_of_non-binary_gender
	 *
	 * @var string
	 */
	const OTHER = 'X';

	/**
	 * Check if value is valid.
	 *
	 * @param string $gender Gender.
	 * @return boolean True if valid, false otherwise.
	 */
	public static function is_valid( $gender ) {
		return in_array(
			$gender,
			[
				self::FEMALE,
				self::MALE,
				self::OTHER,
			],
			true
		);
	}
}
