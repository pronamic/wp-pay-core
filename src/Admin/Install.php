<?php
/**
 * Install
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\AbstractIntegration;
use Pronamic\WordPress\Pay\Forms\FormPostType;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Upgrades\Upgrade620;

/**
 * WordPress admin install
 *
 * @author  Remco Tolsma
 * @version 2.3.2
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

		add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Install.
		if ( get_option( 'pronamic_pay_version' ) !== $this->plugin->get_version() ) {
			$this->install();
		}

		// Notices.
		add_action( 'admin_notices', array( $this, 'admin_notice_upgrades_available' ), 20 );
		add_action( 'admin_notices', array( $this, 'admin_notice_upgraded' ), 20 );

		// Maybe update database.
		if ( filter_has_var( INPUT_GET, 'pronamic_pay_upgrade' ) && wp_verify_nonce( filter_input( INPUT_GET, 'pronamic_pay_nonce', FILTER_SANITIZE_STRING ), 'pronamic_pay_upgrade' ) ) {
			$this->upgrade();

			/**
			 * Redirect to admin dashboard or referer.
			 *
			 * @link https://developer.wordpress.org/reference/functions/admin_url/
			 * @link https://developer.wordpress.org/reference/functions/wp_get_referer/
			 * @link https://developer.wordpress.org/reference/functions/wp_safe_redirect/
			 */
			$location = admin_url();

			$referer = wp_get_referer();

			if ( false !== $referer ) {
				$location = $referer;
			}

			$location = add_query_arg(
				array(
					'pronamic_pay_upgrade'  => false,
					'pronamic_pay_nonce'    => false,
					'pronamic_pay_upgraded' => true,
				),
				$location
			);

			wp_safe_redirect( $location );

			exit;
		}
	}

	/**
	 * Removable query arguments.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/misc.php#L1204-L1230
	 * @link https://developer.wordpress.org/reference/functions/wp_removable_query_args/
	 * @param array $args Arguments.
	 * @return array
	 */
	public function removable_query_args( $args ) {
		$args[] = 'pronamic_pay_upgraded';

		return $args;
	}

	/**
	 * Install.
	 *
	 * @return void
	 */
	private function install() {
		// Roles.
		$this->create_roles();

		// Rewrite Rules.
		flush_rewrite_rules();

		// Version.
		$version = $this->plugin->get_version();

		$current_version = get_option( 'pronamic_pay_version', null );

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

		// Set database version option.
		$db_version = \get_option( 'pronamic_pay_db_version', null );

		if ( null === $db_version ) {
			\update_option( 'pronamic_pay_db_version', $this->plugin->get_version() );
		}

		// Update version.
		update_option( 'pronamic_pay_version', $version );
	}

	/**
	 * Admin notice upgrades.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/admin_notices/
	 * @return void
	 */
	public function admin_notice_upgrades_available() {
		if ( ! $this->requires_upgrade() ) {
			return;
		}

		include __DIR__ . '/../../views/notice-upgrade.php';
	}

	/**
	 * Admin notice upgraded.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/admin_notices/
	 * @return void
	 */
	public function admin_notice_upgraded() {
		$upgraded = filter_input( INPUT_GET, 'pronamic_pay_upgraded', FILTER_VALIDATE_BOOLEAN );

		if ( true !== $upgraded ) {
			return;
		}

		include __DIR__ . '/../../views/notice-upgraded.php';
	}

	/**
	 * Create roles.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 * @link https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/class-wc-install.php#L519-L562
	 * @link https://github.com/woothemes/woocommerce/blob/v2.2.3/includes/class-wc-post-types.php#L245
	 * @return void
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
	 * Get upgradeable integrations.
	 *
	 * @return array<AbstractIntegration>
	 */
	private function get_upgradeable_integrations() {
		$integrations = $this->plugin->integrations;

		$integrations = array_filter(
			$integrations,
			/**
			 * Filter integration with version option name.
			 *
			 * @param AbstractIntegration $integration Integration object.
			 * @return bool True if integration has version option name, false otherwise.
			 */
			function( $integration ) {
				if ( ! $integration->is_active() ) {
					return false;
				}

				if ( null === $integration->get_db_version_option_name() ) {
					return false;
				}

				if ( ! $integration->get_upgrades()->are_executable() ) {
					return false;
				}

				return true;
			}
		);

		return $integrations;
	}

	/**
	 * Requires upgrade.
	 *
	 * @return bool True if database update is required, false otherwise.
	 */
	public function requires_upgrade() {
		$current_db_version = get_option( 'pronamic_pay_db_version' );

		if (
			// Check for old database version notation without dots, for example `366`.
			false === strpos( $current_db_version, '.' )
				||
			version_compare( $current_db_version, max( $this->db_updates ), '<' )
		) {
			return true;
		}

		// Integrations.
		$integrations = $this->get_upgradeable_integrations();

		foreach ( $integrations as $integration ) {
			$version_option = $integration->get_db_version_option();

			if ( null === $version_option ) {
				continue;
			}

			$upgrades = $integration->get_upgrades();

			foreach ( $upgrades as $upgrade ) {
				if ( version_compare( $version_option, $upgrade->get_version(), '<' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Upgrade.
	 *
	 * @return void
	 */
	public function upgrade() {
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

		// Integrations.
		$integrations = $this->get_upgradeable_integrations();

		foreach ( $integrations as $integration ) {
			$db_version_option_name = $integration->get_db_version_option_name();

			if ( null === $db_version_option_name ) {
				continue;
			}

			$db_version_option = \strval( $integration->get_db_version_option() );

			$upgrades = $integration->get_upgrades();

			foreach ( $upgrades as $upgrade ) {
				$version = $upgrade->get_version();

				if ( ! version_compare( $db_version_option, $version, '<' ) ) {
					continue;
				}

				$upgrade->execute();

				update_option( $db_version_option_name, $version );
			}
		}

		update_option( 'pronamic_pay_db_version', $this->plugin->get_version() );
	}
}
