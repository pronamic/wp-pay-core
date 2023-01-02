<?php
/**
 * Admin Dashboard
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin dashboard
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 3.7.0
 */
class AdminDashboard {
	/**
	 * Construct admin dashboard.
	 *
	 * @link https://github.com/WordImpress/Give/blob/1.1/includes/admin/dashboard-widgets.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-dashboard.php
	 */
	public function __construct() {
		// Actions.
		add_action( 'wp_dashboard_setup', [ $this, 'setup' ] );
	}

	/**
	 * Setup.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_add_dashboard_widget/
	 * @return void
	 */
	public function setup() {
		/**
		 * Currently we only add dashboard widgets if the
		 * current user can manage options.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		 * The `wp_add_dashboard_widget` function should exist at
		 * this point. To make tools like Psalm happy we do check
		 * if the function exists.
		 */
		if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
			return;
		}

		/**
		 * Ok, add the dashboard widget.
		 */
		wp_add_dashboard_widget(
			'pronamic_pay_dashboard_status',
			__( 'Pronamic Pay Status', 'pronamic_ideal' ),
			[ $this, 'status_widget' ]
		);
	}

	/**
	 * Status widget.
	 *
	 * @return void
	 */
	public function status_widget() {
		include __DIR__ . '/../../views/widget-payments-status-list.php';
	}
}
