<?php
/**
 * Select field option group
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use Pronamic\WordPress\Html\Element;

/**
 * Select field option group class
 */
class SelectFieldOptionGroup {
	/**
	 * Label.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Options.
	 *
	 * @var SelectFieldOption[]
	 */
	public $options = [];

	/**
	 * Construct select field option group.
	 *
	 * @param string $label Label.
	 */
	public function __construct( $label ) {
		$this->label = $label;
	}

	/**
	 * Get element.
	 *
	 * @return Element
	 */
	public function get_element() {
		$element = new Element(
			'optgroup',
			[
				'label' => $this->label,
			]
		);

		foreach ( $this->options as $option ) {
			$element->children[] = $option->get_element();
		}

		return $element;
	}
}
