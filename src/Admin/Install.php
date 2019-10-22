<?php
/**
 * Install
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Forms\FormPostType;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin install
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Install {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Admin.
	 *
	 * @var AdminModule
	 */
	private $admin;

	/**
	 * Database updates.
	 *
	 * @var array
	 */
	private $db_updates = array(
		'2.0.0',
		'2.0.1',
		'3.3.0',
		'3.7.0',
		'3.7.2',
	);

	/**
	 * Constructs and initializes an install object.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/class-wc-install.php
	 *
	 * @param Plugin      $plugin Plugin.
	 * @param AdminModule $admin  Admin.
	 */
	public function __construct( Plugin $plugin, AdminModule $admin ) {
		$this->plugin = $plugin;
		$this->admin  = $admin;

		// Actions.
		add_action( 'admin_init', array( $this, 'admin_init' ), 5 );
	}

	/**
	 * Admin initialize.
	 */
	public function admin_init() {
		// Install.
		if ( get_option( 'pronamic_pay_version' ) !== $this->plugin->get_version() ) {
			$this->install();
		}

		// Maybe update database.
		if ( filter_has_var( INPUT_GET, 'pronamic_pay_update_db' ) && wp_verify_nonce( filter_input( INPUT_GET, 'pronamic_pay_nonce', FILTER_SANITIZE_STRING ), 'pronamic_pay_update_db' ) ) {
			$this->update_db();

			$this->admin->notices->remove_notice( 'update_db' );

			$this->redirect_to_about();
		}
	}

	/**
	 * Install.
	 */
	private function install() {
		// Roles.
		$this->create_roles();

		// Rewrite Rules.
		flush_rewrite_rules();

		// Version.
		$version = $this->plugin->get_version();

		$current_version = get_option( 'pronamic_pay_version', null );

		// Database update.
		if ( $this->requires_db_update() ) {
			$this->admin->notices->add_notice( 'update_db' );
		}

		// Redirect.
		if ( null !== $this->admin->about_page ) {
			try {
				$about_page_version = $this->admin->about_page->get_version();
			} catch ( \Exception $e ) {
				$about_page_version = '';
			}

			$about_page_version_viewed = get_option( 'pronamic_pay_about_page_version', null );

			$tab = null;

			if ( null === $current_version ) {
				// No version? This is a new install :).
				$tab = 'getting-started';
			} elseif ( version_compare( $about_page_version_viewed, $about_page_version, '<' ) ) {
				// Show about page only if viewed version is lower then current version.
				$tab = 'new';
			}

			if ( null !== $tab ) {
				$url = add_query_arg(
					array(
						'page' => 'pronamic-pay-about',
						'tab'  => $tab,
					),
					admin_url( 'index.php' )
				);

				set_transient( 'pronamic_pay_admin_redirect', $url, 3600 );
			}

			update_option( 'pronamic_pay_about_page_version', $about_page_version );
		}

		// Update version.
		update_option( 'pronamic_pay_version', $version );
	}

	/**
	 * Create roles.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 * @link https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/class-wc-install.php#L519-L562
	 * @link https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/class-wc-post-types.php#L245
	 */
	private function create_roles() {
		// Payer role.
		add_role(
			'payer',
			__( 'Payer', 'pronamic_ideal' ),
			array(
				'read' => true,
			)
		);

		// @link https://developer.wordpress.org/reference/functions/wp_roles/.
		$roles = wp_roles();

		// Payments.
		$payment_capabilities = PaymentPostType::get_capabilities();

		unset( $payment_capabilities['publish_posts'] );
		unset( $payment_capabilities['create_posts'] );

		foreach ( $payment_capabilities as $capability ) {
			$roles->add_cap( 'administrator', $capability );
		}

		// Forms.
		$form_capabilities = FormPostType::get_capabilities();

		foreach ( $form_capabilities as $capability ) {
			$roles->add_cap( 'administrator', $capability );
		}
	}

	/**
	 * Requires database update.
	 *
	 * @return bool True if database update is required, false othwerise.
	 */
	public function requires_db_update() {
		$current_db_version = get_option( 'pronamic_pay_db_version' );

		if (
			// Check for old database version notation without dots, for example `366`.
			false === strpos( $current_db_version, '.' )
				||
			version_compare( $current_db_version, max( $this->db_updates ), '<' )
		) {
			return true;
		}

		// Plugin integrations.
		foreach ( $this->plugin->plugin_integrations as $integration ) {
			$option_db_version  = $integration->option_db_version;
			$current_db_version = get_option( $option_db_version );

			$db_updates = $integration->get_db_update_files();

			foreach ( $db_updates as $version => $files ) {
				if ( version_compare( $current_db_version, max( $db_updates ), '<' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Update database.
	 */
	public function update_db() {
		$current_db_version = get_option( 'pronamic_pay_db_version', null );

		if ( $current_db_version ) {
			foreach ( $this->db_updates as $version ) {
				if ( ! version_compare( $current_db_version, $version, '<' ) ) {
					continue;
				}

				$file = plugin_dir_path( $this->plugin->get_file() ) . 'includes/updates/update-' . $version . '.php';

				if ( is_readable( $file ) ) {
					include $file;

					update_option( 'pronamic_pay_db_version', $version );
				}
			}
		}

		// Plugin integrations.
		foreach ( $this->plugin->plugin_integrations as $integration ) {
			$option_db_version  = $integration->option_db_version;
			$current_db_version = get_option( $option_db_version );

			$db_updates = $integration->get_db_update_files();

			foreach ( $db_updates as $version => $files ) {
				if ( ! version_compare( $current_db_version, $version, '<' ) ) {
					continue;
				}

				foreach ( $files as $file ) {
					include $file;
				}

				update_option( $option_db_version, $version );
			}
		}

		update_option( 'pronamic_pay_db_version', $this->plugin->get_version() );
	}

	/**
	 * Redirect to about.
	 */
	private function redirect_to_about() {
		wp_safe_redirect( admin_url( 'index.php?page=pronamic-pay-about' ) );

		exit;
	}
}
