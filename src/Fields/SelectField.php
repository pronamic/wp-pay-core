<?php
/**
 * Select field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use Pronamic\WordPress\Html\Element;

/**
 * Select field class
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/components/select-control/
 * @link https://github.com/WordPress/gutenberg/tree/trunk/packages/components/src/select-control
 */
class SelectField extends Field {
	/**
	 * Options.
	 *
	 * @var iterable<SelectFieldOption|SelectFieldOptionGroup>
	 */
	private $options = [];

	/**
	 * Get options.
	 *
	 * @return iterable<SelectFieldOption|SelectFieldOptionGroup>
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Set options.
	 *
	 * @param iterable<SelectFieldOption|SelectFieldOptionGroup> $options Options.
	 */
	public function set_options( $options ) {
		$this->options = $options;
	}

	/**
	 * Get flat options.
	 *
	 * @return iterable<SelectFieldOption>
	 */
	public function get_flat_options() {
		$options = [];

		foreach ( $this->get_options() as $child ) {
			if ( $child instanceof SelectFieldOption ) {
				$options[] = $child;
			}

			if ( $child instanceof SelectFieldOptionGroup ) {
				foreach ( $child->options as $option ) {
					$options[] = $option;
				}
			}
		}

		return $options;
	}

	/**
	 * Render field.
	 *
	 * @return string
	 */
	public function render() {
		$element = new Element( 'select', $this->get_html_attributes() );

		foreach ( $this->get_options() as $child ) {
			$element->children[] = $child->render();
		}

		return $element->render();
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$data = parent::jsonSerialize();

		$data['type']    = 'select';
		$data['options'] = $this->get_flat_options();

		return $data;
	}
}
