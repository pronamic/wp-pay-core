<?php
/**
 * Source Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Source Trait
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
trait SourceTrait {
	/**
	 * Identifier for the source which started this payment info.
	 * For example: 'woocommerce', 'gravityforms', 'easydigitaldownloads', etc.
	 *
	 * @var string|null
	 */
	public $source;

	/**
	 * Unique ID at the source which started this payment info, for example:
	 * - WooCommerce order ID.
	 * - Easy Digital Downloads payment ID.
	 * - Gravity Forms entry ID.
	 *
	 * @var string|int|null
	 */
	public $source_id;

	/**
	 * Get the source identifier of this payment.
	 *
	 * @return string|null
	 */
	public function get_source() {
		return $this->source;
	}

	/**
	 * Set the source of this payment.
	 *
	 * @param string|null $source Source.
	 * @return void
	 */
	public function set_source( $source ) {
		$this->source = $source;
	}

	/**
	 * Get the source ID of this payment.
	 *
	 * @return string|int|null
	 */
	public function get_source_id() {
		return $this->source_id;
	}

	/**
	 * Set the source ID of this payment.
	 *
	 * @param string|int|null $source_id Source ID.
	 * @return void
	 */
	public function set_source_id( $source_id ) {
		$this->source_id = $source_id;
	}
}
