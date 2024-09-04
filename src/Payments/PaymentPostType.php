<?php
/**
 * Payment Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Title: WordPress iDEAL post types
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.7.1
 * @since   3.7.0
 */
class PaymentPostType {
	/**
	 * Constructs and initializes an post types object
	 */
	public function __construct() {
		/**
		 * Priority of the initial post types function should be set to < 10
		 *
		 * @link https://core.trac.wordpress.org/ticket/28488
		 * @link https://core.trac.wordpress.org/changeset/29318
		 *
		 * @link https://github.com/WordPress/WordPress/blob/4.0/wp-includes/post.php#L167
		 */
		add_action( 'init', [ $this, 'register_payment_post_type' ], 0 ); // Highest priority.
		add_action( 'init', [ $this, 'register_post_status' ], 9 );
	}

	/**
	 * Register post types.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.6.1/wp-includes/post.php#L1277-L1300
	 * @return void
	 */
	public function register_payment_post_type() {
		register_post_type(
			'pronamic_payment',
			[
				'label'              => __( 'Payments', 'pronamic_ideal' ),
				'labels'             => [
					'name'                     => __( 'Payments', 'pronamic_ideal' ),
					'singular_name'            => __( 'Payment', 'pronamic_ideal' ),
					'add_new'                  => __( 'Add New', 'pronamic_ideal' ),
					'add_new_item'             => __( 'Add New Payment', 'pronamic_ideal' ),
					'edit_item'                => __( 'Edit Payment', 'pronamic_ideal' ),
					'new_item'                 => __( 'New Payment', 'pronamic_ideal' ),
					'all_items'                => __( 'All Payments', 'pronamic_ideal' ),
					'view_item'                => __( 'View Payment', 'pronamic_ideal' ),
					'search_items'             => __( 'Search Payments', 'pronamic_ideal' ),
					'not_found'                => __( 'No payments found.', 'pronamic_ideal' ),
					'not_found_in_trash'       => __( 'No payments found in Trash.', 'pronamic_ideal' ),
					'menu_name'                => __( 'Payments', 'pronamic_ideal' ),
					'filter_items_list'        => __( 'Filter payments list', 'pronamic_ideal' ),
					'items_list_navigation'    => __( 'Payments list navigation', 'pronamic_ideal' ),
					'items_list'               => __( 'Payments list', 'pronamic_ideal' ),

					/*
					 * New Post Type Labels in 5.0.
					 * @link https://make.wordpress.org/core/2018/12/05/new-post-type-labels-in-5-0/
					 */
					'item_published'           => __( 'Payment published.', 'pronamic_ideal' ),
					'item_published_privately' => __( 'Payment published privately.', 'pronamic_ideal' ),
					'item_reverted_to_draft'   => __( 'Payment reverted to draft.', 'pronamic_ideal' ),
					'item_scheduled'           => __( 'Payment scheduled.', 'pronamic_ideal' ),
					'item_updated'             => __( 'Payment updated.', 'pronamic_ideal' ),
				],
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_nav_menus'  => false,
				'show_in_menu'       => false,
				'show_in_admin_bar'  => false,
				'show_in_rest'       => true,
				'rest_base'          => 'pronamic-payments',
				'supports'           => [
					'pronamic_pay_payment',
				],
				'rewrite'            => false,
				'query_var'          => false,
				'capabilities'       => self::get_capabilities(),
				'map_meta_cap'       => true,
			]
		);
	}

	/**
	 * Get payment states.
	 *
	 * @return array
	 */
	public static function get_payment_states() {
		return [
			'payment_pending'    => _x( 'Pending', 'Payment status', 'pronamic_ideal' ),
			'payment_on_hold'    => _x( 'On Hold', 'Payment status', 'pronamic_ideal' ),
			'payment_completed'  => _x( 'Completed', 'Payment status', 'pronamic_ideal' ),
			'payment_cancelled'  => _x( 'Cancelled', 'Payment status', 'pronamic_ideal' ),
			'payment_refunded'   => _x( 'Refunded', 'Payment status', 'pronamic_ideal' ),
			'payment_failed'     => _x( 'Failed', 'Payment status', 'pronamic_ideal' ),
			'payment_expired'    => _x( 'Expired', 'Payment status', 'pronamic_ideal' ),
			'payment_authorized' => _x( 'Authorized', 'Payment status', 'pronamic_ideal' ),
		];
	}

	/**
	 * Register our custom post statuses, used for order status.
	 *
	 * @return void
	 */
	public function register_post_status() {
		/**
		 * Payment post statuses
		 */
		register_post_status(
			'payment_pending',
			[
				'label'                     => _x( 'Pending', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_reserved',
			[
				'label'                     => _x( 'Reserved', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Reserved <span class="count">(%s)</span>', 'Reserved <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_on_hold',
			[
				'label'                     => _x( 'On Hold', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_completed',
			[
				'label'                     => _x( 'Completed', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_cancelled',
			[
				'label'                     => _x( 'Cancelled', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_refunded',
			[
				'label'                     => _x( 'Refunded', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_failed',
			[
				'label'                     => _x( 'Failed', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_expired',
			[
				'label'                     => _x( 'Expired', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);

		register_post_status(
			'payment_authorized',
			[
				'label'                     => _x( 'Authorized', 'Payment status', 'pronamic_ideal' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count value */
				'label_count'               => _n_noop( 'Authorized <span class="count">(%s)</span>', 'Authorized <span class="count">(%s)</span>', 'pronamic_ideal' ),
			]
		);
	}

	/**
	 * Get capabilities for this post type.
	 *
	 * @return array
	 */
	public static function get_capabilities() {
		return [
			'edit_post'              => 'edit_payment',
			'read_post'              => 'read_payment',
			'delete_post'            => 'delete_payment',
			'edit_posts'             => 'edit_payments',
			'edit_others_posts'      => 'edit_others_payments',
			'publish_posts'          => 'publish_payments',
			'read_private_posts'     => 'read_private_payments',
			'read'                   => 'read',
			'delete_posts'           => 'delete_payments',
			'delete_private_posts'   => 'delete_private_payments',
			'delete_published_posts' => 'delete_published_payments',
			'delete_others_posts'    => 'delete_others_payments',
			'edit_private_posts'     => 'edit_private_payments',
			'edit_published_posts'   => 'edit_published_payments',
			'create_posts'           => 'create_payments',
		];
	}
}
