<?php
/**
 * Select field option group
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Html\Element;

/**
 * Select field option group class
 */
class SelectFieldOptionGroup {
	public $options = [];

	public function __construct( $label ) {
		$this->label = $label;
	}

	public function render() {
		$element = new Element( 'optgroup', [
			'label' => $this->label,
		] );

		foreach ( $this->options as $option ) {
			$element->children[] = $option->render();
		}

		return $element->render();
	}
}
