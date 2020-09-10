<?php
/**
 * Subscription Interval
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscription Interval
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.4.0
 */
class SubscriptionInterval extends \DateInterval implements \JsonSerializable {
	/**
	 * Specification.
	 *
	 * @var string
	 */
	private $specification;

	/**
	 * Construct interval.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_8601#Durations
	 * @link https://www.php.net/manual/en/dateinterval.construct.php
	 * @link https://github.com/php/php-src/blob/php-7.4.10/ext/date/php_date.c#L414-L416
	 * @var string $specification An interval specification.
	 */
	public function __construct( $specification ) {
		$this->specification = $specification;

		parent::__construct( $specification );
	}

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return string
	 */
	public function jsonSerialize() {
		return $this->specification;
	}

	/**
	 * To string.
	 *
	 * @link https://www.php.net/manual/en/language.oop5.magic.php#object.tostring
	 * @return string
	 */
	public function __toString() {
		return $this->specification;
	}
}
