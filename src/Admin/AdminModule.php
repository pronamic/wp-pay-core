<?php
/**
 * Admin Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\AddressHelper;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\ContactNameHelper;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CustomerHelper;
use Pronamic\WordPress\Pay\Forms\FormPostType;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionInterval;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;
use Pronamic\WordPress\Pay\Webhooks\WebhookManager;

/**
 * WordPress Pay admin
 *
 * @author  Remco Tolsma
 * @version 2.5.0
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
	 * @var AdminAboutPage|null
	 */
	public $about_page;

	/**
	 * Admin dashboard page.
	 *
	 * @var AdminDashboard
	 */
	public $dashboard;

	/**
	 * Admin site health.
	 *
	 * @var AdminHealth
	 */
	public $health;

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
	 * Construct and initialize an admin object.
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
		$this->dashboard = new AdminDashboard( $plugin );
		$this->health    = new AdminHealth( $plugin );
		$this->notices   = new AdminNotices( $plugin );
		$this->reports   = new AdminReports( $plugin, $this );
		$this->tour      = new AdminTour( $plugin );

		// About page.
		$about_page_file = $this->plugin->get_option( 'about_page_file' );

		if ( null !== $about_page_file ) {
			$this->about_page = new AdminAboutPage( $plugin, $about_page_file );
		}

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
			\wp_doing_ajax()
				||
			\wp_doing_cron()
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
	 * @throws \Exception When creating page fails.
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

			if ( $result instanceof \WP_Error ) {
				throw new \Exception( $result->get_error_message() );
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
			array(
				'post_title'   => __( 'Subscription Canceled', 'pronamic_ideal' ),
				'post_name'    => __( 'subscription', 'pronamic_ideal' ),
				'post_content' => sprintf(
					'<p>%s</p>',
					__( 'The subscription has been canceled.', 'pronamic_ideal' )
				),
				'post_meta'    => array(
					'_yoast_wpseo_meta-robots-noindex' => true,
				),
				'option_name'  => 'pronamic_pay_subscription_canceled_page_id',
			),
		);

		$url_args = array(
			'page'    => 'pronamic_pay_settings',
			'message' => 'pages-generated',
		);

		try {
			$this->create_pages( $pages );
		} catch ( \Exception $e ) {
			$url_args = array(
				'page'    => 'pronamic_pay_settings',
				'message' => 'pages-not-generated',
			);
		}

		$url = add_query_arg(
			$url_args,
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
		if ( ! \filter_has_var( \INPUT_POST, 'test_pay_gateway' ) ) {
			return;
		}

		if ( ! \check_admin_referer( 'test_pay_gateway', 'pronamic_pay_test_nonce' ) ) {
			return;
		}

		// Amount.
		$string = \filter_input( INPUT_POST, 'test_amount', \FILTER_SANITIZE_STRING );

		try {
			$amount = Number::from_string( $string );
		} catch ( \Exception $e ) {
			\wp_die( \esc_html( $e->getMessage() ) );
		}

		/*
		 * Payment.
		 */
		$payment = new Payment();

		$order_id = (string) \time();

		$payment->order_id = $order_id;

		$payment->set_config_id( \filter_input( \INPUT_POST, 'post_ID', \FILTER_SANITIZE_NUMBER_INT ) );

		$payment->set_payment_method( \filter_input( \INPUT_POST, 'pronamic_pay_test_payment_method', \FILTER_SANITIZE_STRING ) );

		// Description.
		$description = \sprintf(
			/* translators: %s: order ID */
			__( 'Test %s', 'pronamic_ideal' ),
			$order_id
		);

		$payment->set_description( $description );

		// Source.
		$payment->set_source( 'test' );
		$payment->set_source_id( $order_id );

		/*
		 * Credit Card.
		 * Test card to simulate a 3-D Secure registered card.
		 *
		 * @link http://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
		 */
		$credit_card = new CreditCard();

		$expiration_date = new \DateTime( '+5 years' );

		$credit_card->set_expiration_month( (int) $expiration_date->format( 'n' ) );
		$credit_card->set_expiration_year( (int) $expiration_date->format( 'Y' ) );
		$credit_card->set_name( 'Pronamic' );
		$credit_card->set_number( '5300000000000006' );
		$credit_card->set_security_code( '123' );

		$payment->set_credit_card( $credit_card );

		// Data.
		$user = \wp_get_current_user();

		$phone = \filter_input( \INPUT_POST, 'test_phone', \FILTER_SANITIZE_STRING );

		// Name.
		$name = ContactNameHelper::from_array(
			array(
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
			)
		);

		// Customer.
		$customer = CustomerHelper::from_array(
			array(
				'name'    => $name,
				'email'   => $user->user_email,
				'phone'   => $phone,
				'user_id' => $user->ID,
			)
		);

		$payment->set_customer( $customer );

		// Billing address.
		$address = AddressHelper::from_array(
			array(
				'name'  => $name,
				'email' => $user->user_email,
				'phone' => $phone,
			)
		);

		$payment->set_billing_address( $address );

		// Lines.
		$payment->lines = new PaymentLines();

		$line = $payment->lines->new_line();

		$price = new Money( $amount, 'EUR' );

		$line->set_name( __( 'Test', 'pronamic_ideal' ) );
		$line->set_unit_price( $price );
		$line->set_quantity( 1 );
		$line->set_total_amount( $price );

		$payment->set_total_amount( $payment->lines->get_amount() );

		// Subscription.
		$test_subscription = \filter_input( \INPUT_POST, 'pronamic_pay_test_subscription', \FILTER_VALIDATE_BOOLEAN );
		$interval          = \filter_input( \INPUT_POST, 'pronamic_pay_test_repeat_interval', \FILTER_VALIDATE_INT );
		$interval_period   = \filter_input( \INPUT_POST, 'pronamic_pay_test_repeat_frequency', \FILTER_SANITIZE_STRING );

		if ( ! empty( $test_subscription ) && ! empty( $interval ) && ! empty( $interval_period ) ) {
			$subscription = new Subscription();

			$subscription->set_description( $description );
			$subscription->set_lines( $payment->get_lines() );

			// Ends on.
			$ends_on = \filter_input( \INPUT_POST, 'pronamic_pay_ends_on', \FILTER_SANITIZE_STRING );

			$total_periods = null;

			switch ( $ends_on ) {
				case 'count':
					$count = \filter_input( \INPUT_POST, 'pronamic_pay_ends_on_count', \FILTER_VALIDATE_INT );

					if ( ! empty( $count ) ) {
						$total_periods = $count;
					}

					break;
				case 'date':
					$end_date = \filter_input( \INPUT_POST, 'pronamic_pay_ends_on_date', \FILTER_SANITIZE_STRING );

					if ( ! empty( $end_date ) ) {
						$interval_spec = 'P' . $interval . Util::to_period( $interval_period );

						$period = new \DatePeriod(
							new \DateTime(),
							new \DateInterval( $interval_spec ),
							new \DateTime( $end_date )
						);

						$total_periods = iterator_count( $period );
					}

					break;
			}

			// Phase.
			$phase = new SubscriptionPhase(
				$subscription,
				new DateTimeImmutable(),
				new SubscriptionInterval( 'P' . $interval . Util::to_period( $interval_period ) ),
				$price
			);

			$phase->set_total_periods( $total_periods );

			$subscription->add_phase( $phase );

			$period = $subscription->new_period();

			if ( null !== $period ) {
				$payment->add_period( $period );
			}
		}

		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			return;
		}

		// Start.
		try {
			$payment = Plugin::start_payment( $payment );

			$gateway->redirect( $payment );
		} catch ( \Exception $e ) {
			Plugin::render_exception( $e );

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
	 * Get menu icon URL.
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_menu_page/
	 * @return string
	 * @throws \Exception Throws exception when retrieving menu icon fails.
	 */
	private function get_menu_icon_url() {
		/**
		 * Icon URL.
		 *
		 * Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme.
		 * This should begin with 'data:image/svg+xml;base64,'.
		 *
		 * We use a SVG image with default fill color #A0A5AA from the default admin color scheme:
		 * https://github.com/WordPress/WordPress/blob/5.2/wp-includes/general-template.php#L4135-L4145
		 *
		 * The advantage of this is that users with the default admin color scheme do not see the repaint:
		 * https://github.com/WordPress/WordPress/blob/5.2/wp-admin/js/svg-painter.js
		 *
		 * @link https://developer.wordpress.org/reference/functions/add_menu_page/
		 */
		$file = __DIR__ . '/../../images/dist/wp-pay-wp-admin-fresh-base.svgo-min.svg';

		if ( ! \is_readable( $file ) ) {
			throw new \Exception(
				\sprintf(
					'Could not read WordPress admin menu icon from file: %s.',
					$file
				)
			);
		}

		$svg = \file_get_contents( $file, true );

		if ( false === $svg ) {
			throw new \Exception(
				\sprintf(
					'Could not read WordPress admin menu icon from file: %s.',
					$file
				)
			);
		}

		$icon_url = \sprintf(
			'data:image/svg+xml;base64,%s',
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			\base64_encode( $svg )
		);

		return $icon_url;
	}

	/**
	 * Create the admin menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		/**
		 * Badges.
		 *
		 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-menus.php#L145
		 */
		$counts = wp_count_posts( 'pronamic_payment' );

		$payments_pending_count = \property_exists( $counts, 'payment_pending' ) ? $counts->payment_pending : 0;

		$counts = wp_count_posts( 'pronamic_pay_subscr' );

		$subscriptions_on_hold_count = \property_exists( $counts, 'subscr_on_hold' ) ? $counts->subscr_on_hold : 0;

		$badges = array(
			'pay'           => array(
				'title' => array(),
				'count' => 0,
				'html'  => '',
			),
			'payments'      => array(
				'title' => \sprintf(
					/* translators: %d: payments pending count */
					\_n( '%d payment pending', '%d payments pending', $payments_pending_count, 'pronamic_ideal' ),
					$payments_pending_count
				),
				'count' => $payments_pending_count,
				'html'  => '',
			),
			'subscriptions' => array(
				'title' => \sprintf(
					/* translators: %d: subscriptions on hold count */
					\_n( '%d subscription on hold', '%d subscriptions on hold', $subscriptions_on_hold_count, 'pronamic_ideal' ),
					$subscriptions_on_hold_count
				),
				'count' => $subscriptions_on_hold_count,
				'html'  => '',
			),
		);

		foreach ( $badges as &$badge ) {
			$count = $badge['count'];

			if ( 0 === $count ) {
				continue;
			}

			$title = \array_key_exists( 'title', $badge ) && \is_string( $badge['title'] ) ? $badge['title'] : '';

			$badge['html'] = \sprintf(
				' <span class="awaiting-mod update-plugins count-%1$d" title="%2$s"><span class="processing-count">%1$d</span></span>',
				$count,
				$title
			);

			// Pay badge.
			$badges['pay']['count'] += $count;

			if ( ! empty( $title ) ) {
				$badges['pay']['title'][] = $title;
			}
		}

		/**
		 * Submenu pages.
		 */
		$submenu_pages = array(
			array(
				'page_title' => __( 'Payments', 'pronamic_ideal' ),
				'menu_title' => __( 'Payments', 'pronamic_ideal' ) . $badges['payments']['html'],
				'capability' => 'edit_payments',
				'menu_slug'  => 'edit.php?post_type=pronamic_payment',
			),
			array(
				'page_title' => __( 'Subscriptions', 'pronamic_ideal' ),
				'menu_title' => __( 'Subscriptions', 'pronamic_ideal' ) . $badges['subscriptions']['html'],
				'capability' => 'edit_payments',
				'menu_slug'  => 'edit.php?post_type=pronamic_pay_subscr',
			),
			array(
				'page_title' => __( 'Reports', 'pronamic_ideal' ),
				'menu_title' => __( 'Reports', 'pronamic_ideal' ),
				'capability' => 'edit_payments',
				'menu_slug'  => 'pronamic_pay_reports',
				'function'   => function() {
					$this->reports->page_reports();
				},
			),
			array(
				'page_title' => __( 'Payment Forms', 'pronamic_ideal' ),
				'menu_title' => __( 'Forms', 'pronamic_ideal' ),
				'capability' => 'edit_forms',
				'menu_slug'  => 'edit.php?post_type=pronamic_pay_form',
			),
			array(
				'page_title' => __( 'Configurations', 'pronamic_ideal' ),
				'menu_title' => __( 'Configurations', 'pronamic_ideal' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'edit.php?post_type=pronamic_gateway',
			),
			array(
				'page_title' => __( 'Settings', 'pronamic_ideal' ),
				'menu_title' => __( 'Settings', 'pronamic_ideal' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'pronamic_pay_settings',
				'function'   => function() {
					$this->render_page( 'settings' );
				},
			),
		);

		if ( version_compare( get_bloginfo( 'version' ), '5.2', '<' ) ) {
			$submenu_pages[] = array(
				'page_title' => __( 'Tools', 'pronamic_ideal' ),
				'menu_title' => __( 'Tools', 'pronamic_ideal' ),
				'capability' => 'manage_options',
				'menu_slug'  => 'pronamic_pay_tools',
				'function'   => function() {
					$this->render_page( 'tools' );
				},
			);
		}

		$minimum_capability = $this->get_minimum_capability( $submenu_pages );

		try {
			$menu_icon_url = $this->get_menu_icon_url();
		} catch ( \Exception $e ) {
			// @todo Log.

			/**
			 * If retrieving the menu icon URL fails we will
			 * fallback to the WordPress money dashicon.
			 *
			 * @link https://developer.wordpress.org/resource/dashicons/#money
			 */
			$menu_icon_url = 'dashicons-money';
		}

		$pay_badge = '';

		if ( 0 !== $badges['pay']['count'] ) {
			$pay_badge = \sprintf(
				' <span class="awaiting-mod update-plugins count-%1$d" title="%2$s"><span class="processing-count">%1$d</span></span>',
				$badges['pay']['count'],
				\implode( ', ', $badges['pay']['title'] )
			);
		}

		add_menu_page(
			__( 'Pronamic Pay', 'pronamic_ideal' ),
			__( 'Pay', 'pronamic_ideal' ) . $pay_badge,
			$minimum_capability,
			'pronamic_ideal',
			function() {
				$this->render_page( 'dashboard' );
			},
			$menu_icon_url
		);

		// Add submenu pages.
		foreach ( $submenu_pages as $page ) {
			/**
			 * To keep PHPStan happy we use an if/else statement for
			 * the 6th $function parameter which should be a callable
			 * function. Unfortunately this is not documented
			 * correctly in WordPress.
			 *
			 * @link https://github.com/WordPress/WordPress/blob/5.2/wp-admin/includes/plugin.php#L1296-L1377
			 */
			if ( array_key_exists( 'function', $page ) ) {
				add_submenu_page(
					'pronamic_ideal',
					$page['page_title'],
					$page['menu_title'],
					$page['capability'],
					$page['menu_slug'],
					$page['function']
				);
			} else {
				add_submenu_page(
					'pronamic_ideal',
					$page['page_title'],
					$page['menu_title'],
					$page['capability'],
					$page['menu_slug']
				);
			}
		}

		// Change title of plugin submenu page to 'Dashboard'.
		global $submenu;

		if ( isset( $submenu['pronamic_ideal'] ) ) {
			/* phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited */
			$submenu['pronamic_ideal'][0][0] = __( 'Dashboard', 'pronamic_ideal' );
		}
	}

	/**
	 * Get minimum capability from submenu pages.
	 *
	 * @param array $pages Submenu pages.
	 *
	 * @return string
	 */
	public function get_minimum_capability( array $pages ) {
		foreach ( $pages as $page ) {
			if ( \current_user_can( $page['capability'] ) ) {
				return $page['capability'];
			}
		}

		return 'edit_payments';
	}

	/**
	 * Render the specified page.
	 *
	 * @param string $name Page identifier.
	 * @return void
	 */
	public function render_page( $name ) {
		include __DIR__ . '/../../views/page-' . $name . '.php';
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
