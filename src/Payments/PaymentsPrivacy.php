<?php
/**
 * Payments privacy exporters and erasers.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

use Pronamic\WordPress\Pay\AddressHelper;
use Pronamic\WordPress\Pay\CustomerHelper;

/**
 * Payments Privacy class.
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.0.2
 */
class PaymentsPrivacy {
	/**
	 * Payments privacy constructor.
	 */
	public function __construct() {
		// Register exporters.
		add_action( 'pronamic_pay_privacy_register_exporters', [ $this, 'register_exporters' ] );

		// Register erasers.
		add_action( 'pronamic_pay_privacy_register_erasers', [ $this, 'register_erasers' ] );
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
			[ $this, 'payments_export' ]
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
			[ $this, 'payments_anonymizer' ]
		);
	}

	/**
	 * Payments exporter.
	 *
	 * @param string $email_address Email address.
	 * @return array
	 */
	public function payments_export( $email_address ) {
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
			[
				'privacy_export' => true,
			]
		);

		$items = [];

		// Loop payments.
		foreach ( $payments as $payment ) {
			$export_data = [];

			$id = $payment->get_id();

			if ( empty( $id ) ) {
				continue;
			}

			$payment_meta = get_post_meta( $id );

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
				$items[] = [
					'group_id'    => 'pronamic-pay-payments',
					'group_label' => __( 'Payments', 'pronamic_ideal' ),
					'item_id'     => 'pronamic-pay-payment-' . $id,
					'data'        => $export_data,
				];
			}
		}

		// Return export data.
		return [
			'data' => $items,
			'done' => true,
		];
	}

	/**
	 * Payments anonymizer.
	 *
	 * @param string $email_address Email address.
	 * @return array
	 */
	public function payments_anonymizer( $email_address ) {
		// Payments data store.
		$data_store = pronamic_pay_plugin()->payments_data_store;

		// Privacy manager.
		$privacy_manager = pronamic_pay_plugin()->privacy_manager;

		// Return values.
		$items_removed  = false;
		$items_retained = false;
		$messages       = [];

		// Get payments.
		// @todo use paging.
		$payments = get_pronamic_payments_by_meta(
			$data_store->meta_key_prefix . 'email',
			$email_address
		);

		// Get registered meta keys for erasure.
		$meta_keys = wp_list_filter(
			$data_store->get_registered_meta(),
			[
				'privacy_erasure' => null,
			],
			'NOT'
		);

		// Loop payments.
		foreach ( $payments as $payment ) {
			$payment_id = $payment->get_id();

			if ( empty( $payment_id ) ) {
				continue;
			}

			$payment_meta = get_post_meta( $payment_id );

			// Get payment meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $payment_meta ) ) {
					continue;
				}

				$action = ( isset( $meta_options['privacy_erasure'] ) ? $meta_options['privacy_erasure'] : null );

				$privacy_manager->erase_meta( $payment_id, $meta_key, $action );
			}

			// Customer.
			$customer = $payment->get_customer();

			if ( null !== $customer ) {
				CustomerHelper::anonymize_customer( $customer );
			}

			// Billing Address.
			$address = $payment->get_billing_address();

			if ( null !== $address ) {
				AddressHelper::anonymize_address( $address );
			}

			// Shipping Address.
			$address = $payment->get_shipping_address();

			if ( null !== $address ) {
				AddressHelper::anonymize_address( $address );
			}

			// Set anonymized.
			$payment->set_anonymized( true );

			// Save.
			$payment->save();

			// Add payment note.
			$payment->add_note( __( 'Payment anonymized for personal data erasure request.', 'pronamic_ideal' ) );

			// Add message.
			/* translators: %s: Payment ID */
			$messages[] = sprintf( __( 'Payment ID %s anonymized.', 'pronamic_ideal' ), $payment_id );

			$items_removed = true;
		}

		// Return results.
		return [
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => true,
		];
	}
}
