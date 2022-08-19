<?php
/**
 * Select field iDEAL issuer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Select field iDEAL issuer class
 */
class IDealIssuerSelectField extends SelectField {
	/**
	 * Cache key.
	 *
	 * @var string
	 */
	private $cache_key = '';

	/**
	 * Options callback.
	 *
	 * @var callable:array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private $options_callback;

	/**
	 * Set cache key.
	 *
	 * @param string $cache_key Cache key.
	 */
	public function set_cache_key( $cache_key ) {
		$this->cache_key = $cache_key;
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	protected function setup() {
		parent::setup();

		$this->set_label( \__( 'Bank', 'pronamic_ideal' ) );
	}

	/**
	 * Get HTML attributes.
	 *
	 * @return array<string, string>
	 */
	protected function get_html_attributes() {
		$attributes = parent::get_html_attributes();

		$attributes['id']   = 'pronamic_ideal_issuer_id';
		$attributes['name'] = 'pronamic_ideal_issuer_id';

		return $attributes;
	}

	/**
	 * Set options callback.
	 *
	 * @param callable:array<SelectFieldOption|SelectFieldOptionGroup> $options_callback Options callback.
	 * @return void
	 */
	public function set_options_callback( $options_callback ) {
		$this->options_callback = $options_callback;
	}

	/**
	 * Get callback options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private function get_callback_options() {
		return \call_user_func( $this->options_callback );
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

			set_transient( $this->cache_key, $options, \DAY_IN_SECONDS );
		}

		return $options;
	}

	/**
	 * Get options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function get_options() {
		$options = $this->get_transient_options();

		return [
			/**
			 * The list should be accompanied by the instruction phrase "Kies uw bank" (UK: "Choose your bank"). In
			 * case of an HTML <SELECT>, the first element in the list states this instruction phrase and is selected by default (to prevent accidental Issuer selection).
			 */
			new SelectFieldOption( '', __( '— Choose your bank —', 'pronamic_ideal' ) ),

			...$options,
		];
	}
}
