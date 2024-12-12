<?php
/**
 * Select field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
	 * @param iterable<SelectFieldOption|SelectFieldOptionGroup|CachedCallbackOptions> $options Options.
	 * @return void
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
	 * Get element.
	 *
	 * @return Element|null
	 */
	protected function get_element() {
		$element = new Element(
			'select',
			[
				'id'   => $this->get_id(),
				'name' => $this->get_id(),
			]
		);

		foreach ( $this->get_options() as $child ) {
			$element->children[] = $child->get_element();
		}

		return $element;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$data = parent::jsonSerialize();

		$data['type']    = 'select';
		$data['options'] = [];

		try {
			$data['options'] = $this->get_flat_options();
		} catch ( \Exception $e ) {
			$data['error'] = $e->getMessage();
		}

		return $data;
	}
}
