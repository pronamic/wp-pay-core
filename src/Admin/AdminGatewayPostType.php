<?php
/**
 * Gateway Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\WebhookManager;
use WP_Post;

/**
 * WordPress admin gateway post type
 *
 * @author  Remco Tolsma
 * @version 2.2.6
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
	 * Construct admin gateway post type.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ $this, 'edit_columns' ] );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );

		add_action( 'post_edit_form_tag', [ $this, 'post_edit_form_tag' ] );

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_post' ] );

		add_action( 'after_delete_post', [ $this, 'after_delete_post' ], 10, 2 );

		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );

		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
	}

	/**
	 * Edit columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function edit_columns( $columns ) {
		$columns = [
			'cb'                         => '<input type="checkbox" />',
			'title'                      => __( 'Title', 'pronamic_ideal' ),
			'pronamic_gateway_variant'   => __( 'Variant', 'pronamic_ideal' ),
			'pronamic_gateway_id'        => __( 'ID', 'pronamic_ideal' ),
			'pronamic_gateway_dashboard' => __( 'Dashboard', 'pronamic_ideal' ),
			'date'                       => __( 'Date', 'pronamic_ideal' ),
		];

		return $columns;
	}

	/**
	 * Custom columns.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function custom_columns( $column, $post_id ) {
		$id = get_post_meta( $post_id, '_pronamic_gateway_id', true );

		$integration = $this->plugin->gateway_integrations->get_integration( $id );

		switch ( $column ) {
			case 'pronamic_gateway_variant':
				$value = \strval( $id );

				if ( isset( $integration ) ) {
					$name = $integration->get_name();

					if ( null !== $name ) {
						$value = $name;
					}
				}

				echo \esc_html( $value );

				break;
			case 'pronamic_gateway_id':
				$data = array_filter(
					[
						get_post_meta( $post_id, '_pronamic_gateway_ems_ecommerce_storename', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ideal_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_buckaroo_website_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_icepay_merchant_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_mollie_partner_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_multisafepay_account_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_pay_nl_service_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_paydutch_username', true ),
						get_post_meta( $post_id, '_pronamic_gateway_targetpay_layoutcode', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_psp_id', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_user_id', true ),
					]
				);

				$display_value = \implode( ' ', $data );

				/**
				 * Filters the gateway configuration display value.
				 *
				 * @param string $display_value Display value.
				 * @param int    $post_id       Gateway configuration post ID.
				 */
				$display_value = \apply_filters( 'pronamic_gateway_configuration_display_value', $display_value, $post_id );

				/**
				 * Filters the gateway configuration display value.
				 *
				 * The dynamic portion of the hook name, `$id`, refers to the gateway ID.
				 * For example, the gateway ID for Payvision is `payvision`, so the filter
				 * for that gateway would be:
				 * `pronamic_gateway_configuration_display_value_payvision`
				 *
				 * @param string $display_value Display value.
				 * @param int    $post_id       Gateway configuration post ID.
				 */
				$display_value = \apply_filters( "pronamic_gateway_configuration_display_value_{$id}", $display_value, $post_id );

				echo \esc_html( $display_value );

				break;
			case 'pronamic_gateway_secret':
				$data = array_filter(
					[
						get_post_meta( $post_id, '_pronamic_gateway_ideal_basic_hash_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_omnikassa_secret_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_buckaroo_secret_key', true ),
						get_post_meta( $post_id, '_pronamic_gateway_icepay_secret_code', true ),
						get_post_meta( $post_id, '_pronamic_gateway_ogone_password', true ),
					]
				);

				echo esc_html( implode( ' ', $data ) );

				break;
			case 'pronamic_gateway_dashboard':
				if ( isset( $integration ) ) {
					$url = $integration->get_dashboard_url();

					if ( null !== $url ) {
						\printf(
							'<a href="%s" target="_blank">%s</a>',
							esc_url( $url ),
							esc_html__( 'Dashboard', 'pronamic_ideal' )
						);
					}
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
	 * @return void
	 */
	public function post_edit_form_tag( $post ) {
		if ( self::POST_TYPE !== get_post_type( $post ) ) {
			return;
		}

		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Add meta boxes.
	 *
	 * @param string $post_type Post Type.
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		add_meta_box(
			'pronamic_gateway_config',
			__( 'Configuration', 'pronamic_ideal' ),
			[ $this, 'meta_box_config' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_gateway_test',
			__( 'Test', 'pronamic_ideal' ),
			[ $this, 'meta_box_test' ],
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Pronamic Pay gateway config meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_config( $post ) {
		wp_nonce_field( 'pronamic_pay_save_gateway', 'pronamic_pay_nonce' );

		$plugin = $this->plugin;

		$gateway = Plugin::get_gateway( $post->ID );

		include __DIR__ . '/../../views/meta-box-gateway-config.php';

		\wp_localize_script(
			'pronamic-pay-admin',
			'pronamicPayGatewayAdmin',
			[
				'rest_url' => \rest_url( 'pronamic-pay/v1/gateways/' . $post->ID . '/admin' ),
				'nonce'    => \wp_create_nonce( 'wp_rest' ),
			]
		);
	}

	/**
	 * Pronamic Pay gateway payment methods setting.
	 *
	 * @param null|Gateway $gateway    Gateway.
	 * @param null|string  $gateway_id Gateway ID.
	 *
	 * @return void
	 */
	public static function settings_payment_methods( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- Parameters are used in include.
		$gateway,
		$gateway_id
	) {
		if ( null === $gateway ) {
			return;
		}

		$payment_methods = $gateway->get_payment_methods()->getIterator();

		$payment_methods->uasort(
			function ( $a, $b ) {
				return strnatcasecmp( $a->get_name(), $b->get_name() );
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
	public static function settings_webhook_log( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- Parameters are used in include.
		$gateway,
		$gateway_id,
		$config_id
	) {
		if ( null === $gateway ) {
			return;
		}

		require __DIR__ . '/../../views/meta-box-gateway-webhook-log.php';
	}

	/**
	 * Pronamic Pay gateway test meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_test( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is used in include.
		$post
	) {
		\wp_register_script(
			'pronamic-pay-gateway-test',
			plugins_url( 'js/dist/admin-test.min.js', dirname( __DIR__ ) ),
			[
				'jquery',
			],
			$this->plugin->get_version(),
			false
		);

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
		if ( ! \filter_has_var( INPUT_POST, 'pronamic_pay_nonce' ) ) {
			return;
		}

		\check_admin_referer( 'pronamic_pay_save_gateway', 'pronamic_pay_nonce' );

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// OK, it's safe for us to save the data now.
		if ( ! \array_key_exists( '_pronamic_gateway_id', $_POST ) ) {
			return;
		}

		// Gateway.
		$gateway_id = \sanitize_text_field( \wp_unslash( $_POST['_pronamic_gateway_id'] ) );

		\update_post_meta( $post_id, '_pronamic_gateway_id', $gateway_id );

		// Transient.
		\delete_transient( 'pronamic_outdated_webhook_urls' );

		// Gateway fields.
		if ( empty( $gateway_id ) ) {
			return;
		}

		$integration = $this->plugin->gateway_integrations->get_integration( $gateway_id );

		if ( null === $integration ) {
			return;
		}

		// Delete transients.
		$config = $integration->get_config( $post_id );

		\delete_transient( 'pronamic_pay_issuers_' . md5( serialize( $config ) ) );
		\delete_transient( 'pronamic_gateway_payment_methods_' . md5( serialize( $config ) ) );

		// Remove legacy gateway mode meta, to allow updating the gateway integration setting.
		\delete_post_meta( $post_id, '_pronamic_gateway_mode' );

		// Save settings.
		$fields = $integration->get_settings_fields();

		foreach ( $fields as $field ) {
			// Check presence of required field settings.
			if ( ! \array_key_exists( 'meta_key', $field ) ) {
				continue;
			}

			$name = $field['meta_key'];

			if ( ! \array_key_exists( $name, $_POST ) ) {
				continue;
			}

			$callback = static function ( $name ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( ! \array_key_exists( $name, $_POST ) ) {
					return '';
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				return \sanitize_text_field( \wp_unslash( $_POST[ $name ] ) );
			};

			if ( \array_key_exists( 'type', $field ) ) {
				$type = $field['type'];

				switch ( $type ) {
					case 'textarea':
						$callback = static function ( $name ) {
							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							if ( ! \array_key_exists( $name, $_POST ) ) {
								return '';
							}

							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							return \sanitize_textarea_field( \wp_unslash( $_POST[ $name ] ) );
						};

						break;
					case 'checkbox':
						$callback = static function ( $name ) {
							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							if ( ! \array_key_exists( $name, $_POST ) ) {
								return '';
							}

							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							return '1' === \sanitize_text_field( \wp_unslash( $_POST[ $name ] ) );
						};

						break;
				}
			}

			if ( \array_key_exists( 'input', $field ) ) {
				$callback = $field['input'];
			}

			$value = \call_user_func( $callback, $name );

			if ( array_key_exists( 'filter', $field ) ) {
				$filter = $field['filter'];

				$value = \filter_var( $value, $filter );
			}

			// Update post meta.
			if ( '' !== $value ) {
				\update_post_meta( $post_id, $name, $value );
			} else {
				\delete_post_meta( $post_id, $name );
			}
		}

		$integration->save_post( $post_id );

		// Update active payment methods.
		PaymentMethods::update_active_payment_methods();
	}

	/**
	 * Update active payment methods on gateway post deletion.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 * @return void
	 */
	public function after_delete_post( $post_id, $post ): void {
		if ( self::POST_TYPE !== $post->post_type ) {
			return;
		}

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

		$messages[ self::POST_TYPE ] = [
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
		];

		return $messages;
	}
}
