<?php
/**
 * Payments privacy exporters and erasers.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Payments Privacy class.
 *
 * @author  Reüel van der Steege
 * @version 5.2.0
 * @since   5.2.0
 * @package Pronamic\WordPress\Pay\Payments
 */
class PaymentsPrivacy {
	/**
	 * Payments privacy constructor.
	 */
	public function __construct() {
		// Register exporters.
		add_action( 'pronamic_pay_privacy_register_exporters', array( $this, 'register_exporters' ) );

		// Register erasers.
		add_action( 'pronamic_pay_privacy_register_erasers', array( $this, 'register_erasers' ) );
	}

	/**
	 * Register privacy exporters.
	 *
	 * @param \Pronamic\WordPress\Pay\PrivacyManager $privacy_manager Privacy manager.
	 *
	 * @return void
	 */
	public function register_exporters( $privacy_manager ) {
		// Payments export.
		$privacy_manager->add_exporter(
			'payments',
			__( 'Payments', 'pronamic_ideal' ),
			array( $this, 'payments_export' )
		);
	}

	/**
	 * Register privacy erasers.
	 *
	 * @param \Pronamic\WordPress\Pay\PrivacyManager $privacy_manager Privacy manager.
	 *
	 * @return void
	 */
	public function register_erasers( $privacy_manager ) {
		// Payments anonymizer.
		$privacy_manager->add_eraser(
			'payments',
			__( 'Payments', 'pronamic_ideal' ),
			array( $this, 'payments_anonymizer' )
		);
	}

	/**
	 * Payments exporter.
	 *
	 * @param string $email_address Email address.
	 * @param int    $page          Page.
	 *
	 * @return array
	 */
	public function payments_export( $email_address, $page = 1 ) {
		// Payments data store.
		$data_store = pronamic_pay_plugin()->payments_data_store;

		// Privacy manager.
		$privacy_manager = pronamic_pay_plugin()->privacy_manager;

		// Get payments.
		// @todo use paging.
		$payments = get_pronamic_payments_by_meta(
			$data_store->meta_key_prefix . 'email',
			$email_address
		);

		// Get registered meta keys for export.
		$meta_keys = wp_list_filter(
			$data_store->get_registered_meta(),
			array(
				'privacy_export' => true,
			)
		);

		$items = array();

		// Loop payments.
		foreach ( $payments as $payment ) {
			$export_data = array();

			$payment_meta = get_post_meta( $payment->get_id() );

			// Get payment meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $payment_meta ) ) {
					continue;
				}

				// Add export value.
				$export_data[] = $privacy_manager->export_meta( $meta_key, $meta_options, $payment_meta );
			}

			// Add item to export data.
			if ( ! empty( $export_data ) ) {
				$items[] = array(
					'group_id'    => 'pronamic-pay-payments',
					'group_label' => __( 'Payments', 'pronamic_ideal' ),
					'item_id'     => 'pronamic-pay-payment-' . $payment->get_id(),
					'data'        => $export_data,
				);
			}
		}

		$done = true;

		// Return export data.
		return array(
			'data' => $items,
			'done' => $done,
		);
	}

	/**
	 * Payments anonymizer.
	 *
	 * @param string $email_address Email address.
	 * @param int    $page          Page.
	 *
	 * @return array
	 */
	public function payments_anonymizer( $email_address, $page = 1 ) {
		// Payments data store.
		$data_store = pronamic_pay_plugin()->payments_data_store;

		// Privacy manager.
		$privacy_manager = pronamic_pay_plugin()->privacy_manager;

		// Return values.
		$items_removed  = false;
		$items_retained = false;
		$messages       = array();
		$done           = false;

		// Get payments.
		// @todo use paging.
		$payments = get_pronamic_payments_by_meta(
			$data_store->meta_key_prefix . 'email',
			$email_address
		);

		// Get registered meta keys for erasure.
		$meta_keys = wp_list_filter(
			$data_store->get_registered_meta(),
			array(
				'privacy_erasure' => null,
			),
			'NOT'
		);

		// Loop payments.
		foreach ( $payments as $payment ) {
			$payment_id = $payment->get_id();

			$payment_meta = get_post_meta( $payment_id );

			// Get payment meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $payment_meta ) ) {
					continue;
				}

				$privacy_manager->erase_meta( $payment_id, $meta_key, $meta_options['privacy_erasure'] );
			}

			// Add payment note.
			$payment->add_note( __( 'Payment anonymized for personal data erasure request.', 'pronamic_ideal' ) );

			// Add message.
			$messages[] = sprintf( __( 'Payment ID %s anonymized.', 'pronamic_ideal' ), $payment_id );

			$items_removed = true;
		}

		$done = true;

		// Return results.
		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		);
	}

}
