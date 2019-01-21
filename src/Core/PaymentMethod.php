<?php
/**
 * Payment method
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: Payment method
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.3.11
 */
abstract class PaymentMethod {
	/**
	 * The ID of this payment method.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The name of this payment method.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Is active flag.
	 *
	 * @var boolean
	 */
	protected $is_active = true;

	/**
	 * Get the ID of this payment method.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the name of this payment method.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Is active.
	 *
	 * @return bool True if payment method is active, false otherwise.
	 */
	public function is_active() {
		return $this->is_active;
	}
}
