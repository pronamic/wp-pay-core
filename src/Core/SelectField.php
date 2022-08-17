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

	private function ensure_option( $key, $value ) {
		if ( $value instanceof SelectFieldOption ) {
			return $value;
		}

		if ( $value instanceof SelectFieldOptionGroup ) {
			return $value;
		}

		return new SelectFieldOption( $key, $value );
	}

	private function ensure_option_group( $item ) {
		if ( ! \array_key_exists( 'name', $item ) ) {
			throw new \Exception( 'Unexpected option.' );
		}

		if ( ! \array_key_exists( 'options', $item ) ) {
			throw new \Exception( 'Unexpected option.' );
		}

		$group = new SelectFieldOptionGroup( $item['name'] );

		foreach ( $item['options'] as $key => $value ) {
			$group->options[] = new SelectFieldOption( $key, $value );
		}

		return $group;
	}

	private function ensure_children( $options ) {
		$children = [];

		foreach ( $options as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $item ) {
					$children[] = $this->ensure_option_group( $item );
				}
			}

			if ( ! is_array( $value ) ) {
				$children[] = $this->ensure_option( $key, $value );
			}
		}
	}

	public function get_children() {
		$options = $this->get_options();

		$children = $this->ensure_children( $options );

		return $children;
	}

	public function get_flat_options() {
		$options = [];

		foreach ( $this->get_children() as $child ) {
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
		$element = new Element( 'select', [] );

		foreach ( $this->get_children() as $child ) {
			$element->children[] = $child->render();
		}

		return $element->render();
	}
}
