<?php
/**
 * Admin Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\ContactNameHelper;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\CreditCard;
use Pronamic\WordPress\Pay\CustomerHelper;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionInterval;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;

/**
 * WordPress Pay admin
 *
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
	 * Admin tour page.
	 *
	 * @var AdminTour
	 */
	public $tour;

	/**
	 * Construct and initialize an admin object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_init', $this->admin_init( ... ) );
		add_action( 'admin_menu', $this->admin_menu( ... ) );

		add_action( 'load-post.php', $this->maybe_test_payment( ... ) );

		add_action( 'admin_enqueue_scripts', $this->enqueue_scripts( ... ) );

		add_filter( 'parent_file', $this->admin_menu_parent_file( ... ) );

		// Modules.
		$this->settings  = new AdminSettings( $plugin );
		$this->dashboard = new AdminDashboard();
		$this->health    = new AdminHealth( $plugin );
		$this->tour      = new AdminTour( $plugin );

		// About page.
		$about_page_file = $this->plugin->get_option( 'about_page_file' );

		if ( null !== $about_page_file ) {
			$this->about_page = new AdminAboutPage( $plugin, $about_page_file );
		}
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Maybe.
		$this->maybe_redirect();

		// Post types.
		new AdminGatewayPostType( $this->plugin );

		$admin_payment_post_type = new AdminPaymentPostType( $this->plugin );

		$admin_payment_post_type->admin_init();

		$admin_subscription_post_type = new AdminSubscriptionPostType( $this->plugin );

		$admin_subscription_post_type->admin_init();
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
		$defaults = [
			'name'           => 'pronamic_pay_config_id',
			'echo'           => true,
			'selected'       => false,
			'payment_method' => null,
		];

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
				[
					'select' => [
						'id'   => [],
						'name' => [],
					],
					'option' => [
						'value'    => [],
						'selected' => [],
					],
				]
			);

			return null;
		}

		return $output;
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
		if ( str_contains( $hook, 'pronamic_pay' ) ) {
			return true;
		}

		// Check if the hook contains the value 'pronamic_ideal'.
		if ( str_contains( $hook, 'pronamic_ideal' ) ) {
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
			[
				'pronamic_gateway',
				'pronamic_payment',
				'pronamic_pay_form',
				'pronamic_pay_gf',
				'pronamic_pay_subscr',
			],
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
			[],
			'3.4.1',
			true
		);

		// Pronamic.
		wp_register_style(
			'pronamic-pay-icons',
			plugins_url( '../../fonts/dist/pronamic-pay-icons.css', __FILE__ ),
			[],
			$this->plugin->get_version()
		);

		wp_register_style(
			'pronamic-pay-admin',
			plugins_url( '../../css/admin' . $min . '.css', __FILE__ ),
			[ 'pronamic-pay-icons' ],
			$this->plugin->get_version()
		);

		wp_register_script(
			'pronamic-pay-admin',
			plugins_url( '../../js/dist/admin' . $min . '.js', __FILE__ ),
			[ 'jquery', 'tippy.js' ],
			$this->plugin->get_version(),
			true
		);

		/**
		 * Clipboard feature.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/68e3310c024d7fceb84a5028e955ad163de6bd45/wp-includes/js/plupload/handlers.js#L364-L393
		 * @link https://translate.wordpress.org/projects/wp/dev/nl/default/?filters%5Bstatus%5D=either&filters%5Boriginal_id%5D=10763746&filters%5Btranslation_id%5D=91929960
		 * @link https://translate.wordpress.org/projects/wp/dev/nl/default/?filters%5Bstatus%5D=either&filters%5Boriginal_id%5D=6831324&filters%5Btranslation_id%5D=58732256
		 */
		\wp_register_script(
			'pronamic-pay-admin-clipboard',
			\plugins_url( '../../js/dist/admin-cb' . $min . '.js', __FILE__ ),
			[ 'clipboard', 'jquery' ],
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
		$currency_code = 'EUR';

		if ( \array_key_exists( 'test_currency_code', $_POST ) ) {
			$currency_code = \sanitize_text_field( \wp_unslash( $_POST['test_currency_code'] ) );
		}

		/*
		 * Payment.
		 */
		$payment = new Payment();

		$order_id = (string) \time();

		$payment->order_id = $order_id;

		$config_id = \filter_input( \INPUT_POST, 'post_ID', \FILTER_SANITIZE_NUMBER_INT );

		if ( false !== $config_id ) {
			$payment->set_config_id( (int) $config_id );
		}

		if ( \array_key_exists( 'pronamic_pay_test_payment_method', $_POST ) ) {
			$payment_method = \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_test_payment_method'] ) );

			$payment->set_payment_method( $payment_method );
		}

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

		// Customer.
		$customer_data = \array_map( sanitize_text_field( ... ), \wp_unslash( $_POST['customer'] ?? [] ) );

		$name = ContactNameHelper::from_array(
			[
				'first_name' => $this->get_optional_value( $customer_data, 'first_name' ),
				'last_name'  => $this->get_optional_value( $customer_data, 'last_name' ),
			]
		);

		$customer = CustomerHelper::from_array(
			[
				'name'    => $name,
				'email'   => $this->get_optional_value( $customer_data, 'email' ),
				'phone'   => $this->get_optional_value( $customer_data, 'phone' ),
				'user_id' => $user->ID,
			]
		);

		$payment->set_customer( $customer );

		// Lines.
		$lines_data = \array_map(
			function ( $item ) {
				if ( ! \is_array( $item ) ) {
					return [];
				}

				return \array_map( sanitize_text_field( ... ), $item );
			},
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized, see code above.
			\wp_unslash( $_POST['lines'] ?? [] )
		);

		$payment->lines = new PaymentLines();

		foreach ( $lines_data as $item ) {
			$line = $payment->lines->new_line();

			try {
				$value = $this->get_optional_value( $item, 'price' );

				$amount = Number::from_mixed( $value );

				$quantity = Number::from_mixed( $this->get_optional_value( $item, 'quantity' ) ?? 1 );
			} catch ( \Exception $e ) {
				\wp_die( \esc_html( $e->getMessage() ) );
			}

			$unit_price   = new Money( $amount, $currency_code );
			$total_amount = $unit_price->multiply( $quantity );

			$line->set_name( $this->get_optional_value( $item, 'name' ) );
			$line->set_unit_price( $unit_price );
			$line->set_quantity( $quantity );
			$line->set_total_amount( $total_amount );
		}

		$payment->set_total_amount( $payment->lines->get_amount() );

		// Billing address.
		$billing_data = \array_map( sanitize_text_field( ... ), \wp_unslash( $_POST['billing'] ?? [] ) );

		$name = new ContactName();
		$name->set_first_name( $this->get_optional_value( $billing_data, 'first_name' ) );
		$name->set_last_name( $this->get_optional_value( $billing_data, 'last_name' ) );

		$billing_address = new Address();
		$billing_address->set_name( $name );
		$billing_address->set_company_name( $this->get_optional_value( $billing_data, 'company' ) );
		$billing_address->set_line_1( $this->get_optional_value( $billing_data, 'line_1' ) );
		$billing_address->set_line_2( $this->get_optional_value( $billing_data, 'line_2' ) );
		$billing_address->set_city( $this->get_optional_value( $billing_data, 'city' ) );
		$billing_address->set_postal_code( $this->get_optional_value( $billing_data, 'postal_code' ) );
		$billing_address->set_country_code( $this->get_optional_value( $billing_data, 'country_code' ) );
		$billing_address->set_region( $this->get_optional_value( $billing_data, 'state' ) );
		$billing_address->set_email( $this->get_optional_value( $billing_data, 'email' ) );
		$billing_address->set_phone( $this->get_optional_value( $billing_data, 'phone' ) );

		$payment->set_billing_address( $billing_address );

		// Shipping address.
		$shipping_data = \array_map( sanitize_text_field( ... ), \wp_unslash( $_POST['shipping'] ?? [] ) );

		$name = new ContactName();
		$name->set_first_name( $this->get_optional_value( $shipping_data, 'first_name' ) );
		$name->set_last_name( $this->get_optional_value( $shipping_data, 'last_name' ) );

		$shipping_address = new Address();
		$shipping_address->set_name( $name );
		$shipping_address->set_company_name( $this->get_optional_value( $shipping_data, 'company' ) );
		$shipping_address->set_line_1( $this->get_optional_value( $shipping_data, 'line_1' ) );
		$shipping_address->set_line_2( $this->get_optional_value( $shipping_data, 'line_2' ) );
		$shipping_address->set_city( $this->get_optional_value( $shipping_data, 'city' ) );
		$shipping_address->set_postal_code( $this->get_optional_value( $shipping_data, 'postal_code' ) );
		$shipping_address->set_country_code( $this->get_optional_value( $shipping_data, 'country_code' ) );
		$shipping_address->set_region( $this->get_optional_value( $shipping_data, 'state' ) );
		$shipping_address->set_email( $this->get_optional_value( $shipping_data, 'email' ) );
		$shipping_address->set_phone( $this->get_optional_value( $shipping_data, 'phone' ) );

		$payment->set_shipping_address( $shipping_address );

		// Subscription.
		$test_subscription = \filter_input( \INPUT_POST, 'pronamic_pay_test_subscription', \FILTER_VALIDATE_BOOLEAN );
		$interval          = \filter_input( \INPUT_POST, 'pronamic_pay_test_repeat_interval', \FILTER_VALIDATE_INT );
		$interval_period   = \array_key_exists( 'pronamic_pay_test_repeat_frequency', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_test_repeat_frequency'] ) ) : '';

		if ( ! empty( $test_subscription ) && ! empty( $interval ) && ! empty( $interval_period ) ) {
			$subscription = new Subscription();

			$subscription->set_description( $description );
			$subscription->set_lines( $payment->get_lines() );

			// Phase.
			$phase = new SubscriptionPhase(
				$subscription,
				new DateTimeImmutable(),
				new SubscriptionInterval( 'P' . $interval . Util::to_period( $interval_period ) ),
				$payment->get_total_amount()
			);

			// Ends on.
			$total_periods = null;

			if ( \array_key_exists( 'pronamic_pay_ends_on', $_POST ) ) {
				$total_periods = null;

				switch ( $_POST['pronamic_pay_ends_on'] ) {
					case 'count':
						$total_periods = (int) \filter_input( \INPUT_POST, 'pronamic_pay_ends_on_count', \FILTER_VALIDATE_INT );

						break;
					case 'date':
						$end_date = \array_key_exists( 'pronamic_pay_ends_on_date', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['pronamic_pay_ends_on_date'] ) ) : '';

						if ( ! empty( $end_date ) ) {
							$period = new \DatePeriod(
								$phase->get_start_date(),
								$phase->get_interval(),
								new \DateTime( $end_date )
							);

							$total_periods = iterator_count( $period );
						}

						break;
				}

				if ( null !== $total_periods ) {
					$end_date = $phase->get_start_date()->add( $phase->get_interval()->multiply( $total_periods ) );

					$phase->set_end_date( $end_date );
				}
			}

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
	 * Get an optional value from the data array.
	 *
	 * @param array<string, string> $data Data array.
	 * @param string                $key  Key to retrieve the value for.
	 * @return string|null
	 */
	private function get_optional_value( array $data, string $key ) {
		if ( ! array_key_exists( $key, $data ) ) {
			return null;
		}

		$value = $data[ $key ];

		if ( '' === $value ) {
			return null;
		}

		return $value;
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
		return match ( $screen->id ) {
			AdminGatewayPostType::POST_TYPE, AdminPaymentPostType::POST_TYPE, AdminSubscriptionPostType::POST_TYPE => 'pronamic_ideal',
			default => $parent_file,
		};
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
					\esc_html( $file )
				)
			);
		}

		$svg = \file_get_contents( $file, true );

		if ( false === $svg ) {
			throw new \Exception(
				\sprintf(
					'Could not read WordPress admin menu icon from file: %s.',
					\esc_html( $file )
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

		$badges = [
			'pay'           => [
				'title' => [],
				'count' => 0,
				'html'  => '',
			],
			'payments'      => [
				'title' => \sprintf(
					/* translators: %d: payments pending count */
					\_n( '%d payment pending', '%d payments pending', $payments_pending_count, 'pronamic_ideal' ),
					$payments_pending_count
				),
				'count' => $payments_pending_count,
				'html'  => '',
			],
			'subscriptions' => [
				'title' => \sprintf(
					/* translators: %d: subscriptions on hold count */
					\_n( '%d subscription on hold', '%d subscriptions on hold', $subscriptions_on_hold_count, 'pronamic_ideal' ),
					$subscriptions_on_hold_count
				),
				'count' => $subscriptions_on_hold_count,
				'html'  => '',
			],
		];

		foreach ( $badges as &$badge ) {
			$count = $badge['count'];

			if ( 0 === $count ) {
				continue;
			}

			$title = \is_string( $badge['title'] ) ? $badge['title'] : '';

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

		$modules = \apply_filters( 'pronamic_pay_modules', [] );

		/**
		 * Submenu pages.
		 */
		$submenu_pages = [];

		$submenu_pages[] = [
			'page_title' => __( 'Payments', 'pronamic_ideal' ),
			'menu_title' => __( 'Payments', 'pronamic_ideal' ) . $badges['payments']['html'],
			'capability' => 'edit_payments',
			'menu_slug'  => 'edit.php?post_type=pronamic_payment',
		];

		if ( \in_array( 'subscriptions', $modules, true ) ) {
			$submenu_pages[] = [
				'page_title' => __( 'Subscriptions', 'pronamic_ideal' ),
				'menu_title' => __( 'Subscriptions', 'pronamic_ideal' ) . $badges['subscriptions']['html'],
				'capability' => 'edit_payments',
				'menu_slug'  => 'edit.php?post_type=pronamic_pay_subscr',
			];
		}

		$submenu_pages[] = [
			'page_title' => __( 'Configurations', 'pronamic_ideal' ),
			'menu_title' => __( 'Configurations', 'pronamic_ideal' ),
			'capability' => 'manage_options',
			'menu_slug'  => 'edit.php?post_type=pronamic_gateway',
		];

		$submenu_pages[] = [
			'page_title' => __( 'Settings', 'pronamic_ideal' ),
			'menu_title' => __( 'Settings', 'pronamic_ideal' ),
			'capability' => 'manage_options',
			'menu_slug'  => 'pronamic_pay_settings',
			'function'   => function (): void {
				$this->render_page( 'settings' );
			},
		];

		$minimum_capability = $this->get_minimum_capability( $submenu_pages );

		try {
			$menu_icon_url = $this->get_menu_icon_url();
		} catch ( \Exception ) {
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
			function (): void {
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
		return match ( $post_status ) {
			'payment_pending', 'subscr_pending' => 'pronamic-pay-icon-pending',
			'payment_cancelled', 'subscr_cancelled' => 'pronamic-pay-icon-cancelled',
			'payment_completed', 'subscr_completed' => 'pronamic-pay-icon-completed',
			'payment_refunded' => 'pronamic-pay-icon-refunded',
			'payment_failed', 'subscr_failed' => 'pronamic-pay-icon-failed',
			'payment_on_hold', 'payment_expired', 'subscr_expired', 'subscr_on_hold' => 'pronamic-pay-icon-on-hold',
			default => 'pronamic-pay-icon-processing',
		};
	}
}
