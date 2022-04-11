<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Admin\AdminModule;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Gateways\GatewaysDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\PaymentsDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Payments\StatusChecker;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionsDataStoreCPT;
use Pronamic\WordPress\Pay\Webhooks\WebhookLogger;
use WP_Error;
use WP_Query;

/**
 * Plugin
 *
 * @author  Remco Tolsma
 * @version 2.5.1
 * @since   2.0.1
 */
class Plugin {
	/**
	 * Version.
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * The root file of this WordPress plugin
	 *
	 * @var string
	 */
	public static $file;

	/**
	 * The plugin dirname
	 *
	 * @var string
	 */
	public static $dirname;

	/**
	 * The timezone
	 *
	 * @var string
	 */
	const TIMEZONE = 'UTC';

	/**
	 * Instance.
	 *
	 * @var Plugin|null
	 */
	protected static $instance;

	/**
	 * Instance.
	 *
	 * @param string|array|object $args The plugin arguments.
	 *
	 * @return Plugin
	 */
	public static function instance( $args = array() ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Plugin settings.
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * Gateway data storing.
	 *
	 * @var GatewaysDataStoreCPT
	 */
	public $gateways_data_store;

	/**
	 * Payment data storing.
	 *
	 * @var PaymentsDataStoreCPT
	 */
	public $payments_data_store;

	/**
	 * Subscription data storing.
	 *
	 * @var SubscriptionsDataStoreCPT
	 */
	public $subscriptions_data_store;

	/**
	 * Gateway post type.
	 *
	 * @var GatewayPostType
	 */
	public $gateway_post_type;

	/**
	 * Payment post type.
	 *
	 * @var PaymentPostType
	 */
	public $payment_post_type;

	/**
	 * Subscription post type.
	 *
	 * @var SubscriptionPostType
	 */
	public $subscription_post_type;

	/**
	 * Licence manager.
	 *
	 * @var LicenseManager
	 */
	public $license_manager;

	/**
	 * Privacy manager.
	 *
	 * @var PrivacyManager
	 */
	public $privacy_manager;

	/**
	 * Admin module.
	 *
	 * @var AdminModule
	 */
	public $admin;

	/**
	 * Blocks module.
	 *
	 * @var Blocks\BlocksModule
	 */
	public $blocks_module;

	/**
	 * Forms module.
	 *
	 * @var Forms\FormsModule
	 */
	public $forms_module;

	/**
	 * Tracking module.
	 *
	 * @var TrackingModule
	 */
	public $tracking_module;

	/**
	 * Payments module.
	 *
	 * @var Payments\PaymentsModule
	 */
	public $payments_module;

	/**
	 * Subscriptions module.
	 *
	 * @var Subscriptions\SubscriptionsModule
	 */
	public $subscriptions_module;

	/**
	 * Google analytics ecommerce.
	 *
	 * @var GoogleAnalyticsEcommerce
	 */
	public $google_analytics_ecommerce;

	/**
	 * Gateway integrations.
	 *
	 * @var GatewayIntegrations
	 */
	public $gateway_integrations;

	/**
	 * Integrations
	 *
	 * @var AbstractIntegration[]
	 */
	public $integrations;

	/**
	 * Webhook logger.
	 *
	 * @var WebhookLogger
	 */
	private $webhook_logger;

	/**
	 * Options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Plugin integrations.
	 *
	 * @var array
	 */
	public $plugin_integrations;

	/**
	 * Construct and initialize an Pronamic Pay plugin object.
	 *
	 * @param string|array|object $args The plugin arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'file'    => null,
				'options' => array(),
			)
		);

		// Version from plugin file header.
		if ( null !== $args['file'] ) {
			$file_data = get_file_data( $args['file'], array( 'Version' => 'Version' ) );

			if ( \array_key_exists( 'Version', $file_data ) ) {
				$this->version = $file_data['Version'];
			}
		}

		// Backward compatibility.
		self::$file    = $args['file'];
		self::$dirname = dirname( self::$file );

		// Options.
		$this->options = $args['options'];

		// Integrations.
		$this->integrations = array();

		/*
		 * Plugins loaded.
		 *
		 * Priority should be at least lower then 8 to support the "WP eCommerce" plugin.
		 *
		 * new WP_eCommerce()
		 * add_action( 'plugins_loaded' , array( $this, 'init' ), 8 );
		 * $this->load();
		 * wpsc_core_load_gateways();
		 *
		 * @link https://github.com/wp-e-commerce/WP-e-Commerce/blob/branch-3.11.2/wp-shopping-cart.php#L342-L343
		 * @link https://github.com/wp-e-commerce/WP-e-Commerce/blob/branch-3.11.2/wp-shopping-cart.php#L26-L35
		 * @link https://github.com/wp-e-commerce/WP-e-Commerce/blob/branch-3.11.2/wp-shopping-cart.php#L54
		 * @link https://github.com/wp-e-commerce/WP-e-Commerce/blob/branch-3.11.2/wp-shopping-cart.php#L296-L297
		 */
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 0 );

