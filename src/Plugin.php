<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Admin\AdminModule;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Recurring;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Gateways\Common\AbstractIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentData;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Payments\StatusChecker;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;
use WP_Error;
use WP_Query;

/**
 * Plugin
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.0.1
 */
class Plugin {
	/**
	 * Version.
	 *
	 * @var string
	 */
	private $version;

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
	 * Gateway integrations.
	 *
	 * @since 2.0.3
	 * @var array
	 */
	public static $gateways;

	/**
	 * Instance.
	 *
	 * @var Plugin
	 */
	protected static $instance = null;

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
	 * Payment data storing.
	 *
	 * @var Payments\PaymentsDataStoreCPT
	 */
	public $payments_data_store;

	/**
	 * Subscription data storing.
	 *
	 * @var Subscriptions\SubscriptionsDataStoreCPT
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
	 * Forms module.
	 *
	 * @var Forms\FormsModule
	 */
	public $forms_module;

	/**
	 * Payments module.
	 *
	 * @var Payments\PaymentsModule
	 */
	public $payments_module;

	/**
	 * Subsciptions module.
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
	 * @var AbstractIntegration[]
	 */
	public $gateway_integrations;

	/**
	 * Construct and initialize an Pronamic Pay plugin object.
	 *
	 * @param string|array|object $args The plugin arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'file'       => null,
				'version'    => null,
				'extensions' => array(),
				'gateways'   => array(),
			)
		);

		$this->version = $args['version'];

		// Backward compatibility.
		self::$file     = $args['file'];
		self::$dirname  = dirname( self::$file );
		self::$gateways = $args['gateways'];

		// Bootstrap the add-ons.
		$extensions = $args['extensions'];

		if ( is_array( $extensions ) ) {
			foreach ( $extensions as $extension ) {
				call_user_func( $extension );
			}
		}

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
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 5 );

		// Plugin locale.
		add_filter( 'plugin_locale', array( $this, 'plugin_locale' ), 10, 2 );

		// Register styles.
		add_action( 'wp_loaded', array( $this, 'register_styles' ), 9 );

		// If WordPress is loaded check on returns and maybe redirect requests.
		add_action( 'wp_loaded', array( $this, 'handle_returns' ), 10 );
		add_action( 'wp_loaded', array( $this, 'maybe_redirect' ), 10 );

		// Default date time format.
		add_filter( 'pronamic_datetime_default_format', array( $this, 'datetime_format' ), 10, 1 );
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
	 */
	public static function update_payment( $payment = null, $can_redirect = true ) {
		if ( empty( $payment ) ) {
			return;
		}

		// Gateway.
		$gateway = self::get_gateway( $payment->config_id );

		if ( empty( $gateway ) ) {
			return;
		}

		// Update status.
		$gateway->update_status( $payment );

		// Add gateway errors as payment notes.
		if ( $gateway->has_error() ) {
			foreach ( $gateway->error->get_error_codes() as $code ) {
				$payment->add_note( sprintf( '%s: %s', $code, $gateway->error->get_error_message( $code ) ) );
			}
		}

		// Update payment in data store.
		$payment->save();

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
		if ( Core_Util::doing_cron() ) {
			return;
		}

		/*
		 * If WordPress CLI is runnig we can't redirect.
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

		// Check if the request is an callback request.
		// Sisow gatway will extend callback requests with querystring "callback=true".
		if ( filter_has_var( INPUT_GET, 'callback' ) && filter_input( INPUT_GET, 'callback', FILTER_VALIDATE_BOOLEAN ) ) {
			$should_redirect = false;
		}

		// Check if the request is an notify request.
		// Sisow gatway will extend callback requests with querystring "notify=true".
		if ( filter_has_var( INPUT_GET, 'notify' ) && filter_input( INPUT_GET, 'notify', FILTER_VALIDATE_BOOLEAN ) ) {
			$should_redirect = false;
		}

		self::update_payment( $payment, $should_redirect );
	}

	/**
	 * Maybe redirect.
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

		// Handle redirect message from payment meta.
		$redirect_message = $payment->get_meta( 'payment_redirect_message' );

		if ( ! empty( $redirect_message ) ) {
			require self::$dirname . '/views/redirect-message.php';

			exit;
		}

		$gateway = self::get_gateway( $payment->config_id );

		if ( $gateway ) {
			// Give gateway a chance to handle redirect.
			$gateway->payment_redirect( $payment );

			// Handle HTML form redirect.
			if ( $gateway->is_html_form() ) {
				$gateway->start( $payment );

				$error = $gateway->get_error();

				if ( is_wp_error( $error ) ) {
					self::render_errors( $error );
				} else {
					$gateway->redirect( $payment );
				}
			}
		}

		// Redirect to payment action URL.
		if ( ! empty( $payment->action_url ) ) {
			wp_redirect( $payment->action_url );

			exit;
		}
	}

	/**
	 * Get number payments.
	 *
	 * @return int
	 */
	public static function get_number_payments() {
		$number = false;

		$count = wp_count_posts( 'pronamic_payment' );

		if ( isset( $count, $count->payment_completed ) ) {
			$number = intval( $count->payment_completed );
		}

		return $number;
	}

	/**
	 * Setup, creates or updates database tables. Will only run when version changes.
	 */
	public function plugins_loaded() {
		// Load plugin text domain.
		$rel_path = dirname( plugin_basename( self::$file ) );

		load_plugin_textdomain( 'pronamic_ideal', false, $rel_path . '/languages' );

		load_plugin_textdomain( 'pronamic-money', false, $rel_path . '/vendor/pronamic/wp-money/languages' );

		// Settings.
		$this->settings = new Settings( $this );

		// Data Stores.
		$this->payments_data_store      = new Payments\PaymentsDataStoreCPT();
		$this->subscriptions_data_store = new Subscriptions\SubscriptionsDataStoreCPT();

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

		// Modules.
		$this->forms_module         = new Forms\FormsModule( $this );
		$this->payments_module      = new Payments\PaymentsModule( $this );
		$this->subscriptions_module = new Subscriptions\SubscriptionsModule( $this );

		// Google Analytics Ecommerce.
		$this->google_analytics_ecommerce = new GoogleAnalyticsEcommerce();

		// Admin.
		if ( is_admin() ) {
			$this->admin = new Admin\AdminModule( $this );
		}

		// Gateway Integrations.
		$gateways     = apply_filters( 'pronamic_pay_gateways', self::$gateways );
		$integrations = new GatewayIntegrations( $gateways );

		$this->gateway_integrations = $integrations->register_integrations();

		// Maybes.
		PaymentMethods::maybe_update_active_payment_methods();
	}

	/**
	 * Filter plugin locale.
	 *
	 * @param string $locale A WordPress locale identifier.
	 * @param string $domain A WordPress text domain indentifier.
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
		return __( 'Something went wrong with the payment. Please try again later or pay another way.', 'pronamic_ideal' );
	}

	/**
	 * Register styles.
	 *
	 * @since 2.1.6
	 */
	public function register_styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'pronamic-pay-redirect',
			plugins_url( 'css/redirect' . $min . '.css', $this->get_file() ),
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

		if ( $payment_method ) {
			$args['post__in'] = PaymentMethods::get_config_ids( $payment_method );
		}

		$query = new WP_Query( $args );

		$options = array( __( '— Select Configuration —', 'pronamic_ideal' ) );

		foreach ( $query->posts as $post ) {
			$id = $post->ID;

			$options[ $id ] = sprintf(
				'%s (%s)',
				get_the_title( $id ),
				get_post_meta( $id, '_pronamic_gateway_mode', true )
			);
		}

		return $options;
	}

