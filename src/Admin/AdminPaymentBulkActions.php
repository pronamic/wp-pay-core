<?php
/**
 * Payment Bulk Actions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;
use WP_Query;

/**
 * WordPress admin payment bulk actions
 *
 * @link https://www.skyverge.com/blog/add-custom-bulk-action/
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 4.1.0
 */
class AdminPaymentBulkActions {
	/**
	 * Constructs and initializes an admin payment bulk actions object.
	 */
	public function __construct() {
		add_action( 'load-edit.php', [ $this, 'load' ] );
	}

	/**
	 * Load.
	 *
	 * @return void
	 */
	public function load() {
		// Current user.
		if ( ! current_user_can( 'edit_payments' ) ) {
			return;
		}

		// Screen.
		$screen = get_current_screen();

		if ( null === $screen ) {
			return;
		}

		if ( 'edit-pronamic_payment' !== $screen->id ) {
			return;
		}

		// Bulk actions.
		add_filter( 'bulk_actions-' . $screen->id, [ $this, 'bulk_actions' ] );

		add_filter( 'handle_bulk_actions-' . $screen->id, [ $this, 'handle_bulk_action' ], 10, 3 );

		// Admin notices.
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Custom bulk actions.
	 *
	 * @link https://make.wordpress.org/core/2016/10/04/custom-bulk-actions/
	 * @link https://github.com/WordPress/WordPress/blob/4.7/wp-admin/includes/class-wp-list-table.php#L440-L452
	 * @param array $bulk_actions Bulk actions.
	 * @return array
	 */
	public function bulk_actions( $bulk_actions ) {
		// Don't allow edit in bulk.
		unset( $bulk_actions['edit'] );

		// Bulk check payment status.
		$bulk_actions['pronamic_payment_check_status'] = __( 'Check Payment Status', 'pronamic_ideal' );

		return $bulk_actions;
	}

	/**
	 * Handle bulk action.
	 *
	 * @see hhttps://make.wordpress.org/core/2016/10/04/custom-bulk-actions/
	 * @link https://github.com/WordPress/WordPress/blob/4.7/wp-admin/edit.php#L166-L167
	 * @param string $sendback Sendback URL.
	 * @param string $doaction Action indicator.
	 * @param array  $post_ids  Post ID's to bulk edit.
	 * @return string
	 */
	public function handle_bulk_action( $sendback, $doaction, $post_ids ) {
		if ( 'pronamic_payment_check_status' !== $doaction ) {
			return $sendback;
		}

		$status_updated       = 0;
		$skipped_check        = 0;
		$unsupported_gateways = [];

		foreach ( $post_ids as $post_id ) {
			$payment = get_pronamic_payment( $post_id );

			if ( null === $payment ) {
				continue;
			}

			// Only check status for pending payments.
			if ( \Pronamic\WordPress\Pay\Payments\PaymentStatus::OPEN !== $payment->status && '' !== $payment->status ) {
				++$skipped_check;

				continue;
			}

			// Make sure gateway supports `payment_status_request` feature.
			$config_id = $payment->get_config_id();

			if ( null === $config_id ) {
				continue;
			}

			if ( ! \in_array( $config_id, $unsupported_gateways, true ) ) {
				$gateway = $payment->get_gateway();

				if ( null !== $gateway && ! $gateway->supports( 'payment_status_request' ) ) {
					$unsupported_gateways[] = $config_id;
				}
			}

			if ( \in_array( $config_id, $unsupported_gateways, true ) ) {
				continue;
			}

			Plugin::update_payment( $payment, false );

			++$status_updated;
		}

		$sendback = add_query_arg(
			[
				'status_updated'       => $status_updated,
				'skipped_check'        => $skipped_check,
				'unsupported_gateways' => implode( ',', $unsupported_gateways ),
				'_wpnonce'             => \wp_create_nonce( 'pronamic_pay_bulk_check_status' ),
			],
			$sendback
		);

		return $sendback;
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		if (
			! \array_key_exists( 'status_updated', $_GET )
				||
			! \array_key_exists( 'skipped_check', $_GET )
				||
			! \array_key_exists( 'unsupported_gateways', $_GET )
		) {
			return;
		}

		if ( ! \check_admin_referer( 'pronamic_pay_bulk_check_status' ) ) {
			return;
		}

		// Status updated.
		$updated = filter_input( INPUT_GET, 'status_updated', FILTER_VALIDATE_INT );

		if ( $updated > 0 ) {
			/* translators: %s: number updated payments */
			$message = sprintf( _n( '%s payment updated.', '%s payments updated.', $updated, 'pronamic_ideal' ), number_format_i18n( $updated ) );

			printf(
				'<div class="notice notice-success"><p>%s</p></div>',
				esc_html( $message )
			);
		}

		// Skipped.
		$skipped = filter_input( INPUT_GET, 'skipped_check', FILTER_VALIDATE_INT );

		if ( $skipped > 0 ) {
			$message = sprintf(
				/* translators: %s: number skipped payments */
				_n( '%s payment is not updated because it already has a final payment status.', '%s payments are not updated because they already have a final payment status.', $skipped, 'pronamic_ideal' ),
				number_format_i18n( $skipped )
			);

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html( $message )
			);
		}

		// Unsupported gateways.
		$gateways = \wp_parse_id_list( \sanitize_text_field( \wp_unslash( $_GET['unsupported_gateways'] ) ) );

		$gateways = array_filter( $gateways );
		$gateways = array_unique( $gateways );

		if ( ! empty( $gateways ) ) {
			$query = new WP_Query(
				[
					'post_type'              => 'pronamic_gateway',
					'post__in'               => $gateways,
					'nopaging'               => true,
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				]
			);

			$titles = wp_list_pluck( $query->posts, 'post_title' );

			$message = sprintf(
				/* translators: %s: gateways lists */
				__( 'Requesting the current payment status is unsupported by %s.', 'pronamic_ideal' ),
				implode( ', ', $titles )
			);

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	}
}
