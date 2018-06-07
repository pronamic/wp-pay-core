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

		$items = array();

		// Get payments.
		// @todo use paging
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

		foreach ( $payments as $payment ) {
			$export_data = array();

			$payment_meta = get_post_meta( $payment->get_id() );

			// Get payment meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $payment_meta ) ) {
					continue;
				}

				// Label.
				$label = $meta_key;

				if ( isset( $meta_options['label'] ) ) {
					$label = $meta_options['label'];
				}

				// Meta value.
				$meta_value = $payment_meta[ $meta_key ];

				if ( 1 === count( $meta_value ) ) {
					$meta_value = array_shift( $meta_value );
				} else {
					$meta_value = wp_json_encode( $meta_value );
				}

				// Add to export data.
				$export_data[] = array(
					'name'  => $label,
					'value' => $meta_value,
				);
			}

			// Add item to export data.
			if ( ! empty( $export_data ) ) {
				$items[] = array(
					'group_id'    => 'pronamic-payments',
					'group_label' => __( 'Payments', 'pronamic_ideal' ),
					'item_id'     => 'pronamic-payment-' . $payment->get_id(),
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

	public function payments_anonymizer( $email_address, $page = 1 ) {
		// @todo implement payments anonymizer
	}
}
