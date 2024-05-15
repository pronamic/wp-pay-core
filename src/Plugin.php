<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Http\Facades\Http;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Admin\AdminModule;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethod;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\PaymentMethodsCollection;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Gateways\GatewaysDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Payments\PaymentsDataStoreCPT;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\StatusChecker;
use Pronamic\WordPress\Pay\Refunds\Refund;
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
	public static function instance( $args = [] ) {
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
	 * Pronamic service URL.
	 *
	 * @var string|null
	 */
	private static $pronamic_service_url;

	/**
	 * Payment methods.
	 *
	 * @var PaymentMethodsCollection
	 */
	private $payment_methods;

	/**
	 * Construct and initialize an Pronamic Pay plugin object.
	 *
	 * @param string|array|object $args The plugin arguments.
	 */
	public function __construct( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'file'    => null,
				'options' => [],
			]
		);

		// Version from plugin file header.
		if ( null !== $args['file'] ) {
			$file_data = get_file_data( $args['file'], [ 'Version' => 'Version' ] );

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
		$this->integrations = [];

		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 0 );

		// Register styles.
		add_action( 'init', [ $this, 'register_styles' ], 9 );

		// If WordPress is loaded check on returns and maybe redirect requests.
		add_action( 'wp_loaded', [ $this, 'handle_returns' ], 10 );
		add_action( 'wp_loaded', [ $this, 'maybe_redirect' ], 10 );

		// Default date time format.
		add_filter( 'pronamic_datetime_default_format', [ $this, 'datetime_format' ], 10, 1 );

		/**
		 * Pronamic service URL.
		 */
		if ( \array_key_exists( 'pronamic_service_url', $args ) ) {
			self::$pronamic_service_url = $args['pronamic_service_url'];
		}

		/**
		 * Action scheduler.
		 *
		 * @link https://actionscheduler.org/
		 */
		if ( ! \array_key_exists( 'action_scheduler', $args ) ) {
			$args['action_scheduler'] = self::$dirname . '/wp-content/plugins/action-scheduler/action-scheduler.php';
		}

		require_once $args['action_scheduler'];

		/**
		 * Get Buy Now, Pay Later disclaimer.
		 * 
		 * @link https://github.com/pronamic/pronamic-pay/issues/70
		 * @param string $provider Provider.
		 * @return string
		 */
		/* translators: %s: provider */
		$bnpl_disclaimer_template = \__( 'You must be at least 18+ to use this service. If you pay on time, you will avoid additional costs and ensure that you can use %s services again in the future. By continuing, you accept the Terms and Conditions and confirm that you have read the Privacy Statement and Cookie Statement.', 'pronamic_ideal' );

		/**
		 * Payment methods.
		 */
		$this->payment_methods = new PaymentMethodsCollection();

		// AfterPay.nl.
		$payment_method_afterpay_nl = new PaymentMethod( PaymentMethods::AFTERPAY_NL );

		$payment_method_afterpay_nl->descriptions = [           
			/**
			 * AfterPay method description.
			 *
			 * @link https://www.afterpay.nl/en/customers/where-can-i-pay-with-afterpay
			 */
			'default' => \__( 'AfterPay is one of the largest and most popular post-payment system in the Benelux. Millions of Dutch and Belgians use AfterPay to pay for products.', 'pronamic_ideal' ),
		];

		$payment_method_afterpay_nl->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/afterpay-nl/method-afterpay-nl-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_afterpay_nl );

		// AfterPay.com.
		$payment_method_afterpay_com = new PaymentMethod( PaymentMethods::AFTERPAY_COM );

		$payment_method_afterpay_com->descriptions = [
			/**
			 * Afterpay method description.
			 *
			 * @link https://en.wikipedia.org/wiki/Afterpay
			 * @link https://docs.adyen.com/payment-methods/afterpaytouch
			 */
			'default' => \__( 'Afterpay is a popular buy now, pay later service in Australia, New Zealand, the United States, and Canada.', 'pronamic_ideal' ),
		];

		$payment_method_afterpay_com->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/afterpay-com/method-afterpay-com-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_afterpay_com );

		// Alipay.
		$payment_method_alipay = new PaymentMethod( PaymentMethods::ALIPAY );

		$payment_method_alipay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/alipay/method-alipay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_alipay );

		// American Express.
		$payment_method_american_express = new PaymentMethod( PaymentMethods::AMERICAN_EXPRESS );

		$payment_method_american_express->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/american-express/method-american-express-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_american_express );

		// Apple Pay.
		$payment_method_apple_pay = new PaymentMethod( PaymentMethods::APPLE_PAY );

		$payment_method_apple_pay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/apple-pay/method-apple-pay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_apple_pay );

		// Bancontact.
		$payment_method_bancontact = new PaymentMethod( PaymentMethods::BANCONTACT );

		$payment_method_bancontact->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/bancontact/method-bancontact-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_bancontact );

		// Bank Transfer.
		$payment_method_bank_transfer = new PaymentMethod( PaymentMethods::BANK_TRANSFER );

		$payment_method_bank_transfer->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/bank-transfer/method-bank-transfer-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_bank_transfer );

		// Belfius Direct Net.
		$payment_method_belfius = new PaymentMethod( PaymentMethods::BELFIUS );

		$payment_method_belfius->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/belfius/method-belfius-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_belfius );

		// Billie.
		$payment_method_billie = new PaymentMethod( PaymentMethods::BILLIE );

		$payment_method_billie->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/billie/method-billie-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_billie );

		// Billink.
		$payment_method_billink = new PaymentMethod( PaymentMethods::BILLINK );

		$payment_method_billie->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/billink/method-billink-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_billink );

		// Bitcoin.
		$payment_method_bitcoin = new PaymentMethod( PaymentMethods::BITCOIN );

		$payment_method_bitcoin->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/bitcoin/method-bitcoin-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_bitcoin );

		// BLIK.
		$payment_method_blik = new PaymentMethod( PaymentMethods::BLIK );

		$payment_method_blik->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/blik/method-blik-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_blik );

		// Bunq.
		$payment_method_bunq = new PaymentMethod( PaymentMethods::BUNQ );

		$payment_method_bunq->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/bunq/method-bunq-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_bunq );

		// In3.
		$payment_method_in3 = new PaymentMethod( PaymentMethods::IN3 );

		$payment_method_in3->descriptions = [
			'customer' => \sprintf( $bnpl_disclaimer_template, $payment_method_in3->name ),
		];

		$payment_method_in3->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/in3/method-in3-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_in3 );

		// Capayable.
		$payment_method_capayable = new PaymentMethod( PaymentMethods::CAPAYABLE );

		$payment_method_capayable->images = [];

		$this->payment_methods->add( $payment_method_capayable );

		// Card.
		$payment_method_card = new PaymentMethod( PaymentMethods::CARD );

		$payment_method_card->descriptions = [
			'default' => \__( 'The most popular payment method in the world. Offers customers a safe and trusted way to pay online. Customers can pay for their order quickly and easily with their card, without having to worry about their security. It is possible to charge a payment surcharge for card costs.', 'pronamic_ideal' ),
		];

		$payment_method_card->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/credit-card/method-credit-card-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_card );

		// Credit card.
		$payment_method_credit_card = new PaymentMethod( PaymentMethods::CREDIT_CARD );

		$payment_method_credit_card->descriptions = [
			'default' => \__( 'The most popular payment method in the world. Offers customers a safe and trusted way to pay online. Customers can pay for their order quickly and easily with their credit card, without having to worry about their security. It is possible to charge a payment surcharge for credit card costs.', 'pronamic_ideal' ),
		];

		$payment_method_credit_card->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/credit-card/method-credit-card-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_credit_card );

		// Direct debit.
		$payment_method_direct_debit = new PaymentMethod( PaymentMethods::DIRECT_DEBIT );

		$payment_method_direct_debit->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/direct-debit/method-direct-debit-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_direct_debit );

		/* translators: %s: payment method */
		$description_template = \__( 'By using this payment method you authorize us via %s to debit payments from your bank account.', 'pronamic_ideal' );

		// Direct debit (mandate via Bancontact).
		$payment_method_direct_debit_bancontact = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_BANCONTACT );

		$payment_method_direct_debit_bancontact->descriptions = [
			'customer' => \sprintf( $description_template, $payment_method_direct_debit_bancontact->name ),
		];

		$payment_method_direct_debit_bancontact->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/direct-debit-bancontact/method-direct-debit-bancontact-wc-107x32.svg',
		];

		$this->payment_methods->add( $payment_method_direct_debit_bancontact );

		// Direct debit (mandate via Bancontact).
		$payment_method_direct_debit_ideal = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_IDEAL );

		$payment_method_direct_debit_ideal->descriptions = [
			'customer' => \sprintf( $description_template, $payment_method_direct_debit_ideal->name ),
		];

		$payment_method_direct_debit_ideal->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/direct-debit-ideal/method-direct-debit-ideal-wc-107x32.svg',
		];

		$this->payment_methods->add( $payment_method_direct_debit_ideal );

		// Direct debit (mandate via SOFORT).
		$payment_method_direct_debit_sofort = new PaymentMethod( PaymentMethods::DIRECT_DEBIT_SOFORT );

		$payment_method_direct_debit_sofort->descriptions = [
			'customer' => \sprintf( $description_template, $payment_method_direct_debit_sofort->name ),
		];

		$payment_method_direct_debit_sofort->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/direct-debit-sofort/method-direct-debit-sofort-wc-107x32.svg',
		];

		$this->payment_methods->add( $payment_method_direct_debit_sofort );

		// EPS.
		$payment_method_eps = new PaymentMethod( PaymentMethods::EPS );

		$payment_method_eps->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/eps/method-eps-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_eps );

		// Focum.
		$payment_method_focum = new PaymentMethod( PaymentMethods::FOCUM );

		$payment_method_eps->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/focum/method-focum-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_focum );

		// IDEAL.
		$payment_method_ideal = new PaymentMethod( PaymentMethods::IDEAL );

		$payment_method_ideal->descriptions = [
			'customer' => \__( 'With iDEAL you can easily pay online in the secure environment of your own bank.', 'pronamic_ideal' ),
		];

		$payment_method_ideal->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/ideal/method-ideal-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_ideal );

		// IDEAL QR.
		$payment_method_ideal_qr = new PaymentMethod( PaymentMethods::IDEALQR );

		$payment_method_ideal_qr->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/ideal-qr/method-ideal-qr-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_ideal_qr );

		// Giropay.
		$payment_method_giropay = new PaymentMethod( PaymentMethods::GIROPAY );

		$payment_method_giropay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/giropay/method-giropay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_giropay );

		// Google Pay.
		$payment_method_google_pay = new PaymentMethod( PaymentMethods::GOOGLE_PAY );

		$payment_method_google_pay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/google-pay/method-google-pay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_google_pay );

		// KBC/CBC Payment Button.
		$payment_method_kbc = new PaymentMethod( PaymentMethods::KBC );

		$payment_method_kbc->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/kbc/method-kbc-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_kbc );

		// Klarna Pay Later.
		$payment_method_klarna_pay_later = new PaymentMethod( PaymentMethods::KLARNA_PAY_LATER );

		$payment_method_klarna_pay_later->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/klarna-pay-later/method-klarna-pay-later-wc-51x32.svg',
		];

		$payment_method_klarna_pay_later->descriptions = [
			'customer' => \sprintf( $bnpl_disclaimer_template, $payment_method_klarna_pay_later->name ),
		];

		$this->payment_methods->add( $payment_method_klarna_pay_later );

		// Klarna Pay Now.
		$payment_method_klarna_pay_now = new PaymentMethod( PaymentMethods::KLARNA_PAY_NOW );

		$payment_method_klarna_pay_now->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/klarna-pay-now/method-klarna-pay-now-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_klarna_pay_now );

		// Klarna Pay Over Time.
		$payment_method_klarna_pay_over_time = new PaymentMethod( PaymentMethods::KLARNA_PAY_OVER_TIME );

		$payment_method_klarna_pay_over_time->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/klarna-pay-over-time/method-klarna-pay-over-time-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_klarna_pay_over_time );

		// Maestro.
		$payment_method_maestro = new PaymentMethod( PaymentMethods::MAESTRO );

		$payment_method_maestro->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/maestro/method-maestro-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_maestro );

		// Mastercard.
		$payment_method_mastercard = new PaymentMethod( PaymentMethods::MASTERCARD );

		$payment_method_mastercard->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/mastercard/method-mastercard-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_mastercard );

		// MB WAY.
		$payment_method_mb_way = new PaymentMethod( PaymentMethods::MB_WAY );

		$payment_method_mb_way->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/mb-way/method-mb-way-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_mb_way );

		// Payconiq.
		$payment_method_payconiq = new PaymentMethod( PaymentMethods::PAYCONIQ );

		$payment_method_payconiq->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/payconiq/method-payconiq-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_payconiq );

		// PayPal.
		$payment_method_paypal = new PaymentMethod( PaymentMethods::PAYPAL );

		$payment_method_paypal->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/paypal/method-paypal-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_paypal );

		// Przelewy24.
		$payment_method_przelewy24 = new PaymentMethod( PaymentMethods::PRZELEWY24 );

		$payment_method_przelewy24->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/przelewy24/method-przelewy24-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_przelewy24 );

		// Riverty.
		$payment_method_riverty = new PaymentMethod( PaymentMethods::RIVERTY );

		$payment_method_riverty->descriptions = [
			'default'  => \__( 'Riverty (formerly AfterPay) is a payment service that allows customers to pay after receiving the product.', 'pronamic_ideal' ),
			'customer' => \sprintf( $bnpl_disclaimer_template, $payment_method_riverty->name ),
		];

		$payment_method_riverty->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/riverty/method-riverty-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_riverty );

		// Santander.
		$payment_method_santander = new PaymentMethod( PaymentMethods::SANTANDER );

		$payment_method_santander->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/santander/method-santander-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_santander );

		// SOFORT Banking.
		$payment_method_sofort = new PaymentMethod( PaymentMethods::SOFORT );

		$payment_method_sofort->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/sofort/method-sofort-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_sofort );

		// SprayPay.
		$payment_method_spraypay = new PaymentMethod( PaymentMethods::SPRAYPAY );

		$payment_method_spraypay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/spraypay/method-spraypay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_spraypay );

		// Swish.
		$payment_method_swish = new PaymentMethod( PaymentMethods::SWISH );

		$payment_method_swish->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/swish/method-swish-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_swish );

		// TWINT.
		$payment_method_twint = new PaymentMethod( PaymentMethods::TWINT );

		$payment_method_twint->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/twint/method-twint-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_twint );

		// V PAY.
		$payment_method_v_pay = new PaymentMethod( PaymentMethods::V_PAY );

		$payment_method_v_pay->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/v-pay/method-v-pay-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_v_pay );

		// Vipps.
		$payment_method_vipps = new PaymentMethod( PaymentMethods::VIPPS );

		$payment_method_vipps->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/vipps/method-vipps-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_vipps );

		// Visa.
		$payment_method_visa = new PaymentMethod( PaymentMethods::VISA );

		$payment_method_visa->images = [
			'woocommerce' => __DIR__ . '/../images/dist/methods/visa/method-visa-wc-51x32.svg',
		];

		$this->payment_methods->add( $payment_method_visa );
	}

	/**
	 * Get payment methods.
	 *
	 * @param array $args Query arguments.
	 * @return PaymentMethodsCollection
	 */
	public function get_payment_methods( $args = [] ) {
		return $this->payment_methods->query( $args );
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
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if (
			! \array_key_exists( 'payment', $_GET )
				||
			! \array_key_exists( 'key', $_GET )
		) {
			return;
		}

		$payment_id = (int) $_GET['payment'];

		$payment = get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check if payment key is valid.
		$key = \sanitize_text_field( \wp_unslash( $_GET['key'] ) );

		if ( $key !== $payment->key ) {
			wp_safe_redirect( home_url() );

			exit;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended

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
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! \array_key_exists( 'payment_redirect', $_GET ) || ! \array_key_exists( 'key', $_GET ) ) {
			return;
		}

		// Get payment.
		$payment_id = (int) $_GET['payment_redirect'];

		$payment = get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Validate key.
		$key = \sanitize_text_field( \wp_unslash( $_GET['key'] ) );

		if ( $key !== $payment->key || empty( $payment->key ) ) {
			return;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		Core_Util::no_cache();

		$gateway = $payment->get_gateway();

		if ( null !== $gateway ) {
			// Give gateway a chance to handle redirect.
			$gateway->payment_redirect( $payment );

			// Handle HTML form redirect.
			if ( $gateway->is_html_form() ) {
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
	 * @return void
	 */
	public function plugins_loaded() {
		// Settings.
		$this->settings = new Settings( $this );

		// Data Stores.
		$this->gateways_data_store      = new GatewaysDataStoreCPT();
		$this->payments_data_store      = new PaymentsDataStoreCPT();
		$this->subscriptions_data_store = new SubscriptionsDataStoreCPT();

		// Post Types.
		$this->gateway_post_type      = new GatewayPostType();
		$this->payment_post_type      = new PaymentPostType();
		$this->subscription_post_type = new SubscriptionPostType();

		// Privacy Manager.
		$this->privacy_manager = new PrivacyManager();

		// Webhook Logger.
		$this->webhook_logger = new WebhookLogger();
		$this->webhook_logger->setup();

		// Modules.
		$this->payments_module      = new Payments\PaymentsModule( $this );
		$this->subscriptions_module = new Subscriptions\SubscriptionsModule( $this );
		$this->tracking_module      = new TrackingModule();

		// Blocks module.
		if ( function_exists( 'register_block_type' ) ) {
			$this->blocks_module = new Blocks\BlocksModule();
			$this->blocks_module->setup();
		}

		// Admin.
		if ( is_admin() ) {
			$this->admin = new Admin\AdminModule( $this );
		}

		new Admin\Install( $this ); 

		$controllers = [
			new PagesController(),
			new HomeUrlController(),
			new ActionSchedulerController(),
		];

		foreach ( $controllers as $controller ) {
			$controller->setup();
		}

		$gateways = [];

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

		$plugin_integrations = [];

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
		\add_filter( 'pronamic_payment_redirect_url', [ $this, 'payment_redirect_url' ], 10, 2 );

		// Actions.
		\add_action( 'pronamic_pay_pre_create_payment', [ __CLASS__, 'complement_payment' ], 10, 1 );
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
			\plugins_url( 'css/redirect' . $min . '.css', __DIR__ ),
			[],
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
		$args = [
			'post_type' => 'pronamic_gateway',
			'orderby'   => 'post_title',
			'order'     => 'ASC',
			'nopaging'  => true,
		];

		if ( null !== $payment_method ) {
			$config_ids = PaymentMethods::get_config_ids( $payment_method );

			$args['post__in'] = empty( $config_ids ) ? [ 0 ] : $config_ids;
		}

		$query = new WP_Query( $args );

		$options = [ __( '— Select Configuration —', 'pronamic_ideal' ) ];

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
	 * Render exception.
	 *
	 * @param \Exception $exception An exception.
	 * @return void
	 */
	public static function render_exception( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is used in include.
		\Exception $exception
	) {
		include __DIR__ . '/../views/exception.php';
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
	public static function get_gateway( $config_id, $args = [] ) {
		// Get gateway from data store.
		$gateway = \pronamic_pay_plugin()->gateways_data_store->get_gateway( $config_id );

		// Use gateway identifier from arguments to get new gateway.
		if ( null === $gateway && ! empty( $args ) ) {
			// Get integration.
			$args = wp_parse_args(
				$args,
				[
					'gateway_id' => \get_post_meta( $config_id, '_pronamic_gateway_id', true ),
				]
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
				$referer_host = \wp_parse_url( $referer, \PHP_URL_HOST );

				if ( null === $referer_host ) {
					$referer = \home_url( $referer );
				}

				$post_id = \url_to_postid( $referer );

				if ( $post_id > 0 ) {
					$origin_id = $post_id;
				}
			}

			// Set origin ID.
			$payment->set_origin_id( $origin_id );
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

		// Post data.
		self::process_payment_post_data( $payment );

		// Gender.
		if ( null !== $customer->get_gender() ) {
			$payment->delete_meta( 'gender' );
		}

		// Date of birth.
		if ( null !== $customer->get_birth_date() ) {
			$payment->delete_meta( 'birth_date' );
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
		$consumer_bank_details_name = $payment->get_meta( 'consumer_bank_details_name' );
		$consumer_bank_details_iban = $payment->get_meta( 'consumer_bank_details_iban' );

		if ( null !== $consumer_bank_details_name || null !== $consumer_bank_details_iban ) {
			$consumer_bank_details = $payment->get_consumer_bank_details();

			if ( null === $consumer_bank_details ) {
				$consumer_bank_details = new BankAccountDetails();
			}

			if ( null === $consumer_bank_details->get_name() ) {
				$consumer_bank_details->set_name( $consumer_bank_details_name );
			}

			if ( null === $consumer_bank_details->get_iban() ) {
				$consumer_bank_details->set_iban( $consumer_bank_details_iban );
			}

			$payment->set_consumer_bank_details( $consumer_bank_details );
		}

		// Payment lines payment.
		$lines = $payment->get_lines();

		if ( null !== $lines ) {
			foreach ( $lines as $line ) {
				$line->set_payment( $payment );
			}
		}
	}

	/**
	 * Process payment input data.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	private static function process_payment_post_data( Payment $payment ) {
		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			return;
		}

		$payment_method = $payment->get_payment_method();

		if ( null === $payment_method ) {
			return;
		}

		$payment_method = $gateway->get_payment_method( $payment_method );

		if ( null === $payment_method ) {
			return;
		}

		foreach ( $payment_method->get_fields() as $field ) {
			$id = $field->get_id();

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( \array_key_exists( $id, $_POST ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$value = \sanitize_text_field( \wp_unslash( $_POST[ $id ] ) );

				if ( '' !== $field->meta_key ) {
					$payment->set_meta( $field->meta_key, $value );
				}
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

		/**
		 * Merge tags.
		 * 
		 * @link https://github.com/pronamic/wp-pronamic-pay/issues/358
		 * @link https://github.com/pronamic/wp-pronamic-pay-woocommerce/issues/43
		 */
		$payment->set_description( $payment->format_string( (string) $payment->get_description() ) );

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
			$payment->add_note(
				\sprintf(
					/* translators: %d: Gateway configuration ID */
					\__( 'Payment failed because gateway configuration with ID `%d` does not exist.', 'pronamic_ideal' ),
					$config_id
				)
			);

			$payment->set_status( PaymentStatus::FAILURE );

			$payment->save();

			return $payment;
		}

		// Mode.
		$payment->set_mode( $gateway->get_mode() );

		// Subscriptions.
		$subscriptions = $payment->get_subscriptions();

		// Start payment at the gateway.
		try {
			self::pronamic_service( $payment );

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
	 * The Pronamic Pay service forms an abstraction layer for the various supported
	 * WordPress plugins and Payment Service Providers (PSP. Optionally, a risk analysis
	 * can be performed before payment.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	private static function pronamic_service( Payment $payment ) {
		if ( null === self::$pronamic_service_url ) {
			return;
		}

		try {
			$body = [
				'license' => \get_option( 'pronamic_pay_license_key' ),
				'payment' => \wp_json_encode( $payment->get_json() ),
			];

			$map = [
				'query'  => 'GET',
				'body'   => 'POST',
				'server' => 'SERVER',
			];

			foreach ( $map as $parameter => $key ) {
				$name = '_' . $key;

				$body[ $parameter ] = $GLOBALS[ $name ];
			}

			$response = Http::post(
				self::$pronamic_service_url,
				[
					'body' => $body,
				]
			);

			$data = $response->json();

			if ( ! \is_object( $data ) ) {
				return;
			}

			if ( \property_exists( $data, 'id' ) ) {
				$payment->set_meta( 'pronamic_pay_service_id', $data->id );
			}

			if ( \property_exists( $data, 'risk_score' ) ) {
				$payment->set_meta( 'pronamic_pay_risk_score', $data->risk_score );
			}
		} catch ( \Exception $e ) {
			return;
		}
	}

	/**
	 * Create refund.
	 *
	 * @param Refund $refund Refund.
	 * @return void
	 * @throws \Exception Throws exception on error.
	 */
	public static function create_refund( Refund $refund ) {
		$payment = $refund->get_payment();

		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			throw new \Exception(
				\esc_html__( 'Unable to process refund as gateway could not be found.', 'pronamic_ideal' )
			);
		}

		try {
			$gateway->create_refund( $refund );

			$payment->refunds[] = $refund;

			$refunded_amount = $payment->get_refunded_amount();

			$refunded_amount = $refunded_amount->add( $refund->get_amount() );

			$payment->set_refunded_amount( $refunded_amount );
		} catch ( \Exception $exception ) {
			$payment->add_note( $exception->getMessage() );

			throw $exception;
		} finally {
			$payment->save();
		}
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
		 * @param string  $url     Redirect URL.
		 * @param Payment $payment Payment.
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

		if ( defined( 'PRONAMIC_PAY_DEBUG' ) && PRONAMIC_PAY_DEBUG ) {
			$value = true;
		}

		return (bool) $value;
	}
}
