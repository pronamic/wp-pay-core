<?php
/**
 * Payments Module
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Plugin;
use WP_CLI;

/**
 * Payments Module
 *
 * @link    https://woocommerce.com/2017/04/woocommerce-3-0-release/
 * @link    https://woocommerce.wordpress.com/2016/10/27/the-new-crud-classes-in-woocommerce-2-7/
 * @author  Remco Tolsma
 * @version 2.5.0
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
		add_filter( 'comments_clauses', [ $this, 'exclude_payment_comment_notes' ], 10, 2 );

		// Payment redirect URL.
		add_filter( 'pronamic_payment_redirect_url', [ $this, 'payment_redirect_url' ], 5, 2 );

		// Listen to payment status changes so we can log these in a note.
		add_action( 'pronamic_payment_status_update', [ $this, 'log_payment_status_update' ], 10, 4 );

		// REST API.
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );

		// Payment Status Checker.
		$this->status_checker = new StatusChecker();

		// CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command(
				'pay payment status',
				function ( $args, $assoc_args ) {
					foreach ( $args as $id ) {
						$payment = get_pronamic_payment( $id );

						if ( null === $payment ) {
							WP_CLI::error(
								\sprintf(
									'Cannot find payment based on ID %s.',
									$id
								)
							);

							exit( 1 );
						}

						WP_CLI::log(
							\sprintf(
								'Check the status (current: %s) of payment with ID %s…',
								$payment->get_status(),
								$id
							)
						);

						Plugin::update_payment( $payment, false );

						WP_CLI::log(
							\sprintf(
								'Checked the status (current: %s) of payment with ID %s.',
								$payment->get_status(),
								$id
							)
						);
					}
				}
			);
		}
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
			case PaymentStatus::CANCELLED:
				$page_id = pronamic_pay_get_page_id( 'cancel' );

				break;
			case PaymentStatus::EXPIRED:
				$page_id = pronamic_pay_get_page_id( 'expired' );

				break;
			case PaymentStatus::FAILURE:
				$page_id = pronamic_pay_get_page_id( 'error' );

				break;
			case PaymentStatus::OPEN:
				$page_id = pronamic_pay_get_page_id( 'unknown' );

				break;
			case PaymentStatus::SUCCESS:
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
		\register_rest_route(
			'pronamic-pay/v1',
			'/payments/(?P<payment_id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_api_payment' ],
				'permission_callback' => function() {
					return \current_user_can( 'edit_payments' );
				},
				'args'                => [
					'payment_id' => [
						'description' => __( 'Payment ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
				],
			]
		);
	}

	/**
	 * REST API payment.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_payment( \WP_REST_Request $request ) {
		$payment_id = $request->get_param( 'payment_id' );

		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return new \WP_Error(
				'pronamic-pay-payment-not-found',
				\sprintf(
					/* translators: %s: payment ID */
					\__( 'Could not find payment with ID `%s`.', 'pronamic_ideal' ),
					$payment_id
				),
				$payment_id
			);
		}

		return $payment->get_json();
	}
}
