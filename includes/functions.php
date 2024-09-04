<?php
/**
 * Functions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

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
 * @param bool|int|string|null $post_id A payment post ID.
 * @return Payment|null
 */
function get_pronamic_payment( $post_id ) {
	if ( \is_bool( $post_id ) ) {
		return null;
	}

	if ( \is_null( $post_id ) ) {
		return null;
	}

	return pronamic_pay_plugin()->payments_data_store->get_payment( (int) $post_id );
}

/**
 * Get payment by specified meta key and value.
 *
 * @link https://developer.wordpress.org/reference/classes/wp_query/
 * @link https://developer.wordpress.org/reference/functions/wp_reset_postdata/
 *
 * @param string     $meta_key   The meta key to query for.
 * @param string|int $meta_value The meta value to query for.
 * @param array      $args       Query arguments.
 * @return Payment|null
 */
function get_pronamic_payment_by_meta( $meta_key, $meta_value, $args = [] ) {
	$args['posts_per_page'] = 1;

	$payments = get_pronamic_payments_by_meta( $meta_key, $meta_value, $args );

	// No payments found.
	if ( empty( $payments ) ) {
		return null;
	}

	// Get first (and only) payment.
	$payment = array_shift( $payments );

	return $payment;
}

/**
 * Get payments by specified meta key and value.
 *
 * @link https://developer.wordpress.org/reference/classes/wp_query/
 * @link https://developer.wordpress.org/reference/functions/wp_reset_postdata/
 *
 * @param string     $meta_key   The meta key to query for.
 * @param string|int $meta_value The meta value to query for.
 * @param array      $args       Query arguments.
 * @return Payment[]
 */
function get_pronamic_payments_by_meta( $meta_key, $meta_value, $args = [] ) {
	$payments = [];

	$defaults = [
		'post_type'      => 'pronamic_payment',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => [],
	];

	$args = wp_parse_args( $args, $defaults );

	// Add meta query for given meta key and value.
	if ( ! empty( $meta_key ) ) {
		if ( ! is_array( $args['meta_query'] ) ) {
			$args['meta_query'] = [];
		}

		$args['meta_query'][] = [
			'key'   => $meta_key,
			'value' => $meta_value,
		];
	}

	$query = new WP_Query( $args );

	foreach ( $query->posts as $p ) {
		$payment = get_pronamic_payment( $p->ID );

		if ( null !== $payment ) {
			$payments[] = $payment;
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
 * Get payments by the specified user ID.
 *
 * @param string|int $user_id The user ID to query for.
 *
 * @return Payment[]
 */
function get_pronamic_payments_by_user_id( $user_id = null ) {
	if ( null === $user_id ) {
		$user_id = \get_current_user_id();
	}

	return get_pronamic_payments_by_meta( null, null, [ 'author' => $user_id ] );
}

/**
 * Get payments by the specified source and source ID.
 *
 * @param string          $source    The source to query for.
 * @param string|int|null $source_id The source ID to query for.
 * @return Payment[]
 */
function get_pronamic_payments_by_source( $source, $source_id = null ) {
	// Meta query.
	$meta_query = [
		[
			'key'   => '_pronamic_payment_source',
			'value' => $source,
		],
	];

	// Add source ID meta query condition.
	if ( ! empty( $source_id ) ) {
		$meta_query[] = [
			'key'   => '_pronamic_payment_source_id',
			'value' => $source_id,
		];
	}

	// Return.
	$args = [
		'meta_query' => $meta_query,
		'order'      => 'DESC',
		'orderby'    => 'ID',
	];

	return get_pronamic_payments_by_meta( null, null, $args );
}

/**
 * Get subscription by the specified post ID.
 *
 * @param int $post_id A subscription post ID.
 * @return Subscription|null
 */
function get_pronamic_subscription( $post_id ) {
	return pronamic_pay_plugin()->subscriptions_data_store->get_subscription( $post_id );
}

/**
 * Get subscription by the specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The meta value to query for.
 * @param array  $args       Query arguments.
 * @return Subscription|null
 */
function get_pronamic_subscription_by_meta( $meta_key, $meta_value, $args = [] ) {
	$args['posts_per_page'] = 1;

	$subscriptions = get_pronamic_subscriptions_by_meta( $meta_key, $meta_value, $args );

	// No subscriptions found.
	if ( empty( $subscriptions ) ) {
		return null;
	}

	// Get first (and only) subscription.
	$subscription = array_shift( $subscriptions );

	return $subscription;
}

/**
 * Get subscriptions by specified meta key and value.
 *
 * @param string $meta_key   The meta key to query for.
 * @param string $meta_value The meta value to query for.
 * @param array  $args       Query arguments.
 * @return Subscription[]
 */
function get_pronamic_subscriptions_by_meta( $meta_key, $meta_value, $args = [] ) {
	$subscriptions = [];

	$defaults = [
		'post_type'      => 'pronamic_pay_subscr',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => [],
	];

	$args = wp_parse_args( $args, $defaults );

	// Add meta query for given meta key and value.
	if ( ! is_array( $args['meta_query'] ) ) {
		$args['meta_query'] = [];
	}

	$args['meta_query'][] = [
		'key'   => $meta_key,
		'value' => $meta_value,
	];

	$query = new WP_Query( $args );

	foreach ( $query->posts as $p ) {
		$subscription = get_pronamic_subscription( $p->ID );

		if ( null !== $subscription ) {
			$subscriptions[] = $subscription;
		}
	}

	return $subscriptions;
}

/**
 * Get subscriptions by the specified user ID.
 *
 * @param string|int $user_id The user ID to query for.
 *
 * @return Subscription[]
 */
function get_pronamic_subscriptions_by_user_id( $user_id = null ) {
	if ( null === $user_id ) {
		$user_id = \get_current_user_id();
	}

	return get_pronamic_subscriptions_by_meta( null, null, [ 'author' => $user_id ] );
}

/**
 * Get subscriptions by the specified source and source ID.
 *
 * @param string          $source    The source to query for.
 * @param string|int|null $source_id The source ID to query for.
 * @return Subscription[]
 */
function get_pronamic_subscriptions_by_source( $source, $source_id = null ) {
	// Meta query.
	$meta_query = [
		[
			'key'   => '_pronamic_subscription_source',
			'value' => $source,
		],
	];

	// Add source ID meta query condition.
	if ( ! empty( $source_id ) ) {
		$meta_query[] = [
			'key'   => '_pronamic_subscription_source_id',
			'value' => $source_id,
		];
	}

	// Return.
	$args = [
		'meta_query' => $meta_query,
		'order'      => 'DESC',
		'orderby'    => 'ID',
	];

	return get_pronamic_subscriptions_by_meta( null, null, $args );
}

/**
 * Let to num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @link https://github.com/woothemes/woocommerce/blob/v2.0.20/woocommerce-core-functions.php#L1779
 * @access public
 * @param string $size A php.ini notation for number to convert to an integer.
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
	$data = (array) wp_slash( $data );

	// Meta.
	foreach ( $data as $key => $value ) {
		if ( isset( $value ) && '' !== $value ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
