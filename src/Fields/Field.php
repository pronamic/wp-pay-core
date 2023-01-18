<?php
/**
 * Field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Fields;

use JsonSerializable;

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
	 * Get HTML attributes.
	 *
	 * @return array<string, string>
	 */
	protected function get_html_attributes() {
		return [];
	}

	/**
	 * Render.
	 *
	 * @return string
	 */
	public function render() {
		return '';
	}

	/**
	 * Print output.
	 *
	 * @return int
	 */
	public function output() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return print $this->render();
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
