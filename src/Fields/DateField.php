<?php
/**
 * Date field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use Pronamic\WordPress\Html\Element;

/**
 * Date field class
 */
class DateField extends Field {
	/**
	 * Get element.
	 *
	 * @return Element|null
	 */
	protected function get_element() {
		$element = new Element(
			'input',
			[
				'type' => 'date',
				'id'   => $this->get_id(),
				'name' => $this->get_id(),
			]
		);

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

		$data['type'] = 'date';

		return $data;
	}
}
