<?php
/**
 * Gateway Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Title: WordPress gateway post type
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   ?
 */
class GatewayPostType {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'pronamic_gateway';

	/**
	 * Constructs and initializes a gateway post type object.
	 */
	public function __construct() {
		/**
		 * Priority of the initial post types function should be set to < 10.
		 *
		 * @link https://core.trac.wordpress.org/ticket/28488
		 * @link https://core.trac.wordpress.org/changeset/29318
		 *
		 * @link https://github.com/WordPress/WordPress/blob/4.0/wp-includes/post.php#L167
		 */
		add_action( 'init', [ $this, 'register_gateway_post_type' ], 0 ); // Highest priority.

		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'maybe_set_default_gateway' ] );

		// REST API.
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	/**
	 * Register post types.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.6.1/wp-includes/post.php#L1277-L1300
	 * @return void
	 */
	public function register_gateway_post_type() {
		register_post_type(
			'pronamic_gateway',
			[
				'label'              => __( 'Payment Gateway Configurations', 'pronamic_ideal' ),
				'labels'             => [
					'name'                     => __( 'Payment Gateway Configurations', 'pronamic_ideal' ),
					'singular_name'            => __( 'Payment Gateway Configuration', 'pronamic_ideal' ),
					'add_new'                  => __( 'Add New', 'pronamic_ideal' ),
					'add_new_item'             => __( 'Add New Payment Gateway Configuration', 'pronamic_ideal' ),
					'edit_item'                => __( 'Edit Payment Gateway Configuration', 'pronamic_ideal' ),
					'new_item'                 => __( 'New Payment Gateway Configuration', 'pronamic_ideal' ),
					'all_items'                => __( 'All Payment Gateway Configurations', 'pronamic_ideal' ),
					'view_item'                => __( 'View Payment Gateway Configuration', 'pronamic_ideal' ),
					'search_items'             => __( 'Search Payment Gateway Configurations', 'pronamic_ideal' ),
					'not_found'                => __( 'No payment gateway configurations found.', 'pronamic_ideal' ),
					'not_found_in_trash'       => __( 'No payment gateway configurations found in Trash.', 'pronamic_ideal' ),
					'menu_name'                => __( 'Configurations', 'pronamic_ideal' ),
					'filter_items_list'        => __( 'Filter payment gateway configurations list', 'pronamic_ideal' ),
					'items_list_navigation'    => __( 'Payment gateway configurations list navigation', 'pronamic_ideal' ),
					'items_list'               => __( 'Payment gateway configurations list', 'pronamic_ideal' ),

					/*
					 * New Post Type Labels in 5.0.
					 * @link https://make.wordpress.org/core/2018/12/05/new-post-type-labels-in-5-0/
					 */
					'item_published'           => __( 'Payment gateway configuration published.', 'pronamic_ideal' ),
					'item_published_privately' => __( 'Payment gateway configuration published privately.', 'pronamic_ideal' ),
					'item_reverted_to_draft'   => __( 'Payment gateway configuration reverted to draft.', 'pronamic_ideal' ),
					'item_scheduled'           => __( 'Payment gateway configuration scheduled.', 'pronamic_ideal' ),
					'item_updated'             => __( 'Payment gateway configuration updated.', 'pronamic_ideal' ),
				],
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_nav_menus'  => false,
				'show_in_menu'       => false,
				'show_in_admin_bar'  => false,
				'hierarchical'       => true,
				'supports'           => [
					'title',
					'revisions',
				],
				'rewrite'            => false,
				'query_var'          => false,
				'capabilities'       => self::get_capabilities(),
				// Don't map meta capabilities since we only use the `manage_options` capability for this post type.
				'map_meta_cap'       => false,
			]
		);
	}

	/**
	 * Maybe set the default gateway.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function maybe_set_default_gateway( $post_id ) {
		// Don't set the default gateway if the post is not published.
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		// Don't set the default gateway if there is already a published gateway set.
		$config_id = get_option( 'pronamic_pay_config_id' );

		if ( ! empty( $config_id ) && 'publish' === get_post_status( $config_id ) ) {
			return;
		}

		// Update.
		update_option( 'pronamic_pay_config_id', $post_id );
	}

	/**
	 * Get capabilities for this post type.
	 *
	 * @return array
	 */
	public static function get_capabilities() {
		return [
			'edit_post'              => 'manage_options',
			'read_post'              => 'manage_options',
			'delete_post'            => 'manage_options',
			'edit_posts'             => 'manage_options',
			'edit_others_posts'      => 'manage_options',
			'publish_posts'          => 'manage_options',
			'read_private_posts'     => 'manage_options',
			'read'                   => 'manage_options',
			'delete_posts'           => 'manage_options',
			'delete_private_posts'   => 'manage_options',
			'delete_published_posts' => 'manage_options',
			'delete_others_posts'    => 'manage_options',
			'edit_private_posts'     => 'manage_options',
			'edit_published_posts'   => 'manage_options',
			'create_posts'           => 'manage_options',
		];
	}

	/**
	 * REST API init.
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
	 *
	 * @return void
	 */
	public function rest_api_init() {
		\register_rest_route(
			'pronamic-pay/v1',
			'/gateways/(?P<config_id>\d+)',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_api_gateway' ],
				'permission_callback' => function () {
					return \current_user_can( 'manage_options' );
				},
				'args'                => [
					'config_id' => [
						'description' => __( 'Gateway configuration ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
				],
			]
		);

		register_rest_route(
			'pronamic-pay/v1',
			'/gateways/(?P<config_id>\d+)/admin',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_api_gateway_admin' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'config_id'    => [
						'description' => __( 'Gateway configuration ID.', 'pronamic_ideal' ),
						'type'        => 'integer',
					],
					'gateway_id'   => [
						'description' => __( 'Gateway ID.', 'pronamic_ideal' ),
						'type'        => 'string',
					],
					'gateway_mode' => [
						'description' => __( 'Gateway mode.', 'pronamic_ideal' ),
						'type'        => 'string',
					],
				],
			]
		);
	}

	/**
	 * REST API gateway.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_gateway( \WP_REST_Request $request ) {
		$config_id = $request->get_param( 'config_id' );

		// Gateway.
		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return new \WP_Error(
				'pronamic-pay-gateway-not-found',
				\sprintf(
					/* translators: %s: Gateway configuration ID */
					\__( 'Could not find gateway with ID `%s`.', 'pronamic_ideal' ),
					$config_id
				),
				$config_id
			);
		}

		return $gateway;
	}

	/**
	 * REST API gateway.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_gateway_admin( \WP_REST_Request $request ) {
		$config_id    = $request->get_param( 'config_id' );
		$gateway_id   = $request->get_param( 'gateway_id' );
		$gateway_mode = $request->get_param( 'gateway_mode' );

		// Gateway.
		$args = [
			'gateway_id' => $gateway_id,
		];

		$gateway = Plugin::get_gateway( $config_id, $args );

		if ( empty( $gateway ) ) {
			return new \WP_Error(
				'pronamic-pay-gateway-not-found',
				sprintf(
					/* translators: %s: Gateway configuration ID */
					__( 'Could not find gateway with ID `%s`.', 'pronamic_ideal' ),
					$config_id
				),
				$config_id
			);
		}

		// Settings.
		ob_start();

		$plugin = \pronamic_pay_plugin();

		require __DIR__ . '/../views/meta-box-gateway-settings.php';

		$meta_box_settings = ob_get_clean();

		// Object.
		return (object) [
			'config_id'    => $config_id,
			'gateway_id'   => $gateway_id,
			'gateway_mode' => $gateway_mode,
			'meta_boxes'   => (object) [
				'settings' => $meta_box_settings,
			],
		];
	}
}
