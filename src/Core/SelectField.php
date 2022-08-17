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
	/**
	 * Options.
	 *
	 * @var array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private $options = [];

	/**
	 * Get options.
	 *
	 * @return array<SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get flat options.
	 *
	 * @return SelectFieldOption[]
	 */
	public function get_flat_options() {
		$options = [];

		foreach ( $this->get_options() as $child ) {
			if ( $child instanceof SelectFieldOption ) {
				$options[] = $child;
			}

			if ( $child instanceof SelectFieldOptionGroup ) {
				$options[] = new SelectFieldOption( '', $child->label );

				foreach ( $child->options as $option ) {
					$options[] = new SelectFieldOption( $option->value, "\t" . $option->content );
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
