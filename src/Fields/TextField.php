<?php
/**
 * Text field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use Pronamic\WordPress\Html\Element;

/**
 * Text field class
 */
class TextField extends Field {
	/**
	 * Get HTML attributes.
	 *
	 * @return array<string, string>
	 */
	protected function get_html_attributes(): array {
		$attributes = parent::get_html_attributes();

		$attributes['type'] = 'text';
		$attributes['id']   = $this->get_id();
		$attributes['name'] = $this->get_id();

		return $attributes;
	}

	/**
	 * Render field.
	 *
	 * @return string
	 */
	public function render(): string {
		$element = new Element( 'input', $this->get_html_attributes() );

		return $element->render();
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		$data = parent::jsonSerialize();

		$data['type'] = 'input';

		return $data;
	}
}
