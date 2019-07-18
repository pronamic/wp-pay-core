<?php
/**
 * Admin Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Exception;
use Pronamic\WordPress\Money\Parser as MoneyParser;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Forms\FormPostType;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Webhooks\WebhookManager;
use WP_Error;

/**
 * WordPress Pay admin
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class AdminModule {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Admin settings page.
	 *
	 * @var AdminSettings
	 */
	public $settings;

	/**
	 * Admin about page.
	 *
	 * @var AdminAboutPage
	 */
	public $about;

	/**
	 * Admin dashboard page.
	 *
	 * @var AdminDashboard
	 */
	public $dashboard;

	/**
	 * Admin notices page.
	 *
	 * @var AdminNotices
	 */
	public $notices;

	/**
	 * Admin reports page.
	 *
	 * @var AdminReports
	 */
	public $reports;

	/**
	 * Admin tour page.
	 *
	 * @var AdminTour
	 */
	public $tour;

	/**
	 * Plugin installation.
	 *
	 * @var Install
	 */
	public $install;

	/**
	 * Webhook manager.
	 *
	 * @var WebhookManager
	 */
	private $webhook_manager;

	/**
	 * Constructs and initalize an admin object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->install = new Install( $plugin, $this );

		// Actions.
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'load-post.php', array( $this, 'maybe_test_payment' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'parent_file', array( $this, 'admin_menu_parent_file' ) );

		// Modules.
		$this->settings  = new AdminSettings( $plugin );
		$this->about     = new AdminAboutPage( $plugin, $this );
		$this->dashboard = new AdminDashboard( $plugin );
		$this->notices   = new AdminNotices( $plugin );
		$this->reports   = new AdminReports( $plugin, $this );
		$this->tour      = new AdminTour( $plugin );

		// Webhook Manager.
		$this->webhook_manager = new WebhookManager( $plugin, $this );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		global $pronamic_ideal_errors;

		$pronamic_ideal_errors = array();

		// Maybe.
		$this->maybe_create_pages();
		$this->maybe_redirect();

		// Post types.
		new AdminGatewayPostType( $this->plugin, $this );
		new AdminPaymentPostType( $this->plugin );
		new AdminSubscriptionPostType( $this->plugin );

		// License check.
		if ( ! wp_next_scheduled( 'pronamic_pay_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'pronamic_pay_license_check' );
		}
	}

	/**
	 * Maybe redirect.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.4/includes/admin/class-wc-admin.php#L29
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.4/includes/admin/class-wc-admin.php#L96-L122
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		$redirect = get_transient( 'pronamic_pay_admin_redirect' );

		// Check.
		if (
			empty( $redirect )
				||
			wp_doing_ajax()
				||
			Util::doing_cron()
				||
			is_network_admin()
				||
			filter_has_var( INPUT_GET, 'activate-multi' )
				||
			! current_user_can( 'manage_options' )
		) {
			return;
		}

		/**
		 * Delete the `pronamic_pay_admin_redirect` transient.
		 *
		 * If we don't get the `true` confirmation we will bail out
		 * so users will not get stuck in a redirect loop.
		 *
		 * We have had issues with this with caching plugins like
		 * W3 Total Cache and on Savvii hosting environments.
		 *
		 * @link https://developer.wordpress.org/reference/functions/delete_transient/
		 */
		$result = delete_transient( 'pronamic_pay_admin_redirect' );

		if ( true !== $result ) {
			return;
		}

		/**
		 * Redirect.
		 */
		wp_safe_redirect( $redirect );

		exit;
	}

	/**
	 * Input checkbox.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public static function input_checkbox( $args ) {
		$defaults = array(
			'label_for' => '',
			'type'      => 'text',
			'label'     => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$id    = $args['label_for'];
		$value = get_option( $id );

		$legend = sprintf(
			'<legend class="screen-reader-text"><span>%s</span></legend>',
			esc_html( $args['label'] )
		);

		$input = sprintf(
			'<input name="%s" id="%s" type="%s" value="%s" %s />',
			esc_attr( $id ),
			esc_attr( $id ),
			esc_attr( 'checkbox' ),
			esc_attr( '1' ),
			checked( $value, true, false )
		);

		$label = sprintf(
			'<label for="%s">%s %s</label>',
			esc_attr( $id ),
			$input,
			esc_html( $args['label'] )
		);

		printf(
			/* phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped */
			'<fieldset>%s %s</fieldset>',
			$legend,
			$label
			/* phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped */
		);
	}

	/**
	 * Sanitize the specified value to a boolean.
	 *
	 * @param mixed $value Value.
	 * @return boolean
	 */
	public static function sanitize_boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Configurations dropdown.
	 *
	 * @param array $args Arguments.
	 * @return string|null
	 */
	public static function dropdown_configs( $args ) {
		$defaults = array(
			'name'           => 'pronamic_pay_config_id',
			'echo'           => true,
			'selected'       => false,
			'payment_method' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		// Output.
		$output = '';

		// Dropdown.
		$id       = $args['name'];
		$name     = $args['name'];
		$selected = $args['selected'];

		if ( false === $selected ) {
			$selected = get_option( $id );
		}

		$output .= sprintf(
			'<select id="%s" name="%s">',
			esc_attr( $id ),
			esc_attr( $name )
		);

		$options = Plugin::get_config_select_options( $args['payment_method'] );

		foreach ( $options as $value => $name ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $value, $selected, false ),
				esc_html( $name )
			);
		}

		$output .= sprintf( '</select>' );

		// Output.
		if ( $args['echo'] ) {
			echo wp_kses(
				$output,
				array(
					'select' => array(
						'id'   => array(),
						'name' => array(),
					),
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);

			return null;
		}

		return $output;
	}

	/**
	 * Create pages.
	 *
	 * @param array    $pages   Page.
	 * @param int|null $parent Parent post ID.
	 * @return void
	 * @throws Exception When creating page fails.
	 */
	private function create_pages( $pages, $parent = null ) {
		foreach ( $pages as $page ) {
			$post = array(
				'post_title'     => $page['post_title'],
				'post_name'      => $page['post_title'],
				'post_content'   => $page['post_content'],
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			);

			if ( isset( $parent ) ) {
				$post['post_parent'] = $parent;
			}

			$result = wp_insert_post( $post, true );

			if ( $result instanceof WP_Error ) {
				throw new Exception( $result->get_error_message() );
			}

			if ( isset( $page['post_meta'] ) ) {
				foreach ( $page['post_meta'] as $key => $value ) {
					update_post_meta( $result, $key, $value );
				}
			}

			if ( isset( $page['option_name'] ) ) {
				update_option( $page['option_name'], $result );
			}

			if ( isset( $page['children'] ) ) {
				$this->create_pages( $page['children'], $result );
			}
		}
	}

	/**
	 * Maybe create pages.
	 *
	 * @return void
	 */
	public function maybe_create_pages() {
		if ( ! filter_has_var( INPUT_POST, 'pronamic_pay_create_pages' ) ) {
			return;
		}

		if ( ! check_admin_referer( 'pronamic_pay_settings', 'pronamic_pay_nonce' ) ) {
			return;
		}

		$pages = array(
			array(
				'post_title'   => __( 'Payment Status', 'pronamic_ideal' ),
				'post_name'    => __( 'payment', 'pronamic_ideal' ),
				'post_content' => '',
				'post_meta'    => array(
					'_yoast_wpseo_meta-robots-noindex' => true,
				),
				'children'     => array(
					'completed' => array(
						'post_title'   => __( 'Payment completed', 'pronamic_ideal' ),
						'post_name'    => __( 'completed', 'pronamic_ideal' ),
						'post_content' => sprintf(
							'<p>%s</p>',
							__( 'The payment has been successfully completed.', 'pronamic_ideal' )
						),
						'post_meta'    => array(
							'_yoast_wpseo_meta-robots-noindex' => true,
						),
						'option_name'  => 'pronamic_pay_completed_page_id',
					),
					'cancel'    => array(
						'post_title'   => __( 'Payment cancelled', 'pronamic_ideal' ),
						'post_name'    => __( 'cancelled', 'pronamic_ideal' ),
						'post_content' => sprintf(
							'<p>%s</p>',
							__( 'You have cancelled the payment.', 'pronamic_ideal' )
						),
						'post_meta'    => array(
							'_yoast_wpseo_meta-robots-noindex' => true,
						),
						'option_name'  => 'pronamic_pay_cancel_page_id',
					),
					'expired'   => array(
						'post_title'   => __( 'Payment expired', 'pronamic_ideal' ),
						'post_name'    => __( 'expired', 'pronamic_ideal' ),
						'post_content' => sprintf(
							'<p>%s</p>',
							__( 'Your payment session has expired.', 'pronamic_ideal' )
						),
						'post_meta'    => array(
							'_yoast_wpseo_meta-robots-noindex' => true,
						),
						'option_name'  => 'pronamic_pay_expired_page_id',
					),
					'error'     => array(
						'post_title'   => __( 'Payment error', 'pronamic_ideal' ),
						'post_name'    => __( 'error', 'pronamic_ideal' ),
						'post_content' => sprintf(
							'<p>%s</p>',
							__( 'An error has occurred during payment.', 'pronamic_ideal' )
						),
						'post_meta'    => array(
							'_yoast_wpseo_meta-robots-noindex' => true,
						),
						'option_name'  => 'pronamic_pay_error_page_id',
					),
					'unknown'   => array(
						'post_title'   => __( 'Payment status unknown', 'pronamic_ideal' ),
						'post_name'    => __( 'unknown', 'pronamic_ideal' ),
						'post_content' => sprintf(
							'<p>%s</p>',
							__( 'The payment status is unknown.', 'pronamic_ideal' )
						),
						'post_meta'    => array(
							'_yoast_wpseo_meta-robots-noindex' => true,
						),
						'option_name'  => 'pronamic_pay_unknown_page_id',
					),
				),
			),
		);

		$this->create_pages( $pages );

		$url = add_query_arg(
			array(
				'page'    => 'pronamic_pay_settings',
				'message' => 'pages-generated',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );

		exit;
	}

	/**
	 * Check if scripts should be enqueued based on the hook and current screen.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_current_screen/
	 * @link https://developer.wordpress.org/reference/classes/wp_screen/
	 *
	 * @param string $hook Hook.
	 * @return bool True if scripts should be enqueued, false otherwise.
	 */
	private function should_enqueue_scripts( $hook ) {
		// Check if the hook contains the value 'pronamic_pay'.
		if ( false !== strpos( $hook, 'pronamic_pay' ) ) {
			return true;
		}

		// Check if the hook contains the value 'pronamic_ideal'.
		if ( false !== strpos( $hook, 'pronamic_ideal' ) ) {
			return true;
		}

		// Check current screen for some values related to Pronamic Pay.
		$screen = get_current_screen();

		if ( null === $screen ) {
			return false;
		}

		// Current screen is dashboard.
		if ( 'dashboard' === $screen->id ) {
			return true;
		}

		// Gravity Forms.
		if ( 'toplevel_page_gf_edit_forms' === $screen->id ) {
			return true;
		}

		// CHeck if current screen post type is related to Pronamic Pay.
		if ( in_array(
			$screen->post_type,
			array(
				'pronamic_gateway',
				'pronamic_payment',
				'pronamic_pay_form',
				'pronamic_pay_gf',
				'pronamic_pay_subscr',
			),
			true
		) ) {
			return true;
		}

		// Other.
		return false;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! $this->should_enqueue_scripts( $hook ) ) {
			return;
		}

		$min = SCRIPT_DEBUG ? '' : '.min';

		// Tippy.js - https://atomiks.github.io/tippyjs/.
		wp_register_script(
			'tippy.js',
			plugins_url( '../../assets/tippy.js/tippy.all' . $min . '.js', __FILE__ ),
			array(),
			'3.4.1',
			true
		);

		// Pronamic.
		wp_register_style(
			'pronamic-pay-icons',
			plugins_url( '../../fonts/dist/pronamic-pay-icons.css', __FILE__ ),
			array(),
			$this->plugin->get_version()
		);

		wp_register_style(
			'pronamic-pay-admin',
			plugins_url( '../../css/admin' . $min . '.css', __FILE__ ),
			array( 'pronamic-pay-icons' ),
			$this->plugin->get_version()
		);

		wp_register_script(
			'pronamic-pay-admin',
			plugins_url( '../../js/dist/admin' . $min . '.js', __FILE__ ),
			array( 'jquery', 'tippy.js' ),
			$this->plugin->get_version(),
			true
		);

		// Enqueue.
		wp_enqueue_style( 'pronamic-pay-admin' );
		wp_enqueue_script( 'pronamic-pay-admin' );
	}

	/**
	 * Maybe test payment.
	 *
	 * @return void
	 */
	public function maybe_test_payment() {
		if ( ! filter_has_var( INPUT_POST, 'test_pay_gateway' ) ) {
			return;
		}

		if ( ! check_admin_referer( 'test_pay_gateway', 'pronamic_pay_test_nonce' ) ) {
			return;
		}

		// Gateway.
		$id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );

		$gateway = Plugin::get_gateway( $id );

		if ( empty( $gateway ) ) {
			return;
		}

		// Amount.
		$string = filter_input( INPUT_POST, 'test_amount', FILTER_SANITIZE_STRING );

		$money_parser = new MoneyParser();

		$amount = $money_parser->parse( $string )->get_value();

		// Start.
		$errors = array();

		try {
			$data = new \Pronamic\WordPress\Pay\Payments\PaymentTestData( wp_get_current_user(), $amount );

			$payment_method = filter_input( INPUT_POST, 'pronamic_pay_test_payment_method', FILTER_SANITIZE_STRING );

			$payment = Plugin::start( $id, $gateway, $data, $payment_method );

			$errors[] = $gateway->get_error();

			if ( ! $gateway->has_error() ) {
				$gateway->redirect( $payment );
			}
		} catch ( Exception $e ) {
			$errors[] = new WP_Error( 'pay_error', $e->getMessage() );
		}

		$errors = array_filter( $errors );

		if ( ! empty( $errors ) ) {
			Plugin::render_errors( $errors );

			exit;
		}
	}

	/**
	 * Admin menu parent file.
	 *
	 * @param string $parent_file Parent file for admin menu.
	 * @return string
	 */
	public function admin_menu_parent_file( $parent_file ) {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return $parent_file;
		}

		switch ( $screen->id ) {
			case FormPostType::POST_TYPE:
			case AdminGatewayPostType::POST_TYPE:
			case AdminPaymentPostType::POST_TYPE:
			case AdminSubscriptionPostType::POST_TYPE:
				return 'pronamic_ideal';
		}

		return $parent_file;
	}

	/**
	 * Create the admin menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		// @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-menus.php#L145
		$counts = wp_count_posts( 'pronamic_payment' );

		$badge = '';

		if ( isset( $counts->payment_pending ) && $counts->payment_pending > 0 ) {
			$badge = sprintf(
				' <span class="awaiting-mod update-plugins count-%s"><span class="processing-count">%s</span></span>',
				$counts->payment_pending,
				$counts->payment_pending
			);
		}

		add_menu_page(
			__( 'Pronamic Pay', 'pronamic_ideal' ),
			__( 'Pay', 'pronamic_ideal' ) . $badge,
			'edit_payments',
			'pronamic_ideal',
			array( $this, 'page_dashboard' ),
			'dashicons-money'
		);

		add_submenu_page(
			'pronamic_ideal',
			__( 'Payments', 'pronamic_ideal' ),
			__( 'Payments', 'pronamic_ideal' ) . $badge,
			'edit_payments',
			'edit.php?post_type=pronamic_payment'
		);

		add_submenu_page(
			'pronamic_ideal',
			__( 'Subscriptions', 'pronamic_ideal' ),
			__( 'Subscriptions', 'pronamic_ideal' ),
			'edit_payments',
			'edit.php?post_type=pronamic_pay_subscr'
		);

		do_action( 'pronamic_pay_admin_menu' );

		add_submenu_page(
			'pronamic_ideal',
			__( 'Payment Forms', 'pronamic_ideal' ),
			__( 'Forms', 'pronamic_ideal' ),
			'edit_forms',
			'edit.php?post_type=pronamic_pay_form'
		);

		add_submenu_page(
			'pronamic_ideal',
			__( 'Configurations', 'pronamic_ideal' ),
			__( 'Configurations', 'pronamic_ideal' ),
			'manage_options',
			'edit.php?post_type=pronamic_gateway'
		);

		add_submenu_page(
			'pronamic_ideal',
			__( 'Settings', 'pronamic_ideal' ),
			__( 'Settings', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_settings',
			array( $this, 'page_settings' )
		);

		add_submenu_page(
			'pronamic_ideal',
			__( 'Tools', 'pronamic_ideal' ),
			__( 'Tools', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_tools',
			array( $this, 'page_tools' )
		);

		global $submenu;

		if ( isset( $submenu['pronamic_ideal'] ) ) {
			/* phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited */
			$submenu['pronamic_ideal'][0][0] = __( 'Dashboard', 'pronamic_ideal' );
		}
	}

	/**
	 * Page dashboard.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-admin/admin.php#L236-L253
	 *
	 * @return void
	 */
	public function page_dashboard() {
		$this->render_page( 'dashboard' );
	}

	/**
	 * Page settings.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-admin/admin.php#L236-L253
	 *
	 * @return void
	 */
	public function page_settings() {
		$this->render_page( 'settings' );
	}

	/**
	 * Page tools.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-admin/admin.php#L236-L253
	 *
	 * @return void
	 */
	public function page_tools() {
		$this->render_page( 'tools' );
	}

	/**
	 * Render the specified page.
	 *
	 * @param string $name Page identifier.
	 * @return boolean True if a page is rendered, false otherwise.
	 */
	public function render_page( $name ) {
		$result = false;

		$file = __DIR__ . '/../../views/page-' . $name . '.php';

		if ( is_readable( $file ) ) {
			include $file;

			$result = true;
		}

		return $result;
	}

	/**
	 * Get a CSS class for the specified post status.
	 *
	 * @param string $post_status Post status.
	 * @return string
	 */
	public static function get_post_status_icon_class( $post_status ) {
		switch ( $post_status ) {
			case 'payment_pending':
			case 'subscr_pending':
				return 'pronamic-pay-icon-pending';

			case 'payment_cancelled':
			case 'subscr_cancelled':
				return 'pronamic-pay-icon-cancelled';

			case 'payment_completed':
			case 'subscr_completed':
				return 'pronamic-pay-icon-completed';

			case 'payment_refunded':
				return 'pronamic-pay-icon-refunded';

			case 'payment_failed':
			case 'subscr_failed':
				return 'pronamic-pay-icon-failed';

			case 'payment_on_hold':
			case 'payment_expired':
			case 'subscr_expired':
			case 'subscr_on_hold':
				return 'pronamic-pay-icon-on-hold';

			case 'payment_reserved':
			case 'subscr_active':
			default:
				return 'pronamic-pay-icon-processing';
		}
	}
}
