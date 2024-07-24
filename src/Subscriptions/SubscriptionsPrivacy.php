<?php
/**
 * Subscriptions privacy exporters and erasers.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Subscriptions Privacy class.
 *
 * @author  Reüel van der Steege
 * @version 2.2.6
 * @since   2.0.2
 */
class SubscriptionsPrivacy {
	/**
	 * Subscriptions privacy constructor.
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
		// Subscriptions export.
		$privacy_manager->add_exporter(
			'subscriptions',
			__( 'Subscriptions', 'pronamic_ideal' ),
			[ $this, 'subscriptions_export' ]
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
			[ $this, 'subscriptions_anonymizer' ]
		);
	}

	/**
	 * Subscriptions exporter.
	 *
	 * @param string $email_address Email address.
	 * @return array
	 */
	public function subscriptions_export( $email_address ) {
		// Subscriptions data store.
		$data_store = pronamic_pay_plugin()->subscriptions_data_store;

		// Privacy manager.
		$privacy_manager = pronamic_pay_plugin()->privacy_manager;

		// Get subscriptions.
		// @todo use paging.
		$subscriptions = get_pronamic_subscriptions_by_meta(
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

		// Loop subscriptions.
		foreach ( $subscriptions as $subscription ) {
			$export_data = [];

			$id = $subscription->get_id();

			if ( empty( $id ) ) {
				continue;
			}

			$subscription_meta = get_post_meta( $id );

			// Get subscription meta.
			foreach ( $meta_keys as $meta_key => $meta_options ) {
				$meta_key = $data_store->meta_key_prefix . $meta_key;

				if ( ! array_key_exists( $meta_key, $subscription_meta ) ) {
					continue;
				}

				// Add export value.
				$export_data[] = $privacy_manager->export_meta( $meta_key, $meta_options, $subscription_meta );
			}

			// Add item to export data.
			if ( ! empty( $export_data ) ) {
				$items[] = [
					'group_id'    => 'pronamic-pay-subscriptions',
					'group_label' => __( 'Subscriptions', 'pronamic_ideal' ),
					'item_id'     => 'pronamic-pay-subscription-' . $id,
					'data'        => $export_data,
				];
			}
		}

		$done = true;

		// Return export data.
		return [
			'data' => $items,
			'done' => $done,
		];
	}

	/**
	 * Subscriptions anonymizer.
	 *
	 * @param string $email_address Email address.
	 * @return array
	 */
	public function subscriptions_anonymizer( $email_address ) {
		// Subscriptions data store.
		$data_store = pronamic_pay_plugin()->subscriptions_data_store;

		// Privacy manager.
		$privacy_manager = pronamic_pay_plugin()->privacy_manager;

		// Return values.
		$items_removed  = false;
		$items_retained = false;
		$messages       = [];
		$done           = false;

		// Get subscriptions.
		// @todo use paging.
		$subscriptions = get_pronamic_subscriptions_by_meta(
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

		// Loop subscriptions.
		foreach ( $subscriptions as $subscription ) {
			$subscription_id = $subscription->get_id();

			if ( empty( $subscription_id ) ) {
				continue;
			}

			$subscription_meta = get_post_meta( $subscription_id );

			$subscription_status = null;

			if ( isset( $subscription_meta[ $data_store->meta_key_prefix . 'status' ] ) ) {
				$subscription_status = $subscription_meta[ $data_store->meta_key_prefix . 'status' ];
			}

			// Subscription note and erasure return message.
			$note = __( 'Subscription anonymized for personal data erasure request.', 'pronamic_ideal' );
			/* translators: %s = subscription id */
			$message = __( 'Subscription ID %s anonymized.', 'pronamic_ideal' );

			// Anonymize completed and cancelled subscriptions.
			if ( isset( $subscription_status ) && in_array( $subscription_status, [ SubscriptionStatus::COMPLETED, SubscriptionStatus::CANCELLED ], true ) ) {
				// Erase subscription meta.
				foreach ( $meta_keys as $meta_key => $meta_options ) {
					$meta_key = $data_store->meta_key_prefix . $meta_key;

					if ( ! array_key_exists( $meta_key, $subscription_meta ) ) {
						continue;
					}

					$action = ( isset( $meta_options['privacy_erasure'] ) ? $meta_options['privacy_erasure'] : null );

					$privacy_manager->erase_meta( $subscription_id, $meta_key, $action );
				}

				$items_removed = true;
			} else {
				$note = __( 'Subscription not anonymized for personal data erasure request because of active status.', 'pronamic_ideal' );

				/* translators: %s: Subscription ID */
				$message = __( 'Subscription ID %s not anonymized because of active status.', 'pronamic_ideal' );

				$items_retained = true;
			}

			// Add erasure return message.
			$messages[] = sprintf( $message, $subscription_id );

			// Add subscription note.
			try {
				$subscription->add_note( $note );
			} catch ( \Exception $e ) {
				continue;
			}
		}

		$done = true;

		// Return results.
		return [
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		];
	}
}
