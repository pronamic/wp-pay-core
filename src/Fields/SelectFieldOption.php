<?php
/**
 * Select field option
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use JsonSerializable;
use Pronamic\WordPress\Html\Element;

/**
 * Select field option class
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/components/select-control/#options
 * @link https://github.com/WordPress/gutenberg/tree/trunk/packages/components/src/select-control#options
 */
class SelectFieldOption implements JsonSerializable {
	/**
	 * Value.
	 *
	 * @var string
	 */
	public $value;

	/**
	 * Label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Construct select field option.
	 *
	 * @param string $value Value.
	 * @param string $label Label.
	 */
	public function __construct( string $value, string $label ) {
		$this->value = $value;
		$this->label = $label;
	}

	/**
	 * Get element.
	 *
	 * @return Element
	 */
	public function get_element() {
		$element = new Element(
			'option',
			[
				'value' => $this->value,
			]
		);

		$element->children[] = $this->label;

		return $element;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @link https://developer.wordpress.org/block-editor/reference-guides/components/select-control/
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'value' => $this->value,
			'label' => $this->label,
		];
	}
}
