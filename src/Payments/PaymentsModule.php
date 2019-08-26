<?php
/**
 * Payments Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Statuses;
use WP_Error;
use WP_REST_Request;

/**
 * Payments Module
 *
 * @link    https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link    https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   2.0.1
 */
class PaymentsModule {
	/**
	 * Plugin.
	 *
	 * @var Plugin $plugin
	 */
	public $plugin;

	/**
	 * Privacy.
	 *
	 * @var PaymentsPrivacy
	 */
	public $privacy;

	/**
	 * Status checker.
	 *
	 * @var StatusChecker
	 */
	public $status_checker;

	/**
	 * Construct and initialize a payments module object.
	 *
	 * @param Plugin $plugin The plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Payments privacy exporters and erasers.
		$this->privacy = new PaymentsPrivacy();

		// Exclude payment notes.
		add_filter( 'comments_clauses', array( $this, 'exclude_payment_comment_notes' ), 10, 2 );

		// Payment redirect URL.
		add_filter( 'pronamic_payment_redirect_url', array( $this, 'payment_redirect_url' ), 5, 2 );

		// Listen to payment status changes so we can log these in a note.
		add_action( 'pronamic_payment_status_update', array( $this, 'log_payment_status_update' ), 10, 4 );

		// REST API.
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

		// Payment Status Checker.
		$this->status_checker = new StatusChecker();
	}

	/**
	 * Comments clauses.
	 *
	 * @param array             $clauses Array with query clauses for the comments query.
	 * @param \WP_Comment_Query $query   A WordPress comment query object.
	 *
	 * @return array
	 */
	public function exclude_payment_comment_notes( $clauses, $query ) {
		$type = $query->query_vars['type'];

		// Ignore payment notes comments if it's not specifically requested.
		if ( 'payment_note' !== $type ) {
			$clauses['where'] .= " AND comment_type != 'payment_note'";
		}

		return $clauses;
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @param string  $url     A payment redirect URL.
	 * @param Payment $payment The payment to get a redirect URL for.
	 *
	 * @return string
	 */
	public function payment_redirect_url( $url, $payment ) {
		$page_id = null;

		switch ( $payment->status ) {
			case Statuses::CANCELLED:
				$page_id = pronamic_pay_get_page_id( 'cancel' );

				break;
			case Statuses::EXPIRED:
				$page_id = pronamic_pay_get_page_id( 'expired' );

				break;
			case Statuses::FAILURE:
				$page_id = pronamic_pay_get_page_id( 'error' );

				break;
			case Statuses::OPEN:
				$page_id = pronamic_pay_get_page_id( 'unknown' );

				break;
			case Statuses::SUCCESS:
				$page_id = pronamic_pay_get_page_id( 'completed' );

				break;
			default:
				$page_id = pronamic_pay_get_page_id( 'unknown' );

				break;
		}

		if ( ! empty( $page_id ) ) {
			$page_url = get_permalink( $page_id );

			if ( false !== $page_url ) {
				$url = $page_url;
			}
		}

		return $url;
	}

	/**
	 * Get payment status update note.
	 *
	 * @param string|null $old_status   Old meta status.
	 * @param string      $new_status   New meta status.
	 * @return string
	 */
	private function get_payment_status_update_note( $old_status, $new_status ) {
		$old_label = $this->plugin->payments_data_store->get_meta_status_label( $old_status );
		$new_label = $this->plugin->payments_data_store->get_meta_status_label( $new_status );

		if ( null === $old_status ) {
			return sprintf(
				/* translators: 1: new status */
				__( 'Payment created with status "%1$s".', 'pronamic_ideal' ),
				esc_html( empty( $new_label ) ? $new_status : $new_label )
			);
		}

		return sprintf(
			/* translators: 1: old status, 2: new status */
			__( 'Payment status changed from "%1$s" to "%2$s".', 'pronamic_ideal' ),
			esc_html( empty( $old_label ) ? $old_status : $old_label ),
			esc_html( empty( $new_label ) ? $new_status : $new_label )
		);
	}

	/**
	 * Payment status update.
	 *
	 * @param Payment     $payment      The status updated payment.
	 * @param bool        $can_redirect Whether or not redirects should be performed.
	 * @param string|null $old_status   Old meta status.
	 * @param string      $new_status   New meta status.
	 *
	 * @return void
	 */
	public function log_payment_status_update( $payment, $can_redirect, $old_status, $new_status ) {
		$note = $this->get_payment_status_update_note( $old_status, $new_status );

		$payment->add_note( $note );
	}

	/**
	 * REST API init.
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
	 *
	 * @return void
	 */
	public function rest_api_init() {
		register_rest_route(
			'pronamic-pay/v1',
			'/gateways/(?P<config_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_gateway' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'config_id'    => array(
						'description' => __( 'Gateway configuration ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					),
					'gateway_id'   => array(
						'description' => __( 'Gateway ID.', 'pronamic_ideal' ),
						'type'        => 'string',
					),
					'gateway_mode' => array(
						'description' => __( 'Gateway mode.', 'pronamic_ideal' ),
						'type'        => 'string',
					),
				),
			)
		);
	}

	/**
	 * REST API gateway.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_gateway( WP_REST_Request $request ) {
		$config_id    = $request->get_param( 'config_id' );
		$gateway_id   = $request->get_param( 'gateway_id' );
		$gateway_mode = $request->get_param( 'gateway_mode' );

		// Gateway.
		$args = array(
			'gateway_id'   => $gateway_id,
			'gateway_mode' => $gateway_mode,
		);

		$gateway = Plugin::get_gateway( $config_id, $args );

		if ( empty( $gateway ) ) {
			return new WP_Error(
				'pronamic-pay-gateway-not-found',
				sprintf(
					/* translators: %s: Gateway configuration ID */
					__( 'Could not found gateway with ID `%s`.', 'pronamic_ideal' ),
					$config_id
				),
				$config_id
			);
		}

		// Settings.
		ob_start();

		require __DIR__ . '/../../views/meta-box-gateway-settings.php';

		$meta_box_settings = ob_get_clean();

		// Object.
		return (object) array(
			'config_id'    => $config_id,
			'gateway_id'   => $gateway_id,
			'gateway_mode' => $gateway_mode,
			'meta_boxes'   => (object) array(
				'settings' => $meta_box_settings,
			),
		);
	}
}
