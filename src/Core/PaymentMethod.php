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
	 * Name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Status.
	 *
	 * @var string
	 */
	private $status;

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
	public function set_status( $status ) {
		$this->status = $status;
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

	/**
	 * Get field by identifier.
	 *
	 * @param string $id Field identifier.
	 * @return Field|null
	 */
	public function get_field( $id ) : ?Field {
		$fields = array_filter(
			$this->get_fields(),
			function( $field ) use ( $id ) {
				return $field->get_id() === $id;
			}
		);

		$field = reset( $fields );

		if ( false !== $field ) {
			return $field;
		}

		return null;
	}
}
