<?php
/**
 * Functions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Admin\AdminPaymentPostType;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;

/**
 * Pronamic Pay plugin.
 *
 * @return \Pronamic\WordPress\Pay\Plugin
 */
function pronamic_pay_plugin() {
	return \Pronamic\WordPress\Pay\Plugin::instance();
}

/**
 * Get payment by specified post ID.
 *
 * @param int|string|null $post_id A payment post ID.
 * @return Payment|null
 */
function get_pronamic_payment( $post_id ) {
	if ( empty( $post_id ) ) {
		return null;
	}

	$post_type = get_post_type( $post_id );

	if ( AdminPaymentPostType::POST_TYPE !== $post_type ) {
		return null;
	}

	$payment = new Payment( $post_id );

	return $payment;
}

/**
 * Get payment by specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The Meta value to query for.
 * @return Payment|null
 */
function get_pronamic_payment_by_meta( $meta_key, $meta_value ) {
	global $wpdb;

	$db_query = $wpdb->prepare(
		"
		SELECT
			post_id
		FROM
			$wpdb->postmeta
		WHERE
			meta_key = %s
				AND
			meta_value = %s
			;
	",
		$meta_key,
		$meta_value
	);

	$post_id = $wpdb->get_var( $db_query ); // WPCS: unprepared SQL ok, db call ok, cache ok.

	$payment = get_pronamic_payment( $post_id );

	return $payment;
}

/**
 * Get payments by specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The Meta value to query for.
 * @return Payment[]
 */
function get_pronamic_payments_by_meta( $meta_key, $meta_value ) {
	global $wpdb;

	$payments = array();

	$db_query = $wpdb->prepare(
		"
		SELECT
			post_id
		FROM
			$wpdb->postmeta
		WHERE
			meta_key = %s
				AND
			meta_value = %s
		ORDER BY
			meta_id ASC
			;
	",
		$meta_key,
		$meta_value
	);

	$results = $wpdb->get_results( $db_query ); // WPCS: unprepared SQL ok, db call ok, cache ok.

	foreach ( $results as $result ) {
		$payment = new Payment( $result->post_id );

		if ( null !== $payment->post ) {
			$payments[] = new Payment( $result->post_id );
		}
	}

	return $payments;
}

/**
 * Get payment by the specified purchase ID.
 *
 * @param string $purchase_id The purchase ID to query for.
 * @return Payment|null
 */
function get_pronamic_payment_by_purchase_id( $purchase_id ) {
	return get_pronamic_payment_by_meta( '_pronamic_payment_purchase_id', $purchase_id );
}

/**
 * Get payment by the specified transaction ID.
 *
 * @param string $transaction_id The transaction ID to query for.
 *
 * @return Payment|null
 */
function get_pronamic_payment_by_transaction_id( $transaction_id ) {
	return get_pronamic_payment_by_meta( '_pronamic_payment_transaction_id', $transaction_id );
}

/**
 * Get subscription by the specified post ID.
 *
 * @param int $post_id A subscription post ID.
 * @return Subscription|null
 */
function get_pronamic_subscription( $post_id ) {
	if ( empty( $post_id ) ) {
		return null;
	}

	$subscription = new Subscription( $post_id );

	if ( ! isset( $subscription->post ) ) {
		return null;
	}

	return $subscription;
}

/**
 * Get subscription by the specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The Meta value to query for.
 * @return Subscription|null
 */
function get_pronamic_subscription_by_meta( $meta_key, $meta_value ) {
	global $wpdb;

	$subscription = null;

	$db_query = $wpdb->prepare(
		"
		SELECT
			post_id
		FROM
			$wpdb->postmeta
		WHERE
			meta_key = %s
				AND
			meta_value = %s
			;
	",
		$meta_key,
		$meta_value
	);

	$post_id = $wpdb->get_var( $db_query ); // WPCS: unprepared SQL ok, db call ok, cache ok.

	if ( $post_id ) {
		$subscription = new Subscription( $post_id );
	}

	return $subscription;
}

/**
 * Get subscriptions by specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The Meta value to query for.
 * @return Subscription[]
 */
function get_pronamic_subscriptions_by_meta( $meta_key, $meta_value ) {
	global $wpdb;

	$subscriptions = array();

	$db_query = $wpdb->prepare(
		"
		SELECT
			post_id
		FROM
			$wpdb->postmeta
		WHERE
			meta_key = %s
				AND
			meta_value = %s
		ORDER BY
			meta_id ASC
			;
	",
		$meta_key,
		$meta_value
	);

	$results = $wpdb->get_results( $db_query ); // WPCS: unprepared SQL ok, db call ok, cache ok.

	foreach ( $results as $result ) {
		$subscriptions[] = new Subscription( $result->post_id );
	}

	return $subscriptions;
}

/**
 * Bind the global providers and gateways together.
 */
function bind_providers_and_gateways() {
	global $pronamic_pay_providers;

	foreach ( pronamic_pay_plugin()->gateway_integrations as $integration ) {
		if ( isset( $pronamic_pay_providers[ $integration->provider ] ) ) {
			$provider =& $pronamic_pay_providers[ $integration->provider ];

			if ( ! isset( $provider['integrations'] ) ) {
				$provider['integrations'] = array();
			}

			$provider['integrations'][] = $integration;
		}
	}

	// Sort by provider name.
	usort(
		$pronamic_pay_providers,
		function( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		}
	);
}

/**
 * Let to num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @link https://github.com/woothemes/woocommerce/blob/v2.0.20/woocommerce-core-functions.php#L1779
 * @access public
 * @param string $size A php.ini notation for nubmer to convert to an integer.
 * @return int
 */
function pronamic_pay_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
			// no break.
		case 'T':
			$ret *= 1024;
			// no break.
		case 'G':
			$ret *= 1024;
			// no break.
		case 'M':
			$ret *= 1024;
			// no break.
		case 'K':
			$ret *= 1024;
			// no break.
	}

	return intval( $ret );
}

/**
 * Pronamic Pay get page ID.
 *
 * @link https://github.com/woothemes/woocommerce/blob/v2.0.16/woocommerce-core-functions.php#L344
 *
 * @param string $page Pronamic Pay page identifier slug.
 * @return int
 */
function pronamic_pay_get_page_id( $page ) {
	$option_name = sprintf( 'pronamic_pay_%s_page_id', $page );

	$option = get_option( $option_name, -1 );

	if ( false === $option ) {
		return -1;
	}

	return $option;
}

/**
 * Helper function to update post meta data.
 *
 * @link http://codex.wordpress.org/Function_Reference/update_post_meta
 * @param int   $post_id The post ID to update the specified meta data for.
 * @param array $data    The data array with meta keys/values.
 */
function pronamic_pay_update_post_meta_data( $post_id, array $data ) {
	/*
	 * Post meta values are passed through the stripslashes() function
	 * upon being stored, so you will need to be careful when passing
	 * in values such as JSON that might include \ escaped characters.
	 */
	$data = wp_slash( $data );

	// Meta.
	foreach ( $data as $key => $value ) {
		if ( isset( $value ) && '' !== $value ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}

