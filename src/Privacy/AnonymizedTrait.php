<?php
/**
 * Anonymized Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Privacy;

/**
 * Anonymized Trait
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
trait AnonymizedTrait {
	/**
	 * Is anonymized.
	 *
	 * @var bool|null
	 */
	private $anonymized;

	/**
	 * Is anonymized?
	 *
	 * @return bool
	 */
	public function is_anonymized() {
		return ( true === $this->anonymized );
	}

	/**
	 * Set anonymized.
	 *
	 * @param bool|null $anonymized Anonymized.
	 * @return void
	 */
	public function set_anonymized( $anonymized ) {
		$this->anonymized = $anonymized;
	}
}
