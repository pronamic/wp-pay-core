<?php
/**
 * Admin Dashboard
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin dashboard
 *
 * @author Remco Tolsma
 * @version 3.7.0
 * @since 3.7.0
 */
class AdminDashboard {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initializes admin dashboard object.
	 *
	 * @link https://github.com/WordImpress/Give/blob/1.1/includes/admin/dashboard-widgets.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-dashboard.php
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
	}

	/**
	 * Setup.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_add_dashboard_widget/
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
			array( $this, 'status_widget' )
		);
	}

	/**
	 * Status widget.
	 */
	public function status_widget() {
		$counts = wp_count_posts( 'pronamic_payment' );

		$states = array(
			/* translators: %s: posts count value */
			'payment_completed' => __( '%s completed', 'pronamic_ideal' ),
			/* translators: %s: posts count value */
			'payment_pending'   => __( '%s pending', 'pronamic_ideal' ),
			/* translators: %s: posts count value */
			'payment_cancelled' => __( '%s cancelled', 'pronamic_ideal' ),
			/* translators: %s: posts count value */
			'payment_failed'    => __( '%s failed', 'pronamic_ideal' ),
			/* translators: %s: posts count value */
			'payment_expired'   => __( '%s expired', 'pronamic_ideal' ),
		);

		$url = add_query_arg(
			array(
				'post_type' => 'pronamic_payment',
			),
			admin_url( 'edit.php' )
		);

		include __DIR__ . '/../../views/widget-payments-status-list.php';
	}
}
