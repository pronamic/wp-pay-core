<?php
/**
 * Admin Notification
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin notification.
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 3.7.0
 */
class AdminNotification {
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
	 * Condition.
	 *
	 * @var bool
	 */
	private $condition;

	/**
	 * Version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructs and initializes an notices object.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/admin/class-wc-admin-notices.php
	 * @param string $id        ID.
	 * @param string $name      Name.
	 * @param bool   $condition Condition.
	 * @param string $version   Version.
	 */
	public function __construct( $id, $name, $condition, $version ) {
		$this->id        = $id;
		$this->name      = $name;
		$this->condition = $condition;
		$this->version   = $version;
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
	 * Is met.
	 *
	 * @return bool
	 */
	public function is_met() {
		return $this->condition;
	}

	/**
	 * Get version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function get_message() {
		$message = \sprintf(
			'We notice that the "%1$s" plugin is active, support for the "%1$s" plugin has been removed from the Pronamic Pay plugin since version %2$s.',
			$this->get_name(),
			$this->get_version()
		);

		return $message;
	}
}
