<?php
/**
 * Payment method
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

use Pronamic\WordPress\Pay\Fields\Field;

/**
 * Payment method class
 */
class PaymentMethod {
	/**
	 * ID.
	 */
	private string $id;

	/**
	 * Name.
	 */
	private string $name;

	/**
	 * Status.
	 */
	private string $status;

	/**
	 * Fields.
	 *
	 * @var Field[]
	 */
	private array $fields = [];

	/**
	 * Supports.
	 */
	use SupportsTrait;

	/**
	 * Construct payment method.
	 *
	 * @param string $id ID.
	 */
	public function __construct( $id ) {
		$this->id     = $id;
		$this->name   = (string) PaymentMethods::get_name( $id );
		$this->status = '';
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
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string $name Name.
	 * @return void
	 */
	public function set_name( $name ): void {
		$this->name = $name;
	}

	/**
	 * Get status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set status.
	 *
	 * @param string $status Status.
	 * @return void
	 */
	public function set_status( $status ): void {
		$this->status = $status;
	}

	/**
	 * Add field.
	 *
	 * @param Field $field Field.
	 * @return void
	 */
	public function add_field( Field $field ): void {
		$this->fields[] = $field;
	}

	/**
	 * Get fields.
	 *
	 * @return Field[]
	 */
	public function get_fields() {
		return $this->fields;
	}
}
