<?php
/**
 * Admin About Page
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin about
 *
 * @version 2.2.6
 * @since   1.0.0
 */
class AdminAboutPage {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * File.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Constructs and initializes admin about page object.
	 *
	 * @link https://github.com/WordImpress/Give/blob/1.1/includes/admin/dashboard-widgets.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin.php
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-dashboard.php
	 *
	 * @param Plugin $plugin Plugin.
	 * @param string $file   About page file.
	 */
	public function __construct( Plugin $plugin, $file ) {
		$this->plugin = $plugin;
		$this->file   = $file;

		add_action( 'admin_menu', $this->admin_menu( ... ) );
		add_action( 'admin_head', $this->admin_head( ... ) );

		add_action( 'pronamic_pay_install', $this->install( ... ) );
	}

	/**
	 * Add admin menus/screens.
	 *
	 * @return void
	 */
	public function admin_menu() {
		/* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		if ( ! \array_key_exists( 'page', $_GET ) || 'pronamic-pay-about' !== $_GET['page'] ) {
			return;
		}

		$hook_suffix = add_dashboard_page(
			__( 'About Pronamic Pay', 'pronamic_ideal' ),
			__( 'Welcome to Pronamic Pay', 'pronamic_ideal' ),
			'manage_options',
			'pronamic-pay-about',
			$this->render_page( ... )
		);

		if ( false === $hook_suffix ) {
			return;
		}

		add_action( 'admin_print_styles-' . $hook_suffix, $this->admin_css( ... ) );
	}

	/**
	 * Admin head.
	 *
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'pronamic-pay-about' );
	}

	/**
	 * Admin CSS.
	 *
	 * @return void
	 */
	public function admin_css() {
		// @link https://github.com/WordPress/WordPress/blob/4.7/wp-includes/default-constants.php#L83-L93.
		$min = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'pronamic-pay-admin-about',
			plugins_url( '../../css/admin-about' . $min . '.css', __FILE__ ),
			[],
			$this->plugin->get_version()
		);
	}

	/**
	 * Get file version.
	 *
	 * @param string $file Absolute path to the file.
	 * @return string
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.7.0/includes/admin/class-wc-admin-status.php#L144-L176
	 * @link https://github.com/WordPress/WordPress/blob/5.2/wp-includes/functions.php#L5546-L5605
	 * @link https://github.com/WordPress/WordPress/blob/5.2/wp-includes/functions.php#L5479-L5492
	 * @throws \Exception Throws exception when reading file version fails.
	 */
	private function get_file_version( $file ) {
		$data = \get_file_data(
			$file,
			[
				'Version' => 'Version',
			]
		);

		$version = '';

		if ( \array_key_exists( 'Version', $data ) ) {
			$version = $data['Version'];
		}

		return $version;
	}

	/**
	 * Get file.
	 *
	 * @return string
	 */
	private function get_file() {
		return $this->file;
	}

	/**
	 * Get version.
	 *
	 * @return string
	 *
	 * @throws \Exception Throws exception if file could not be opened or read.
	 */
	public function get_version() {
		return $this->get_file_version( $this->get_file() );
	}

	/**
	 * Render about page.
	 *
	 * @return void
	 */
	public function render_page() {
		include $this->get_file();
	}

	/**
	 * Install.
	 *
	 * @return void
	 */
	public function install() {
		$current_version = \get_option( 'pronamic_pay_version', null );

		try {
			$about_page_version = $this->get_version();
		} catch ( \Exception ) {
			$about_page_version = '';
		}

		$about_page_version_viewed = \get_option( 'pronamic_pay_about_page_version', null );

		$tab = null;

		if ( null === $current_version ) {
			// No version? This is a new install :).
			$tab = 'getting-started';
		} elseif ( \version_compare( $about_page_version_viewed, $about_page_version, '<' ) ) {
			// Show about page only if viewed version is lower then current version.
			$tab = 'new';
		}

		if ( null !== $tab ) {
			$url = \add_query_arg(
				[
					'page' => 'pronamic-pay-about',
					'tab'  => $tab,
				],
				\admin_url( 'index.php' )
			);

			\set_transient( 'pronamic_pay_admin_redirect', $url, 3600 );
		}

		\update_option( 'pronamic_pay_about_page_version', $about_page_version );
	}
}
