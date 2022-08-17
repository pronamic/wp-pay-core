<?php
/**
 * Select field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Html\Element;

/**
 * Select field class
 */
class SelectField extends Field {
	public function set_options_callback( $options_callback ) {
		$this->options_callback = $options_callback;
	}

	/**
	 * Get options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function get_options() {
		return call_user_func( $this->options_callback );
	}

	public function get_flat_options() {
		$options = [];

		foreach ( $this->get_options() as $child ) {
			if ( $child instanceof SelectFieldOption ) {
				$options[ $child->value ] = $child->content;
			}

			if ( $child instanceof SelectFieldOptionGroup ) {
				foreach ( $child->options as $option ) {
					$options[ $option->value ] = $option->content;
				}
			}
		}

		return $options;
	}

	public function render() {
		$element = new Element( 'select', $this->get_html_attributes() );

		foreach ( $this->get_options() as $child ) {
			$element->children[] = $child->render();
		}

		return $element->render();
	}
}
