<?php
/**
 * Subscriptions privacy exporters and erasers.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Subscriptions Privacy class.
 *
 * @author  Reüel van der Steege
 * @version 5.2.0
 * @since   5.2.0
 * @package Pronamic\WordPress\Pay\Subscriptions
 */
class SubscriptionsPrivacy {
	/**
	 * Subscriptions privacy constructor.
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
		// Subscriptions export.
		$privacy_manager->add_exporter(
			'subscriptions',
			__( 'Subscriptions', 'pronamic_ideal' ),
			array( $this, 'subscriptions_export' )
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
		// Subscriptions anonymizer.
		$privacy_manager->add_eraser(
			'subscriptions',
			__( 'Subscriptions', 'pronamic_ideal' ),
			array( $this, 'subscriptions_anonymizer' )
		);
	}

	/**
	 * Subscriptions exporter.
	 *
	 * @param string $email_address Email address.
	 * @param int    $page          Page.
	 *
	 * @return array
	 */
	public function subscriptions_export( $email_address, $page = 1 ) {
		// Subscriptions data store.
		$data_store = pronamic_pay_plugin()->subscriptions_data_store;

		$items = array();

		// Get subscriptions.
		// @todo use paging
		$subscriptions = get_pronamic_subscriptions_by_meta(
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

		foreach ( $subscriptions as $subscription ) {
			$export_data = array();

			$subscription_meta = get_post_meta( $subscription->get_id() );

			// Get subscription meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $subscription_meta ) ) {
					continue;
				}

				// Label.
				$label = $meta_key;

				if ( isset( $meta_options['label'] ) ) {
					$label = $meta_options['label'];
				}

				// Meta value.
				$meta_value = $subscription_meta[ $meta_key ];

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
					'group_id'    => 'pronamic-subscriptions',
					'group_label' => __( 'Subscriptions', 'pronamic_ideal' ),
					'item_id'     => 'pronamic-subscription-' . $subscription->get_id(),
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

	public function subscriptions_anonymizer( $email_address, $page = 1 ) {
		// @todo implement subscriptions anonymizer
	}
}
