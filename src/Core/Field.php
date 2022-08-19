<?php
/**
 * Field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Html\Element;

/**
 * Field class
 */
class Field {
	/**
	 * ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Label.
	 *
	 * @var string|null
	 */
	private $label;

	/**
	 * Required.
	 *
	 * @var bool
	 */
	private $required = false;

	/**
	 * Construct field
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
	 * @return string|null
	 */
	public function get_label() : ?string {
		return $this->label;
	}

	/**
	 * Set label.
	 *
	 * @param string|null $label Label.
	 */
	public function set_label( ?string $label ) : void {
		$this->label = $label;
	}

	/**
	 * Set required.
	 *
	 * @param bool $required Required.
	 */
	public function set_required( bool $required ) : void {
		$this->required = $required;
	}

	/**
	 * Is required.
	 *
	 * @return bool
	 */
	public function is_required() : bool {
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
}