	/**
	 * Render errors.
	 *
	 * @param array|WP_Error $errors An array with errors to render.
	 */
	public static function render_errors( $errors = array() ) {
		if ( ! is_array( $errors ) ) {
			$errors = array( $errors );
		}

		foreach ( $errors as $error ) {
			include self::$dirname . '/views/error.php';
		}
	}

	/**
	 * Get gateway.
	 *
	 * @param string|integer|boolean $config_id A gateway configuration ID.
	 *
	 * @return null|Gateway
	 */
	public static function get_gateway( $config_id ) {
		// Check if config is published.
		if ( 'publish' !== get_post_status( $config_id ) ) {
			return null;
		}

		// Get config.
		$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );
		$mode       = get_post_meta( $config_id, '_pronamic_gateway_mode', true );
		$is_utf8    = strcasecmp( get_bloginfo( 'charset' ), 'UTF-8' ) === 0;

		$config = Core\ConfigProvider::get_config( $gateway_id, $config_id );

		// Adjust config for specific gateways.
		switch ( $gateway_id ) {
			case 'abnamro-ideal-zelfbouw-v3':
				$config->payment_server_url = 'https://abnamro.ideal-payment.de/ideal/iDEALv3';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->payment_server_url = 'https://abnamro-test.ideal-payment.de/ideal/iDEALv3';
				}

