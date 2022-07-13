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

/**
 * Payment method class
 */
class PaymentMethod {
	/**
	 * ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Fields.
	 *
	 * @var Field[]
	 */
	private $fields = [];

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
		$this->id = $id;
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
	 * Add field.
	 *
	 * @param Field $field Field.
	 * @return void
	 */
	public function add_field( Field $field ) {
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
