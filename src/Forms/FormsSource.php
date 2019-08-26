<?php
/**
 * Form Source
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

/**
 * Form Source
 *
 * @author  Reüel van der Steege
 * @version 2.1.7
 * @since   2.1.7
 */
class FormsSource {
	/**
	 * Payment form.
	 *
	 * @var string
	 */
	const PAYMENT_FORM = 'payment_form';

	/**
	 * Block payment form.
	 *
	 * @var string
	 */
	const BLOCK_PAYMENT_FORM = 'block_payment_form';

	/**
	 * Is valid source?
	 *
	 * @param string $source Source string to validate.
	 *
	 * @return bool
	 */
	public static function is_valid( $source ) {
		$sources = array(
			self::BLOCK_PAYMENT_FORM,
			self::PAYMENT_FORM,
		);

		return in_array( $source, $sources, true );
	}
}
