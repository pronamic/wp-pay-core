<?php
/**
 * Gateway Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Util;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\WebhookManager;
use WP_Post;

/**
 * WordPress admin gateway post type
 *
 * @author  Remco Tolsma
 * @version 2.1.6
 * @since   ?
 */
class AdminGatewayPostType {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'pronamic_gateway';

	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Admin.
	 *
	 * @var AdminModule
	 */
	private $admin;

	/**
	 * Constructs and initializes an admin gateway post type object.
	 *
	 * @param Plugin      $plugin Plugin.
	 * @param AdminModule $admin  Admin Module.
	 */
	public function __construct( Plugin $plugin, AdminModule $admin ) {
		$this->plugin = $plugin;
		$this->admin  = $admin;

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'edit_columns' ) );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );

		add_action( 'post_edit_form_tag', array( $this, 'post_edit_form_tag' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_post' ) );

		add_action( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Edit columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function edit_columns( $columns ) {
		$columns = array(
			'cb'                         => '<input type="checkbox" />',
			'title'                      => __( 'Title', 'pronamic_ideal' ),
			'pronamic_gateway_variant'   => __( 'Variant', 'pronamic_ideal' ),
			'pronamic_gateway_id'        => __( 'ID', 'pronamic_ideal' ),
			'pronamic_gateway_dashboard' => __( 'Dashboard', 'pronamic_ideal' ),
			'date'                       => __( 'Date', 'pronamic_ideal' ),
		);

		return $columns;
	}

	/**
	 * Custom columns.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public function custom_columns( $column, $post_id ) {
		$id = get_post_meta( $post_id, '_pronamic_gateway_id', true );

		$integration = $this->plugin->gateway_integrations->get_integration( $id );

		switch ( $column ) {
			case 'pronamic_gateway_variant':
				if ( isset( $integration ) ) {
					echo esc_html( $integration->get_name() );
				} else {
					echo esc_html( strval( $id ) );
				}

				break;
			case 'pronamic_gateway_id':
				$data = array_filter(
					array(
						get_post_meta( $post_id, '_pronamic_gateway_adyen_merchant_account', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ems_ecommerce_storename', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ideal_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_omnikassa_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_buckaroo_website_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_icepay_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_mollie_partner_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_multisafepay_account_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_pay_nl_service_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_paydutch_username', true ),
						get_post_meta( $post_id, '_pronamic_gateway_sisow_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_targetpay_layoutcode', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_psp_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_user_id', true ),
					)
				);

				echo esc_html( implode( ' ', $data ) );

				break;
			case 'pronamic_gateway_secret':
				$data = array_filter(
					array(
						get_post_meta( $post_id, '_pronamic_gateway_ideal_basic_hash_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_omnikassa_secret_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_buckaroo_secret_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_icepay_secret_code', true ),
						get_post_meta( $post_id, '_pronamic_gateway_sisow_merchant_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_password', true ),
					)
				);

				echo esc_html( implode( ' ', $data ) );

				break;
			case 'pronamic_gateway_dashboard':
				if ( isset( $integration ) ) {
					$urls = $integration->get_dashboard_url();

					// Output.
					$content = array();

					foreach ( $urls as $name => $url ) {
						if ( empty( $name ) ) {
							$name = __( 'Dashboard', 'pronamic_ideal' );
						}

						$content[] = sprintf(
							'<a href="%s" target="_blank">%s</a>',
							esc_attr( $url ),
							esc_html( ucfirst( strval( $name ) ) )
						);
					}

					echo wp_kses(
						implode(
							' | ',
							$content
						),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						)
					);
				}

				break;
		}
	}

	/**
	 * Display post states.
	 *
	 * @param array    $post_states Post states.
	 * @param \WP_Post $post        Post.
	 *
	 * @return array
	 */
	public function display_post_states( $post_states, $post ) {
		if ( self::POST_TYPE !== get_post_type( $post ) ) {
			return $post_states;
		}

		if ( intval( get_option( 'pronamic_pay_config_id' ) ) === $post->ID ) {
			$post_states['pronamic_pay_config_default'] = __( 'Default', 'pronamic_ideal' );
		}

		return $post_states;
	}

	/**
	 * Post edit form tag.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/3.5.1/wp-admin/edit-form-advanced.php#L299
	 * @link https://github.com/WordPress/WordPress/blob/3.5.2/wp-admin/edit-form-advanced.php#L299
	 *
	 * @param WP_Post $post Post (only available @since 3.5.2).
	 */
	public function post_edit_form_tag( $post ) {
		if ( self::POST_TYPE === get_post_type( $post ) ) {
			echo ' enctype="multipart/form-data"';
		}
	}

	/**
	 * Add meta boxes.
	 *
	 * @param string $post_type Post Type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( self::POST_TYPE === $post_type ) {
			add_meta_box(
				'pronamic_gateway_config',
				__( 'Configuration', 'pronamic_ideal' ),
				array( $this, 'meta_box_config' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'pronamic_gateway_test',
				__( 'Test', 'pronamic_ideal' ),
				array( $this, 'meta_box_test' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Pronamic Pay gateway config meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function meta_box_config( $post ) {
		wp_nonce_field( 'pronamic_pay_save_gateway', 'pronamic_pay_nonce' );

		$gateway = Plugin::get_gateway( $post->ID );

		include __DIR__ . '/../../views/meta-box-gateway-config.php';

		wp_localize_script(
			'pronamic-pay-admin',
			'pronamicPayGatewayAdmin',
			array(
				'rest_url' => rest_url( 'pronamic-pay/v1/gateways/' . $post->ID ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Pronamic Pay gateway payment methods setting.
	 *
	 * @param null|Gateway $gateway Gateway.
	 *
	 * @return void
	 */
	public static function settings_payment_methods( $gateway ) {
		if ( null === $gateway ) {
			return;
		}

		// Supported and available payment methods.
		$supported = $gateway->get_supported_payment_methods();
		$available = $gateway->get_transient_available_payment_methods();

		$payment_methods = array();

		foreach ( $supported as $payment_method ) {
			$name = PaymentMethods::get_name( $payment_method );

			$payment_methods[ $payment_method ] = (object) array(
				'id'        => $payment_method,
				'name'      => $name,
				'available' => in_array( $payment_method, $available, true ),
			);
		}

		usort(
			$payment_methods,
			function( $a, $b ) {
				return strnatcasecmp( $a->name, $b->name );
			}
		);

		require __DIR__ . '/../../views/meta-box-gateway-payment-methods.php';
	}

	/**
	 * Pronamic Pay gateway webhook log setting.
	 *
	 * @param null|Gateway $gateway    Gateway.
	 * @param null|string  $gateway_id Gateway ID.
	 * @param null|int     $config_id  Config ID.
	 *
	 * @return void
	 */
	public static function settings_webhook_log( $gateway, $gateway_id, $config_id ) {
		if ( null === $gateway ) {
			return;
		}

		require __DIR__ . '/../../views/meta-box-gateway-webhook-log.php';
	}

	/**
	 * Pronamic Pay gateway test meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function meta_box_test( $post ) {
		include __DIR__ . '/../../views/meta-box-gateway-test.php';
	}

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-includes/post.php#L3928-L3951
	 *
	 * @param int $post_id The ID of the post being saved.
	 * @return void
	 */
	public function save_post( $post_id ) {
		// Nonce.
		if ( ! filter_has_var( INPUT_POST, 'pronamic_pay_nonce' ) ) {
			return;
		}

		check_admin_referer( 'pronamic_pay_save_gateway', 'pronamic_pay_nonce' );

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// OK, its safe for us to save the data now.
		if ( ! filter_has_var( INPUT_POST, '_pronamic_gateway_id' ) ) {
			return;
		}

		// Gateway.
		$gateway_id = filter_input( INPUT_POST, '_pronamic_gateway_id', FILTER_SANITIZE_STRING );

		update_post_meta( $post_id, '_pronamic_gateway_id', $gateway_id );

		// Mode.
		if ( filter_has_var( INPUT_POST, '_pronamic_gateway_mode' ) ) {
			$gateway_mode = filter_input( INPUT_POST, '_pronamic_gateway_mode', FILTER_SANITIZE_STRING );

			update_post_meta( $post_id, '_pronamic_gateway_mode', $gateway_mode );
		}

		// Transient.
		delete_transient( 'pronamic_outdated_webhook_urls' );

		// Gatway fields.
		if ( empty( $gateway_id ) ) {
			return;
		}

		$integration = $this->plugin->gateway_integrations->get_integration( $gateway_id );

		if ( null === $integration ) {
			return;
		}

		// Delete transients.
		$config = $integration->get_config( $post_id );

		delete_transient( 'pronamic_pay_issuers_' . md5( serialize( $config ) ) );
		delete_transient( 'pronamic_gateway_payment_methods_' . md5( serialize( $config ) ) );

		// Save settings.
		$fields = $integration->get_settings_fields();

		foreach ( $fields as $field ) {
			if ( isset( $field['meta_key'], $field['filter'] ) ) {
				$name   = $field['meta_key'];
				$filter = $field['filter'];

				if ( filter_has_var( INPUT_POST, $name ) ) {
					$value = filter_input( INPUT_POST, $name, $filter );

					update_post_meta( $post_id, $name, $value );
				}
			}
		}

		$integration->save_post( $post_id );

		// Update active payment methods.
		PaymentMethods::update_active_payment_methods();
	}

	/**
	 * Post updated messages.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 * @link https://github.com/WordPress/WordPress/blob/4.4.2/wp-admin/edit-form-advanced.php#L134-L173
	 * @link https://github.com/WordPress/WordPress/blob/5.2.1/wp-admin/edit-form-advanced.php#L164-L203
	 * @link https://github.com/woothemes/woocommerce/blob/2.5.5/includes/admin/class-wc-admin-post-types.php#L111-L168
	 * @link https://github.com/woocommerce/woocommerce/blob/3.6.4/includes/admin/class-wc-admin-post-types.php#L110-L180
	 * @link https://developer.wordpress.org/reference/hooks/post_updated_messages/
	 *
	 * @param array $messages Messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post;

		// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352797&filters[translation_id]=37948900
		$scheduled_date = date_i18n( __( 'M j, Y @ H:i', 'pronamic_ideal' ), strtotime( $post->post_date ) );

		$messages[ self::POST_TYPE ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Configuration updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352799&filters[translation_id]=37947229
			2  => $messages['post'][2],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352800&filters[translation_id]=37947870
			3  => $messages['post'][3],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352798&filters[translation_id]=37947230
			4  => __( 'Configuration updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352801&filters[translation_id]=37947231
			/* phpcs:disable WordPress.Security.NonceVerification.Recommended */
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Configuration restored to revision from %s.', 'pronamic_ideal' ), strval( wp_post_revision_title( (int) $_GET['revision'], false ) ) ) : false,
			/* phpcs:enable WordPress.Security.NonceVerification.Recommended */
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352802&filters[translation_id]=37949178
			6  => __( 'Configuration published.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352803&filters[translation_id]=37947232
			7  => __( 'Configuration saved.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352804&filters[translation_id]=37949303
			8  => __( 'Configuration submitted.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352805&filters[translation_id]=37949302
			/* translators: %s: scheduled date */
			9  => sprintf( __( 'Configuration scheduled for: %s.', 'pronamic_ideal' ), '<strong>' . $scheduled_date . '</strong>' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352806&filters[translation_id]=37949301
			10 => __( 'Configuration draft updated.', 'pronamic_ideal' ),
		);

		return $messages;
	}
}
