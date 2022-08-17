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
	public function __construct( $value, $content ) {
		$this->value   = $value;
		$this->content = $content;
	}

	public function render() {
		$element = new Element( 'option', [
			'value' => $this->value,
		] );

		$element->children[] = $this->content;

		return $element->render();
	}
}
