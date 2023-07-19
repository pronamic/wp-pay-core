<?php
/**
 * Install
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\AbstractIntegration;
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
		add_action( 'init', [ $this, 'init' ], 5 );
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function init() {
		if ( \get_option( 'pronamic_pay_version', null ) !== $this->plugin->get_version() ) {
			$this->install();
		}

		// Integrations.
		$integrations = $this->get_upgradeable_integrations();

		foreach ( $integrations as $integration ) {
			$version_option_name = $integration->get_version_option_name();

			if ( null === $version_option_name ) {
				continue;
			}

			$version_option = \strval( $integration->get_version_option() );

			$upgrades = $integration->get_upgrades();

			foreach ( $upgrades as $upgrade ) {
				$version = $upgrade->get_version();

				if ( ! version_compare( $version_option, $version, '<' ) ) {
					continue;
				}

				$upgrade->execute();

				update_option( $version_option_name, $version );
			}
		}
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
					[
						'page' => 'pronamic-pay-about',
						'tab'  => $tab,
					],
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
	 * @return void
	 */
	private function create_roles() {
		// Payer role.
		add_role(
			'payer',
			__( 'Payer', 'pronamic_ideal' ),
			[
				'read' => true,
			]
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

				if ( null === $integration->get_version_option_name() ) {
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
}
