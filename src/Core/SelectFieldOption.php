<?php
/**
 * Select field option
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Html\Element;

/**
 * Select field option class
 */
class SelectFieldOption {
	/**
	 * Construct select field option.
	 *
	 * @param string $value   Value.
	 * @param string $content Content.
	 */
	public function __construct( $value, $content ) {
		$this->value   = $value;
		$this->content = $content;
	}

	/**
	 * Render field.
	 *
	 * @return string
	 */
	public function render() {
		$element = new Element(
			'option',
			[
				'value' => $this->value,
			]
		);

		$element->children[] = $this->content;

		return $element->render();
	}
}
