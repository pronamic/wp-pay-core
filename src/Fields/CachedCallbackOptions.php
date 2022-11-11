<?php
/**
 * Cached callback options
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use ArrayIterator;
use IteratorAggregate;

/**
 * Cached callback options class
 *
 * @phpstan-implements IteratorAggregate<SelectFieldOption|SelectFieldOptionGroup>
 */
class CachedCallbackOptions implements IteratorAggregate {
	/**
	 * Cache key.
	 */
	private string $cache_key;

	/**
	 * Callback.
	 *
	 * @var callable: array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private $callback;

	/**
	 * Construct cached callback options.
	 *
	 * @param callable $callback  Callback.
	 * @param string   $cache_key Cache key.
	 */
	public function __construct( $callback, $cache_key ) {
		$this->callback  = $callback;
		$this->cache_key = $cache_key;
	}

	/**
	 * Get iterator.
	 *
	 * @return ArrayIterator<int, SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function getIterator() {
		$options = $this->get_transient_options();

		return new ArrayIterator( $options );
	}

	/**
	 * Get callback options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private function get_callback_options() {
		return \call_user_func( $this->callback );
	}

	/**
	 * Get transient options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private function get_transient_options() {
		if ( '' === $this->cache_key ) {
			return $this->get_callback_options();
		}

		$options = \get_transient( $this->cache_key );

		if ( false === $options ) {
			$options = $this->get_callback_options();

			\set_transient( $this->cache_key, $options, \DAY_IN_SECONDS );
		}

		return $options;
	}
}