		// Plugin locale.
		add_filter( 'plugin_locale', array( $this, 'plugin_locale' ), 10, 2 );

		// Register styles.
		add_action( 'init', array( $this, 'register_styles' ), 9 );

		// If WordPress is loaded check on returns and maybe redirect requests.
		add_action( 'wp_loaded', array( $this, 'handle_returns' ), 10 );
		add_action( 'wp_loaded', array( $this, 'maybe_redirect' ), 10 );

		// Default date time format.
		add_filter( 'pronamic_datetime_default_format', array( $this, 'datetime_format' ), 10, 1 );

		/**
		 * Action scheduler.
		 *
		 * @link https://actionscheduler.org/
		 */
		if ( ! \array_key_exists( 'action_scheduler', $args ) ) {
			$args['action_scheduler'] = self::$dirname . '/wp-content/plugins/action-scheduler/action-scheduler.php';
		}

		require_once $args['action_scheduler'];
	}

	/**
	 * Get the version number of this plugin.
	 *
	 * @return string The version number of this plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get plugin file path.
	 *
	 * @return string
	 */
	public function get_file() {
		return self::$file;
	}

	/**
	 * Get option.
	 *
	 * @param string $option Name of option to retrieve.
	 * @return string|null
	 */
	public function get_option( $option ) {
		if ( array_key_exists( $option, $this->options ) ) {
			return $this->options[ $option ];
		}

		return null;
	}

	/**
	 * Get the plugin dir path.
	 *
	 * @return string
	 */
	public function get_plugin_dir_path() {
		return plugin_dir_path( $this->get_file() );
	}

	/**
	 * Update payment.
	 *
	 * @param Payment $payment      The payment to update.
	 * @param bool    $can_redirect Flag to indicate if redirect is allowed after the payment update.
	 * @return void
	 */
	public static function update_payment( $payment = null, $can_redirect = true ) {
		if ( empty( $payment ) ) {
			return;
		}

		// Gateway.
		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			return;
		}

		// Update status.
		try {
			$gateway->update_status( $payment );

			// Update payment in data store.
			$payment->save();
		} catch ( \Exception $error ) {
			$message = $error->getMessage();

			// Maybe include error code in message.
			$code = $error->getCode();

			if ( $code > 0 ) {
				$message = \sprintf( '%s: %s', $code, $message );
			}

			// Add note.
			$payment->add_note( $message );
		}

		// Maybe redirect.
		if ( ! $can_redirect ) {
			return;
		}

		/*
		 * If WordPress is doing cron we can't redirect.
		 *
		 * @link https://github.com/pronamic/wp-pronamic-ideal/commit/bb967a3e7804ecfbd83dea110eb8810cbad097d7
		 * @link https://github.com/pronamic/wp-pronamic-ideal/commit/3ab4a7c1fc2cef0b6f565f8205da42aa1203c3c5
		 */
		if ( \wp_doing_cron() ) {
			return;
		}

		/*
		 * If WordPress CLI is running we can't redirect.
		 *
		 * @link https://basecamp.com/1810084/projects/10966871/todos/346407847
		 * @link https://github.com/woocommerce/woocommerce/blob/3.5.3/includes/class-woocommerce.php#L381-L383
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		// Redirect.
		$url = $payment->get_return_redirect_url();

		wp_redirect( $url );

		exit;
	}

	/**
	 * Handle returns.
	 *
	 * @return void
	 */
	public function handle_returns() {
		if ( ! filter_has_var( INPUT_GET, 'payment' ) ) {
			return;
		}

		$payment_id = filter_input( INPUT_GET, 'payment', FILTER_SANITIZE_NUMBER_INT );

		$payment = get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check if payment key is valid.
		$valid_key = false;

		if ( empty( $payment->key ) ) {
			$valid_key = true;
		} elseif ( filter_has_var( INPUT_GET, 'key' ) ) {
			$key = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );

			$valid_key = ( $key === $payment->key );
		}

		if ( ! $valid_key ) {
			wp_safe_redirect( home_url() );

			exit;
		}

		// Check if we should redirect.
		$should_redirect = true;

		/**
		 * Filter whether or not to allow redirects on payment return.
		 *
		 * @param bool    $should_redirect Flag to indicate if redirect is allowed on handling payment return.
		 * @param Payment $payment         Payment.
		 */
		$should_redirect = apply_filters( 'pronamic_pay_return_should_redirect', $should_redirect, $payment );

		try {
			self::update_payment( $payment, $should_redirect );
		} catch ( \Exception $e ) {
			self::render_exception( $e );

			exit;
		}
	}

	/**
	 * Maybe redirect.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		if ( ! filter_has_var( INPUT_GET, 'payment_redirect' ) || ! filter_has_var( INPUT_GET, 'key' ) ) {
			return;
		}

		// Get payment.
		$payment_id = filter_input( INPUT_GET, 'payment_redirect', FILTER_SANITIZE_NUMBER_INT );

		$payment = get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Validate key.
		$key = filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );

		if ( $key !== $payment->key || empty( $payment->key ) ) {
			return;
		}

		// Don't cache.
		Core_Util::no_cache();

		// Switch to user locale.
		Core_Util::switch_to_user_locale();

		// Handle redirect message from payment meta.
		$redirect_message = $payment->get_meta( 'payment_redirect_message' );

		if ( ! empty( $redirect_message ) ) {
			require self::$dirname . '/views/redirect-message.php';

			exit;
		}

		$gateway = $payment->get_gateway();

		if ( null !== $gateway ) {
			// Give gateway a chance to handle redirect.
			$gateway->payment_redirect( $payment );

			// Handle HTML form redirect.
			if ( $gateway->is_html_form() ) {
				$gateway->start( $payment );

				$gateway->redirect( $payment );
			}
		}

		// Redirect to payment action URL.
		$action_url = $payment->get_action_url();

		if ( ! empty( $action_url ) ) {
			wp_redirect( $action_url );

			exit;
		}
	}

	/**
	 * Get number payments.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_count_posts/
	 *
	 * @return int|false
	 */
	public static function get_number_payments() {
		$number = false;

		$count = wp_count_posts( 'pronamic_payment' );

		if ( isset( $count->payment_completed ) ) {
			$number = intval( $count->payment_completed );
		}

		return $number;
	}

	/**
	 * Plugins loaded.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/plugins_loaded/
	 * @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
	 * @return void
	 */
	public function plugins_loaded() {
		// Load plugin textdomain.
		self::load_plugin_textdomain();

		// Settings.
		$this->settings = new Settings( $this );

		// Data Stores.
		$this->gateways_data_store      = new GatewaysDataStoreCPT();
		$this->payments_data_store      = new PaymentsDataStoreCPT();
		$this->subscriptions_data_store = new SubscriptionsDataStoreCPT();

		$this->payments_data_store->setup();
		$this->subscriptions_data_store->setup();

		// Post Types.
		$this->gateway_post_type      = new GatewayPostType();
		$this->payment_post_type      = new PaymentPostType();
		$this->subscription_post_type = new SubscriptionPostType();

		// License Manager.
		$this->license_manager = new LicenseManager( $this );

		// Privacy Manager.
		$this->privacy_manager = new PrivacyManager();

		// Webhook Logger.
		$this->webhook_logger = new WebhookLogger();
		$this->webhook_logger->setup();

		// Modules.
		$this->forms_module         = new Forms\FormsModule( $this );
		$this->payments_module      = new Payments\PaymentsModule( $this );
		$this->subscriptions_module = new Subscriptions\SubscriptionsModule( $this );
		$this->tracking_module      = new TrackingModule();

		// Blocks module.
		if ( function_exists( 'register_block_type' ) ) {
			$this->blocks_module = new Blocks\BlocksModule();
			$this->blocks_module->setup();
		}

		// Google Analytics Ecommerce.
		$this->google_analytics_ecommerce = new GoogleAnalyticsEcommerce();

		// Admin.
		if ( is_admin() ) {
			$this->admin = new Admin\AdminModule( $this );
		}

		$gateways = array();

		/**
		 * Filters the gateway integrations.
		 *
		 * @param AbstractGatewayIntegration[] $gateways Gateway integrations.
		 */
		$gateways = apply_filters( 'pronamic_pay_gateways', $gateways );

		$this->gateway_integrations = new GatewayIntegrations( $gateways );

		foreach ( $this->gateway_integrations as $integration ) {
			$integration->setup();
		}

		$plugin_integrations = array();

		/**
		 * Filters the plugin integrations.
		 *
		 * @param AbstractPluginIntegration[] $plugin_integrations Plugin integrations.
		 */
		$this->plugin_integrations = apply_filters( 'pronamic_pay_plugin_integrations', $plugin_integrations );

		foreach ( $this->plugin_integrations as $integration ) {
			$integration->setup();
		}

		// Integrations.
		$gateway_integrations = \iterator_to_array( $this->gateway_integrations );

		$this->integrations = array_merge( $gateway_integrations, $this->plugin_integrations );

		// Maybes.
		PaymentMethods::maybe_update_active_payment_methods();

		// Filters.
		\add_filter( 'pronamic_payment_redirect_url', array( $this, 'payment_redirect_url' ), 10, 2 );

		// Actions.
		\add_action( 'pronamic_pay_pre_create_payment', array( __CLASS__, 'complement_payment' ), 10, 1 );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		$rel_path = \dirname( \plugin_basename( self::$file ) );

		\load_plugin_textdomain( 'pronamic_ideal', false, $rel_path . '/languages' );

		\load_plugin_textdomain( 'pronamic-money', false, $rel_path . '/vendor/pronamic/wp-money/languages' );
	}

	/**
	 * Filter plugin locale.
	 *
	 * @param string $locale A WordPress locale identifier.
	 * @param string $domain A WordPress text domain identifier.
	 *
	 * @return string
	 */
	public function plugin_locale( $locale, $domain ) {
		if ( 'pronamic_ideal' !== $domain ) {
			return $locale;
		}

		if ( 'nl_NL_formal' === $locale ) {
			return 'nl_NL';
		}

		if ( 'nl_BE' === $locale ) {
			return 'nl_NL';
		}

		return $locale;
	}

	/**
	 * Default date time format.
	 *
	 * @param string $format Format.
	 *
	 * @return string
	 */
	public function datetime_format( $format ) {
		$format = _x( 'D j M Y \a\t H:i', 'default datetime format', 'pronamic_ideal' );

		return $format;
	}

	/**
	 * Get default error message.
	 *
	 * @return string
	 */
	public static function get_default_error_message() {
		return __( 'Something went wrong with the payment. Please try again or pay another way.', 'pronamic_ideal' );
	}

	/**
	 * Register styles.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function register_styles() {
		$min = \SCRIPT_DEBUG ? '' : '.min';

		\wp_register_style(
			'pronamic-pay-redirect',
			\plugins_url( 'css/redirect' . $min . '.css', \dirname( __FILE__ ) ),
			array(),
			$this->get_version()
		);
	}

	/**
	 * Get config select options.
	 *
	 * @param null|string $payment_method The gateway configuration options for the specified payment method.
	 *
	 * @return array
	 */
	public static function get_config_select_options( $payment_method = null ) {
		$args = array(
			'post_type' => 'pronamic_gateway',
			'orderby'   => 'post_title',
			'order'     => 'ASC',
			'nopaging'  => true,
		);

		if ( null !== $payment_method ) {
			$config_ids = PaymentMethods::get_config_ids( $payment_method );

			$args['post__in'] = empty( $config_ids ) ? array( 0 ) : $config_ids;
		}

		$query = new WP_Query( $args );

		$options = array( __( '— Select Configuration —', 'pronamic_ideal' ) );

		foreach ( $query->posts as $post ) {
			if ( ! \is_object( $post ) ) {
				continue;
			}

			$id = $post->ID;

			$options[ $id ] = \get_the_title( $id );
		}

		return $options;
	}

	/**
	 * Render errors.
	 *
	 * @param array|WP_Error $errors An array with errors to render.
	 * @return void
	 */
	public static function render_errors( $errors = array() ) {
		if ( ! is_array( $errors ) ) {
			$errors = array( $errors );
		}

		foreach ( $errors as $pay_error ) {
			include self::$dirname . '/views/error.php';
		}
	}

	/**
	 * Render exception.
	 *
	 * @param \Exception $exception An exception.
	 * @return void
	 */
	public static function render_exception( \Exception $exception ) {
		include self::$dirname . '/views/exception.php';
	}

	/**
	 * Get gateway.
	 *
	 * @link https://wordpress.org/support/article/post-status/#default-statuses
	 *
	 * @param int   $config_id A gateway configuration ID.
	 * @param array $args      Extra arguments.
	 *
	 * @return null|Gateway
	 */
	public static function get_gateway( $config_id, $args = array() ) {
		// Get gateway from data store.
		$gateway = \pronamic_pay_plugin()->gateways_data_store->get_gateway( $config_id );

		// Use gateway identifier from arguments to get new gateway.
		if ( null === $gateway && ! empty( $args ) ) {
			// Get integration.
			$args = wp_parse_args(
				$args,
				array(
					'gateway_id' => \get_post_meta( $config_id, '_pronamic_gateway_id', true ),
				)
			);

			$integration = pronamic_pay_plugin()->gateway_integrations->get_integration( $args['gateway_id'] );

			// Get new gateway.
			if ( null !== $integration ) {
				$gateway = $integration->get_gateway( $config_id );
			}
		}

		return $gateway;
	}

	/**
	 * Complement payment.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public static function complement_payment( Payment $payment ) {
		// Key.
		if ( null === $payment->key ) {
			$payment->key = uniqid( 'pay_' );
		}

		$origin_id = $payment->get_origin_id();

		if ( null === $origin_id ) {
			// Queried object.
			$queried_object    = \get_queried_object();
			$queried_object_id = \get_queried_object_id();

			if ( null !== $queried_object && $queried_object_id > 0 ) {
				$origin_id = $queried_object_id;
			}

			// Referer.
			$referer = \wp_get_referer();

			if ( null === $origin_id && false !== $referer ) {
				$post_id = \url_to_postid( $referer );

				if ( $post_id > 0 ) {
					$origin_id = $post_id;
				}
			}

			// Set origin ID.
			$payment->set_origin_id( $origin_id );
		}

		// Google Analytics client ID.
		$google_analytics_client_id = $payment->get_meta( 'google_analytics_client_id' );

		if ( null === $google_analytics_client_id ) {
			$google_analytics_client_id = GoogleAnalyticsEcommerce::get_cookie_client_id();

			if ( null !== $google_analytics_client_id ) {
				$payment->set_meta( 'google_analytics_client_id', $google_analytics_client_id );
			}
		}

		// Customer.
		$customer = $payment->get_customer();

		if ( null === $customer ) {
			$customer = new Customer();

			$payment->set_customer( $customer );
		}

		CustomerHelper::complement_customer( $customer );

		// Billing address.
		$billing_address = $payment->get_billing_address();

		if ( null !== $billing_address ) {
			AddressHelper::complement_address( $billing_address );
		}

		// Shipping address.
		$shipping_address = $payment->get_shipping_address();

		if ( null !== $shipping_address ) {
			AddressHelper::complement_address( $shipping_address );
		}

		// Version.
		if ( null === $payment->get_version() ) {
			$payment->set_version( pronamic_pay_plugin()->get_version() );
		}

		// Issuer.
		$issuer = $payment->get_meta( 'issuer' );

		if ( null === $issuer ) {
			// Credit card.
			if ( PaymentMethods::CREDIT_CARD === $payment->get_payment_method() && \filter_has_var( INPUT_POST, 'pronamic_credit_card_issuer_id' ) ) {
				$issuer = \filter_input( INPUT_POST, 'pronamic_credit_card_issuer_id', FILTER_SANITIZE_STRING );
			}

			// iDEAL.
			$ideal_methods = array( PaymentMethods::IDEAL, PaymentMethods::DIRECT_DEBIT_IDEAL );

			if ( \in_array( $payment->get_payment_method(), $ideal_methods, true ) && \filter_has_var( INPUT_POST, 'pronamic_ideal_issuer_id' ) ) {
				$issuer = \filter_input( INPUT_POST, 'pronamic_ideal_issuer_id', FILTER_SANITIZE_STRING );
			}

			if ( ! empty( $issuer ) ) {
				$payment->set_meta( 'issuer', $issuer );
			}
		}

		/**
		 * If an issuer has been specified and the payment
		 * method is unknown, we set the payment method to
		 * iDEAL. This may not be correct in all cases,
		 * but for now Pronamic Pay works this way.
		 *
		 * @link https://github.com/wp-pay-extensions/gravityforms/blob/2.4.0/src/Processor.php#L251-L256
		 * @link https://github.com/wp-pay-extensions/contact-form-7/blob/1.0.0/src/Pronamic.php#L181-L187
		 * @link https://github.com/wp-pay-extensions/formidable-forms/blob/2.1.0/src/Extension.php#L318-L329
		 * @link https://github.com/wp-pay-extensions/ninjaforms/blob/1.2.0/src/PaymentGateway.php#L80-L83
		 * @link https://github.com/wp-pay/core/blob/2.4.0/src/Forms/FormProcessor.php#L131-L134
		 */
		$issuer = $payment->get_meta( 'issuer' );

		$payment_method = $payment->get_payment_method();

		if ( null !== $issuer && null === $payment_method ) {
			$payment->set_payment_method( PaymentMethods::IDEAL );
		}

		// Consumer bank details.
		$consumer_bank_details = $payment->get_consumer_bank_details();

		if ( null === $consumer_bank_details ) {
			$consumer_bank_details = new BankAccountDetails();
		}

		if ( null === $consumer_bank_details->get_name() && filter_has_var( INPUT_POST, 'pronamic_pay_consumer_bank_details_name' ) ) {
			$consumer_bank_details->set_name( filter_input( INPUT_POST, 'pronamic_pay_consumer_bank_details_name', FILTER_SANITIZE_STRING ) );
		}

		if ( null === $consumer_bank_details->get_iban() && filter_has_var( INPUT_POST, 'pronamic_pay_consumer_bank_details_iban' ) ) {
			$consumer_bank_details->set_iban( filter_input( INPUT_POST, 'pronamic_pay_consumer_bank_details_iban', FILTER_SANITIZE_STRING ) );
		}

		$payment->set_consumer_bank_details( $consumer_bank_details );

		// Payment lines payment.
		$lines = $payment->get_lines();

		if ( null !== $lines ) {
			foreach ( $lines as $line ) {
				$line->set_payment( $payment );
			}
		}
	}

	/**
	 * Get default gateway configuration ID.
	 *
	 * @return int|null
	 */
	private static function get_default_config_id() {
		$value = (int) \get_option( 'pronamic_pay_config_id' );

		if ( 0 === $value ) {
			return null;
		}

		if ( 'publish' !== \get_post_status( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * Start payment.
	 *
	 * @param Payment $payment The payment to start at the specified gateway.
	 * @return Payment
	 * @throws \Exception Throws exception if gateway payment start fails.
	 */
	public static function start_payment( Payment $payment ) {
		// Set default or filtered config ID.
		$config_id = $payment->get_config_id();

		if ( null === $config_id ) {
			$config_id = self::get_default_config_id();
		}

		/**
		 * Filters the payment gateway configuration ID.
		 *
		 * @param null|int $config_id Gateway configuration ID.
		 * @param Payment  $payment   Payment.
		 */
		$config_id = \apply_filters( 'pronamic_payment_gateway_configuration_id', $config_id, $payment );

		if ( null !== $config_id ) {
			$payment->set_config_id( $config_id );
		}

		// Save payment.
		$payment->save();

		// Periods.
		$periods = $payment->get_periods();

		if ( null !== $periods ) {
			foreach ( $periods as $period ) {
				$subscription = $period->get_phase()->get_subscription();

				$subscription->set_next_payment_date( \max( $subscription->get_next_payment_date(), $period->get_end_date() ) );
			}
		}

		// Subscriptions.
		$subscriptions = $payment->get_subscriptions();

		foreach ( $subscriptions as $subscription ) {
			$subscription->save();
		}

		// Gateway.
		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			$payment->set_status( PaymentStatus::FAILURE );

			$payment->save();

			return $payment;
		}

		// Mode.
		$payment->set_mode( $gateway->get_mode() );

		// Subscriptions.
		$subscriptions = $payment->get_subscriptions();

		if ( \count( $subscriptions ) > 0 && ! $gateway->supports( 'recurring' ) ) {
			throw new \Exception( 'Gateway does not support recurring payments.' );
		}

		// Start payment at the gateway.
		try {
			$gateway->start( $payment );
		} catch ( \Exception $exception ) {
			$message = $exception->getMessage();

			// Maybe include error code in message.
			$code = $exception->getCode();

			if ( $code > 0 ) {
				$message = \sprintf( '%s: %s', $code, $message );
			}

			$payment->add_note( $message );

			$payment->set_status( PaymentStatus::FAILURE );

			throw $exception;
		} finally {
			$payment->save();
		}

		// Schedule payment status check.
		if ( $gateway->supports( 'payment_status_request' ) ) {
			StatusChecker::schedule_event( $payment );
		}

		return $payment;
	}

	/**
	 * Create refund.
	 *
	 * @param string      $transaction_id Gateway transaction ID.
	 * @param Gateway     $gateway        Gateway.
	 * @param Money       $amount         Refund amount.
	 * @param string|null $description    Refund description.
	 * @return string|null
	 * @throws \Exception Throws exception on error.
	 */
	public static function create_refund( $transaction_id, $gateway, Money $amount, $description = null ) {
		// Check if gateway supports refunds.
		if ( ! $gateway->supports( 'refunds' ) || ! \method_exists( $gateway, 'create_refund' ) ) {
			throw new \Exception( __( 'Unable to process refund as gateway does not support refunds.', 'pronamic_ideal' ) );
		}

		// Check amount.
		if ( $amount->get_value() <= 0 ) {
			throw new \Exception(
				sprintf(
					/* translators: %s: formatted amount */
					__( 'Unable to process refund because of invalid amount (%s).', 'pronamic_ideal' ),
					$amount->format_i18n()
				)
			);
		}

		// Create refund.
		$reference = $gateway->create_refund( $transaction_id, $amount, $description );

		// Add note to original payment.
		$payment = \get_pronamic_payment_by_transaction_id( $transaction_id );

		if ( null !== $payment ) {
			/* translators: 1: refunded amount */
			$format = __( 'Refunded %1$s.', 'pronamic_ideal' );

			if ( ! empty( $reference ) ) {
				/* translators: 1: refunded amount, 3: refund reference */
				$format = __( 'Refunded %1$s with gateway reference `%3$s`.', 'pronamic_ideal' );
			}

			if ( ! empty( $description ) ) {
				/* translators: 1: refunded amount, 2: refund description */
				$format = __( 'Refunded %1$s ("%2$s").', 'pronamic_ideal' );

				if ( ! empty( $reference ) ) {
					/* translators: 1: refunded amount, 2: refund description, 3: refund reference */
					$format = __( 'Refunded %1$s ("%2$s") with gateway reference `%3$s`.', 'pronamic_ideal' );
				}
			}

			$note = sprintf(
				$format,
				$amount->format_i18n(),
				$description,
				$reference
			);

			$payment->add_note( $note );
		}

		return $reference;
	}

	/**
	 * Get pages.
	 *
	 * @return array
	 */
	public function get_pages() {
		$return = array();

		$pages = array(
			'completed' => __( 'Completed', 'pronamic_ideal' ),
			'cancel'    => __( 'Canceled', 'pronamic_ideal' ),
			'expired'   => __( 'Expired', 'pronamic_ideal' ),
			'error'     => __( 'Error', 'pronamic_ideal' ),
			'unknown'   => __( 'Unknown', 'pronamic_ideal' ),
		);

		foreach ( $pages as $key => $label ) {
			$id = sprintf( 'pronamic_pay_%s_page_id', $key );

			$return[ $id ] = $label;
		}

		return $return;
	}

	/**
	 * Payment redirect URL.
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 * @return string
	 */
	public function payment_redirect_url( $url, Payment $payment ) {
		$source = $payment->get_source();

		/**
		 * Filters the payment redirect URL by plugin integration source.
		 *
		 * @param null|string $url     Redirect URL.
		 * @param Payment     $payment Payment.
		 */
		$url = \apply_filters( 'pronamic_payment_redirect_url_' . $source, $url, $payment );

		return $url;
	}

	/**
	 * Is debug mode.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/2.9.26/includes/misc-functions.php#L26-L38
	 * @return bool True if debug mode is enabled, false otherwise.
	 */
	public function is_debug_mode() {
		$value = \get_option( 'pronamic_pay_debug_mode', false );

		if ( PRONAMIC_PAY_DEBUG ) {
			$value = true;
		}

		return (bool) $value;
	}
}
