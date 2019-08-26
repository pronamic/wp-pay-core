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
 * @param bool|int|string|null $post_id A payment post ID.
 * @return Payment|null
 */
function get_pronamic_payment( $post_id ) {
	if ( empty( $post_id ) ) {
		return null;
	}

	$post_id = (int) $post_id;

	$post_type = get_post_type( $post_id );

	if ( 'pronamic_payment' !== $post_type ) {
		return null;
	}

	$payment = new Payment( $post_id );

	return $payment;
}

/**
 * Get payment by specified meta key and value.
 *
 * @link https://developer.wordpress.org/reference/classes/wp_query/
 * @link https://developer.wordpress.org/reference/functions/wp_reset_postdata/
 *
 * @param string     $meta_key   The meta key to query for.
 * @param string|int $meta_value The Meta value to query for.
 * @return Payment|null
 */
function get_pronamic_payment_by_meta( $meta_key, $meta_value ) {
	$payment = null;

	$query = new WP_Query(
		array(
			'post_type'      => 'pronamic_payment',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => $meta_key,
					'value' => $meta_value,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$payment = get_pronamic_payment( get_the_ID() );
		}

		wp_reset_postdata();
	}

	return $payment;
}

/**
 * Get payments by specified meta key and value.
 *
 * @link https://developer.wordpress.org/reference/classes/wp_query/
 * @link https://developer.wordpress.org/reference/functions/wp_reset_postdata/
 *
 * @param string     $meta_key   The meta key to query for.
 * @param string|int $meta_value The Meta value to query for.
 * @return Payment[]
 */
function get_pronamic_payments_by_meta( $meta_key, $meta_value ) {
	$payments = array();

	$query = new WP_Query(
		array(
			'post_type'      => 'pronamic_payment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => $meta_key,
					'value' => $meta_value,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$payment = get_pronamic_payment( get_the_ID() );

			if ( null !== $payment ) {
				$payments[] = $payment;
			}
		}

		wp_reset_postdata();
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

	$post_id = (int) $post_id;

	$post_type = get_post_type( $post_id );

	if ( 'pronamic_pay_subscr' !== $post_type ) {
		return null;
	}

	$subscription = new Subscription( $post_id );

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
	$subscription = null;

	$query = new WP_Query(
		array(
			'post_type'      => 'pronamic_pay_subscr',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => $meta_key,
					'value' => $meta_value,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$subscription = get_pronamic_subscription( (int) get_the_ID() );
		}

		wp_reset_postdata();
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
	$subscriptions = array();

	$query = new WP_Query(
		array(
			'post_type'      => 'pronamic_pay_subscr',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => $meta_key,
					'value' => $meta_value,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$subscription = get_pronamic_subscription( (int) get_the_ID() );

			if ( null !== $subscription ) {
				$subscriptions[] = $subscription;
			}
		}

		wp_reset_postdata();
	}

	return $subscriptions;
}

/**
 * Bind the global providers and gateways together.
 */
function bind_providers_and_gateways() {
	global $pronamic_pay_providers;

	$integrations = pronamic_pay_plugin()->gateway_integrations;

	foreach ( $integrations as $integration ) {
		$provider = $integration->provider;

		if ( ! isset( $pronamic_pay_providers[ $provider ] ) ) {
			$pronamic_pay_providers[ $provider ] = array(
				'integrations' => array(),
			);
		}

		$pronamic_pay_providers[ $provider ]['integrations'][] = $integration;
	}

	// Sort by provider.
	ksort( $pronamic_pay_providers );
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

