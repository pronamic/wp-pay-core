<?php
/**
 * WordPress compatibility functions.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @link      https://github.com/WordPress/WordPress/blob/master/wp-includes/compat.php
 */

if ( ! function_exists( 'wp_doing_cron' ) ) {
	/**
	 * Compat function to mimic wp_doing_cron().
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.9/wp-includes/load.php#L1066-L1082
	 * @ignore
	 * @since 2.1.2
	 *
	 * @return bool True if it's a WordPress cron request, false otherwise.
	 */
	function wp_doing_cron() {
		/**
		 * Filters whether the current request is a WordPress cron request.
		 *
		 * @since 4.8.0
		 *
		 * @param bool $wp_doing_cron Whether the current request is a WordPress cron request.
		 */
		return apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
	}
}