				$config->certificates = array();

				break;
			case 'deutschebank-ideal-expert-v3':
				$config->payment_server_url = 'https://myideal.db.com/ideal/iDealv3';

				$config->certificates = array();

				break;
			case 'ideal-simulator-ideal-basic':
				$config->url = 'https://www.ideal-simulator.nl/lite/';

				break;
			case 'ideal-simulator-ideal-advanced-v3':
				$config->payment_server_url = 'https://www.ideal-checkout.nl/simulator/';

				$config->certificates = array();

				break;
			case 'ing-ideal-basic':
				$config->url = 'https://ideal.secure-ing.com/ideal/mpiPayInitIng.do';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->url = 'https://idealtest.secure-ing.com/ideal/mpiPayInitIng.do';
				}

				break;
			case 'ing-ideal-advanced-v3':
				$config->payment_server_url = 'https://ideal.secure-ing.com/ideal/iDEALv3';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->payment_server_url = 'https://idealtest.secure-ing.com/ideal/iDEALv3';
				}

				$config->certificates = array();

				break;
			case 'mollie-ideal-basic':
				$config->url = 'https://secure.mollie.nl/xml/idealAcquirer/lite/';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->url = 'https://secure.mollie.nl/xml/idealAcquirer/testmode/lite/';
				}

				break;
			case 'postcode-ideal':
				$config->payment_server_url = 'https://ideal.postcode.nl/ideal';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->payment_server_url = 'https://ideal-test.postcode.nl/ideal';
				}

				$config->certificates = array();

				break;
			case 'rabobank-ideal-professional-v3':
				$config->payment_server_url = 'https://ideal.rabobank.nl/ideal/iDEALv3';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->payment_server_url = 'https://idealtest.rabobank.nl/ideal/iDEALv3';
				}

				$config->certificates = array();

				break;
			case 'sisow-ideal-basic':
				$config->url = 'https://www.sisow.nl/Sisow/iDeal/IssuerHandler.ashx';

				if ( Gateway::MODE_TEST === $mode ) {
					$config->url = 'https://www.sisow.nl/Sisow/iDeal/IssuerHandler.ashx/test';
				}

				break;
		}

		// Create and return gateway.
		$gateway = Core\GatewayFactory::create( $config );

		return $gateway;
	}

	/**
	 * Start a payment.
	 *
	 * @param string      $config_id      A gateway configuration ID.
	 * @param Gateway     $gateway        The gateway to start the payment at.
	 * @param PaymentData $data           A payment data interface object with all the required payment info.
	 * @param string|null $payment_method The payment method to use to start the payment.
	 *
	 * @return Payment
	 */
	public static function start( $config_id, Gateway $gateway, PaymentData $data, $payment_method = null ) {
		$payment = new Payment();

		/* translators: %s: payment data title */
		$payment->title                  = sprintf( __( 'Payment for %s', 'pronamic_ideal' ), $data->get_title() );
		$payment->config_id              = $config_id;
		$payment->order_id               = $data->get_order_id();
		$payment->description            = $data->get_description();
		$payment->source                 = $data->get_source();
		$payment->source_id              = $data->get_source_id();
		$payment->email                  = $data->get_email();
		$payment->method                 = $payment_method;
		$payment->issuer                 = $data->get_issuer( $payment_method );
		$payment->analytics_client_id    = $data->get_analytics_client_id();
		$payment->recurring              = $data->get_recurring();
		$payment->subscription           = $data->get_subscription();
		$payment->subscription_id        = $data->get_subscription_id();
		$payment->subscription_source_id = $data->get_subscription_source_id();
		$payment->set_total_amount( $data->get_amount() );
		$payment->set_credit_card( $data->get_credit_card() );

		// Customer.
		$customer = array(
			'name'    => (object) array(
				'first_name' => $data->get_first_name(),
				'last_name'  => $data->get_last_name(),
			),
			'email'   => $data->get_email(),
			'phone'   => $data->get_telephone_number(),
			'user_id' => $data->get_user_id(),
		);

		$customer = array_filter( $customer );

		if ( ! empty( $customer ) ) {
			$customer = Customer::from_json( (object) $customer );

			$payment->set_customer( $customer );
		}

		// Billing address.
		$billing_address = array(
			'name'         => ( $customer instanceof Customer ? $customer->get_name() : null ),
			'line_1'       => $data->get_address(),
			'postal_code'  => $data->get_zip(),
			'city'         => $data->get_city(),
			'country_name' => $data->get_country(),
			'email'        => $data->get_email(),
			'phone'        => $data->get_telephone_number(),
		);

		$billing_address = array_filter( $billing_address );

		if ( ! empty( $billing_address ) ) {
			$address = new Address();

			if ( isset( $billing_address['name'] ) ) {
				$address->set_name( $billing_address['name'] );
			}

			if ( isset( $billing_address['line_1'] ) ) {
				$address->set_line_1( $billing_address['line_1'] );
			}

			if ( isset( $billing_address['postal_code'] ) ) {
				$address->set_postal_code( $billing_address['postal_code'] );
			}

			if ( isset( $billing_address['city'] ) ) {
				$address->set_city( $billing_address['city'] );
			}

			if ( isset( $billing_address['country_name'] ) ) {
				$address->set_country_name( $billing_address['country_name'] );
			}

			if ( isset( $billing_address['email'] ) ) {
				$address->set_email( $billing_address['email'] );
			}

			if ( isset( $billing_address['phone'] ) ) {
				$address->set_phone( $billing_address['phone'] );
			}

			$payment->set_billing_address( $address );
		}

		// Start payment.
		return self::start_payment( $payment, $gateway );
	}

	/**
	 * Start recurring payment.
	 *
	 * @param Payment $payment Payment or subscription for backwards compatibility.
	 *
	 * @throws \Exception Throws an Exception on incorrect date interval.
	 *
	 * @return Payment
	 */
	public static function start_recurring_payment( Payment $payment ) {
		return pronamic_pay_plugin()->subscriptions_module->start_payment( $payment );
	}

	/**
	 * Complement payment.
	 *
	 * @param Payment $payment Payment.
	 */
	private static function complement_payment( Payment $payment ) {
		// Entrance Code.
		if ( null === $payment->entrance_code ) {
			$payment->entrance_code = uniqid();
		}

		// Key.
		if ( null === $payment->key ) {
			$payment->key = uniqid( 'pay_' );
		}

		// User ID.
		if ( null === $payment->user_id && is_user_logged_in() ) {
			$payment->user_id = get_current_user_id();
		}

		// Google Analytics client ID.
		if ( null === $payment->analytics_client_id ) {
			$payment->analytics_client_id = GoogleAnalyticsEcommerce::get_cookie_client_id();
		}

		// Complements.
		if ( null !== $payment->get_customer() ) {
			CustomerHelper::complement_customer( $payment->get_customer() );

			// Email.
			if ( null === $payment->get_email() ) {
				$payment->email = $payment->get_customer()->get_email();
			}
		}

		if ( null !== $payment->get_billing_address() ) {
			AddressHelper::complement_address( $payment->get_billing_address() );
		}

		if ( null !== $payment->get_shipping_address() ) {
			AddressHelper::complement_address( $payment->get_shipping_address() );
		}

		// Version.
		if ( null === $payment->get_version() ) {
			$payment->set_version( pronamic_pay_plugin()->get_version() );
		}

		// Mode.
		if ( null === $payment->get_mode() ) {
			$mode = get_post_meta( $payment->get_config_id(), '_pronamic_gateway_mode', true );

			$payment->set_mode( $mode );
		}

		// Issuer.
		if ( null === $payment->issuer ) {
			if ( PaymentMethods::CREDIT_CARD === $payment->method && filter_has_var( INPUT_POST, 'pronamic_credit_card_issuer_id' ) ) {
				$payment->issuer = filter_input( INPUT_POST, 'pronamic_credit_card_issuer_id', FILTER_SANITIZE_STRING );
			}

			if ( PaymentMethods::IDEAL === $payment->method && filter_has_var( INPUT_POST, 'pronamic_ideal_issuer_id' ) ) {
				$payment->issuer = filter_input( INPUT_POST, 'pronamic_ideal_issuer_id', FILTER_SANITIZE_STRING );
			}
		}
	}

	/**
	 * Start payment.
	 *
	 * @param Payment $payment The payment to start at the specified gateway.
	 * @param Gateway $gateway The gateway to start the payment at.
	 *
	 * @return Payment
	 */
	public static function start_payment( Payment $payment, $gateway = null ) {
		global $pronamic_ideal;

		self::complement_payment( $payment );

		$pronamic_ideal->payments_data_store->create( $payment );

		// Prevent payment start at gateway if amount is empty.
		$amount = $payment->get_total_amount()->get_value();

		if ( empty( $amount ) ) {
			$payment->set_status( Statuses::SUCCESS );

			$payment->save();

			return $payment;
		}

		// Gateway.
		if ( null === $gateway ) {
			$gateway = self::get_gateway( $payment->get_config_id() );
		}

		if ( ! $gateway ) {
			$payment->set_status( Statuses::FAILURE );

			$payment->save();

			return $payment;
		}

		// Start payment at the gateway.
		$result = $gateway->start( $payment );

		// Add gateway errors as payment notes.
		if ( $gateway->has_error() ) {
			foreach ( $gateway->error->get_error_codes() as $code ) {
				$payment->add_note( sprintf( '%s: %s', $code, $gateway->error->get_error_message( $code ) ) );
			}
		}

		// Set payment status.
		if ( false === $result ) {
			$payment->set_status( Statuses::FAILURE );
		}

		// Save payment.
		$payment->save();

		// Update subscription status for failed payments.
		if ( false === $result && $payment->get_subscription() ) {
			// Reload payment, so subscription is available.
			$payment = new Payment( $payment->get_id() );

			$subscription = $payment->get_subscription();

			if ( Recurring::FIRST === $payment->recurring_type ) {
				// First payment - cancel subscription to prevent unwanted recurring payments
				// in the future, when a valid customer ID might be set for the user.
				$subscription->set_status( Statuses::CANCELLED );
			} else {
				$subscription->set_status( Statuses::FAILURE );
			}

			$subscription->save();
		}

		// Schedule payment status check.
		if ( $gateway->supports( 'payment_status_request' ) ) {
			StatusChecker::schedule_event( $payment );
		}

		return $payment;
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
}
