<?php
/**
 * Field
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
 * Field class
 */
class Field implements JsonSerializable {
	/**
	 * ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Label.
	 *
	 * @var string
	 */
	private $label = '';

	/**
	 * Required.
	 *
	 * @var bool
	 */
	private $required = false;

	/**
	 * Meta key.
	 *
	 * @var string
	 */
	public $meta_key = '';

	/**
	 * Construct field.
	 *
	 * @param string $id ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;

		$this->setup();
	}

	/**
	 * Setup field.
	 *
	 * @return void
	 */
	protected function setup() {
	}

	/**
	 * Get ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Set label.
	 *
	 * @param string $label Label.
	 */
	public function set_label( string $label ): void {
		$this->label = $label;
	}

	/**
	 * Set required.
	 *
	 * @param bool $required Required.
	 */
	public function set_required( bool $required ): void {
		$this->required = $required;
	}

	/**
	 * Is required.
	 *
	 * @return bool
	 */
	public function is_required(): bool {
		return $this->required;
	}

	/**
	 * Get element.
	 *
	 * @return Element|null
	 */
	protected function get_element() {
		return null;
	}

	/**
	 * Render.
	 *
	 * @return string
	 */
	public function render() {
		$element = $this->get_element();

		if ( null === $element ) {
			return '';
		}

		return $element->render();
	}

	/**
	 * Output.
	 *
	 * @return void
	 */
	public function output() {
		$element = $this->get_element();

		if ( null === $element ) {
			return;
		}

		$element->output();
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'type'     => '',
			'id'       => $this->id,
			'label'    => $this->label,
			'required' => $this->required,
		];
	}
}
