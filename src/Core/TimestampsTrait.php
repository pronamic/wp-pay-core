<?php
/**
 * Timestamps Trait
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Privacy
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Timestamps Trait
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 * @link    https://github.com/laravel/framework/blob/v7.27.0/src/Illuminate/Database/Eloquent/Concerns/HasTimestamps.php
 */
trait TimestampsTrait {
	/**
	 * Created At.
	 *
	 * @var \DateTime|null
	 */
	private $created_at;

	/**
	 * Updated At.
	 *
	 * @var \DateTime|null
	 */
	private $updated_at;

	/**
	 * Set created at.
	 *
	 * @param \DateTime|null $created_at Created at.
	 * @return void
	 */
	public function set_created_at( $created_at ) {
		$this->created_at = $created_at;
	}

	/**
	 * Get created at.
	 *
	 * @return \DateTime|null
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * Set updated at.
	 *
	 * @param \DateTime|null $updated_at Updated at.
	 * @return void
	 */
	public function set_updated_at( $updated_at ) {
		$this->updated_at = $updated_at;
	}

	/**
	 * Get updated at.
	 *
	 * @return \DateTime|null
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * Touch.
	 *
	 * @return void
	 */
	public function touch() {
		if ( null === $this->created_at ) {
			$this->created_at = new \DateTime();
		}

		$this->updated_at = new \DateTime();
	}
}
