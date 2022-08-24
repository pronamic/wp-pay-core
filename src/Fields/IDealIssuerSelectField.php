<?php
/**
 * Select field iDEAL issuer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

/**
 * Select field iDEAL issuer class
 */
class IDealIssuerSelectField extends SelectField {
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

		$attributes['id']   = $this->get_id();
		$attributes['name'] = $this->get_id();

		return $attributes;
	}

	/**
	 * Get options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function get_options() {
		$options = parent::get_options();

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
