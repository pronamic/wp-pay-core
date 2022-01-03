<?php
/**
 * Admin About Page
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin about
 *
 * @author  Remco Tolsma
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

		// Actions.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
	}

	/**
	 * Add admin menus/screens.
	 *
	 * @return void
	 */
	public function admin_menu() {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( 'pronamic-pay-about' !== $page ) {
			return;
		}

		$hook_suffix = add_dashboard_page(
			__( 'About Pronamic Pay', 'pronamic_ideal' ),
			__( 'Welcome to Pronamic Pay', 'pronamic_ideal' ),
			'manage_options',
			$page,
			array( $this, 'render_page' )
		);

		if ( false === $hook_suffix ) {
			return;
		}

		add_action( 'admin_print_styles-' . $hook_suffix, array( $this, 'admin_css' ) );
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
			array(),
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
		// We don't need to write to the file, so just open for reading.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$fp = \fopen( $file, 'r' );

		if ( false === $fp ) {
			throw new \Exception(
				\sprintf(
					'Could not open file to get version: %s.',
					$file
				)
			);
		}

		// Pull only the first 8kiB of the file in.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
		$file_data = \fread( $fp, 8192 );

		if ( false === $file_data ) {
			throw new \Exception(
				\sprintf(
					'Could not read file to get version: %s.',
					$file
				)
			);
		}

		// PHP will close file handle, but we are good citizens.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		\fclose( $fp );

		// Search.
		\preg_match( '/^[ \t\/*#@]*@version(?P<version>.*)$/mi', $file_data, $matches );

		// Version.
		$version = '';

		if ( \array_key_exists( 'version', $matches ) ) {
			$version = \trim( $matches['version'] );
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
}
