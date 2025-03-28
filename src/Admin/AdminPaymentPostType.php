<?php
/**
 * Payment Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentPostType;
use Pronamic\WordPress\Pay\Plugin;
use WP_Post;

/**
 * WordPress admin payment post type
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   1.0.0
 */
class AdminPaymentPostType {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'pronamic_payment';

	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Admin notices.
	 *
	 * @var array
	 */
	private $admin_notices = [];

	/**
	 * Constructs and initializes an admin payment post type object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_filter( 'request', [ $this, 'request' ] );

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', [ $this, 'sortable_columns' ] );
		add_filter( 'list_table_primary_column', [ $this, 'primary_column' ], 10, 2 );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );

		add_action( 'load-post.php', [ $this, 'maybe_process_payment_action' ] );
		add_action( 'load-post.php', [ $this, 'maybe_display_anonymized_notice' ] );

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );

		add_filter( 'default_hidden_columns', [ $this, 'default_hidden_columns' ] );

		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );

		// Bulk Actions.
		new AdminPaymentBulkActions();
	}

	/**
	 * Filters and sorting handler.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.13/includes/admin/class-wc-admin-post-types.php#L1585-L1596
	 *
	 * @param  array $vars Request variables.
	 * @return array
	 */
	public function request( $vars ) {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return $vars;
		}

		// Check payment post type.
		if ( self::POST_TYPE !== $screen->post_type ) {
			return $vars;
		}

		// Check post status var.
		if ( isset( $vars['post_status'] ) && ! empty( $vars['post_status'] ) ) {
			return $vars;
		}

		// Set request post status from payment states.
		$vars['post_status']   = array_keys( PaymentPostType::get_payment_states() );
		$vars['post_status'][] = 'publish';

		return $vars;
	}

	/**
	 * Maybe process payment action.
	 *
	 * @return void
	 */
	public function maybe_process_payment_action() {
		if ( ! current_user_can( 'edit_payments' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( null === $screen ) {
			return;
		}

		if ( ! ( 'post' === $screen->base && 'pronamic_payment' === $screen->post_type ) ) {
			return;
		}

		$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		$payment = get_pronamic_payment( $post_id );

		if ( null === $payment ) {
			return;
		}

		if ( filter_has_var( INPUT_GET, 'pronamic_pay_check_status' ) && check_admin_referer( 'pronamic_payment_check_status_' . $post_id ) ) {
			try {
				Plugin::update_payment( $payment, false );
			} catch ( \Exception $e ) {
				Plugin::render_exception( $e );

				exit;
			}

			$this->admin_notices[] = [
				'type'    => 'info',
				'message' => __( 'Payment status updated.', 'pronamic_ideal' ),
			];
		}
	}

	/**
	 * Maybe display anonymized notice.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_current_screen/
	 * @return void
	 */
	public function maybe_display_anonymized_notice() {
		if ( ! \current_user_can( 'edit_payments' ) ) {
			return;
		}

		$screen = \get_current_screen();

		if ( null === $screen || 'post' !== $screen->base || 'pronamic_payment' !== $screen->post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This check is for display purposes only, no data modification occurs.
		if ( ! \array_key_exists( 'post', $_GET ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This check is for display purposes only, no data modification occurs.
		$post_id = \absint( \wp_unslash( $_GET['post'] ) );

		$payment = new Payment( $post_id );

		if ( ! $payment->is_anonymized() ) {
			return;
		}

		$this->admin_notices[] = [
			'type'    => 'info',
			'message' => \__( 'This payment has been anonymized. Personal details are not available anymore.', 'pronamic_ideal' ),
		];
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		foreach ( $this->admin_notices as $notice ) {
			printf(
				'<div class="notice notice-%1$s"><p>%2$s</p></div>',
				esc_attr( $notice['type'] ),
				esc_html( $notice['message'] )
			);
		}
	}

	/**
	 * Columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$columns = [
			'cb'                            => '<input type="checkbox" />',
			'pronamic_payment_status'       => sprintf(
				'<span class="pronamic-pay-tip pronamic-pay-icon" title="%s">%s</span>',
				esc_html__( 'Status', 'pronamic_ideal' ),
				esc_html__( 'Status', 'pronamic_ideal' )
			),
			'pronamic_payment_subscription' => sprintf(
				'<span class="pronamic-pay-tip pronamic-pay-icon pronamic-pay-icon-recurring" title="%s">%s</span>',
				esc_html__( 'Subscription', 'pronamic_ideal' ),
				esc_html__( 'Subscription', 'pronamic_ideal' )
			),
			'pronamic_payment_method'       => '',
			'pronamic_payment_title'        => __( 'Payment', 'pronamic_ideal' ),
			'pronamic_payment_transaction'  => __( 'Transaction', 'pronamic_ideal' ),
			'pronamic_payment_gateway'      => __( 'Gateway', 'pronamic_ideal' ),
			'pronamic_payment_description'  => __( 'Description', 'pronamic_ideal' ),
			'pronamic_payment_customer'     => __( 'Customer', 'pronamic_ideal' ),
			'pronamic_payment_amount'       => __( 'Amount', 'pronamic_ideal' ),
			'pronamic_payment_date'         => __( 'Date', 'pronamic_ideal' ),
		];

		return $columns;
	}

	/**
	 * Default hidden columns.
	 *
	 * @param array $hidden Default hidden columns.
	 * @return array
	 */
	public function default_hidden_columns( $hidden ) {
		$hidden[] = 'pronamic_payment_gateway';
		$hidden[] = 'pronamic_payment_description';

		return $hidden;
	}

	/**
	 * Sortable columns.
	 *
	 * @param array $sortable_columns Sortable columns.
	 * @return array
	 */
	public function sortable_columns( $sortable_columns ) {
		$sortable_columns['pronamic_payment_title'] = 'ID';
		$sortable_columns['pronamic_payment_date']  = 'date';

		return $sortable_columns;
	}

	/**
	 * Primary column name.
	 *
	 * @param string $column_name Primary column name.
	 * @param string $screen_id   Screen ID.
	 *
	 * @return string
	 */
	public function primary_column( $column_name, $screen_id ) {
		if ( 'edit-pronamic_payment' !== $screen_id ) {
			return $column_name;
		}

		return 'pronamic_payment_title';
	}

	/**
	 * Custom columns.
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_$post_type_posts_custom_column
	 * @link https://developer.wordpress.org/reference/functions/get_post_status/
	 * @link https://developer.wordpress.org/reference/functions/get_post_status_object/
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function custom_columns( $column, $post_id ) {
		$payment = get_pronamic_payment( $post_id );

		if ( null === $payment ) {
			return;
		}

		switch ( $column ) {
			case 'pronamic_payment_status':
				$post_status = get_post_status( $post_id );

				if ( false === $post_status ) {
					break;
				}

				$label = __( 'Unknown', 'pronamic_ideal' );

				if ( 'trash' === $post_status ) {
					$post_status = get_post_meta( $post_id, '_wp_trash_meta_status', true );
				}

				$status_object = get_post_status_object( $post_status );

				if ( isset( $status_object, $status_object->label ) ) {
					$label = $status_object->label;
				}

				printf(
					'<span class="pronamic-pay-tip pronamic-pay-icon %s" title="%s">%s</span>',
					esc_attr( AdminModule::get_post_status_icon_class( $post_status ) ),
					esc_attr( $label ),
					esc_html( $label )
				);

				break;
			case 'pronamic_payment_subscription':
				$subscriptions = $payment->get_subscriptions();

				foreach ( $subscriptions as $subscription ) {
					$label = __( 'Recurring payment', 'pronamic_ideal' );
					$class = 'pronamic-pay-icon-recurring';

					if ( $subscription->is_first_payment( $payment ) ) {
						$label = __( 'First of recurring payment', 'pronamic_ideal' );
						$class = ' pronamic-pay-icon-recurring-first';
					}

					edit_post_link(
						sprintf(
							'<span class="pronamic-pay-tip pronamic-pay-icon %s" title="%s">%s</span>',
							esc_attr( $class ),
							esc_attr( $label ),
							esc_attr( $label )
						),
						'',
						'',
						(int) $subscription->get_id()
					);
				}

				break;
			case 'pronamic_payment_method':
				$payment_method = $payment->get_payment_method();

				$icon_url = PaymentMethods::get_icon_url( $payment_method );

				if ( null !== $icon_url ) {
					\printf(
						'<span class="pronamic-pay-tip" title="%2$s"><img src="%1$s" alt="%2$s" title="%2$s" width="32" valign="bottom" /></span> ',
						\esc_url( $icon_url ),
						\esc_attr( (string) PaymentMethods::get_name( $payment_method ) )
					);
				}

				break;
			case 'pronamic_payment_title':
				$source_id          = $payment->get_source_id();
				$source_description = $payment->get_source_description();

				// Post ID text.
				$text = sprintf(
					'<strong>#%s</strong>',
					esc_html( strval( $post_id ) )
				);

				$link = get_edit_post_link( $post_id );

				if ( null !== $link ) {
					$text = sprintf(
						'<a href="%s" class="row-title">%s</a>',
						esc_url( $link ),
						$text
					);
				}

				// Source text.
				$source_id_text = '';

				if ( null !== $source_id ) {
					$source_id_text = '#' . strval( $source_id );
				}

				$source_link = $payment->get_source_link();

				if ( null !== $source_link ) {
					$source_id_text = sprintf(
						'<a href="%s">%s</a>',
						esc_url( $source_link ),
						$source_id_text
					);
				}

				// Output.
				echo wp_kses(
					sprintf(
						/* translators: 1: edit post link with post ID, 2: source description, 3: source ID text */
						__( '%1$s for %2$s %3$s', 'pronamic_ideal' ),
						$text,
						$source_description,
						$source_id_text
					),
					[
						'a'      => [
							'href'  => true,
							'class' => true,
						],
						'strong' => [],
					]
				);

				break;
			case 'pronamic_payment_gateway':
				$config_id = (int) $payment->get_config_id();

				$gateway_id = get_post_meta( $config_id, '_pronamic_gateway_id', true );

				$integration = $this->plugin->gateway_integrations->get_integration( $gateway_id );

				if ( null !== $integration ) {
					printf(
						'<a href="%1$s" title="%2$s">%2$s</a>',
						\esc_url( (string) \get_edit_post_link( $config_id ) ),
						\esc_html( \get_the_title( $config_id ) )
					);
				} elseif ( ! empty( $config_id ) ) {
					echo esc_html( get_the_title( $config_id ) );
				} else {
					echo '—';
				}

				break;
			case 'pronamic_payment_transaction':
				$transaction_id = get_post_meta( $post_id, '_pronamic_payment_transaction_id', true );
				$transaction_id = strval( $transaction_id );

				$url = $payment->get_provider_link();

				if ( empty( $url ) ) {
					echo esc_html( $transaction_id );
				} else {
					printf(
						'<a href="%s">%s</a>',
						esc_url( $url ),
						esc_html( $transaction_id )
					);
				}

				break;
			case 'pronamic_payment_description':
				echo esc_html( (string) $payment->get_description() );

				break;
			case 'pronamic_payment_amount':
				$total_amount = $payment->get_total_amount();

				$remaining_amount = $total_amount;

				$tip = [];

				$refunded_amount = $payment->get_refunded_amount();

				if ( ! $refunded_amount->is_zero() ) {
					$remaining_amount = $remaining_amount->subtract( $refunded_amount );

					$tip[] = \sprintf(
						/* translators: %s: formatted amount */
						__( '%s refunded', 'pronamic_ideal' ),
						$refunded_amount->format_i18n()
					);
				}

				$charged_back_amount = $payment->get_charged_back_amount();

				if ( null !== $charged_back_amount ) {
					$remaining_amount = $remaining_amount->subtract( $charged_back_amount );

					$tip[] = \sprintf(
						/* translators: %s: formatted amount */
						__( '%s charged back', 'pronamic_ideal' ),
						$charged_back_amount->format_i18n()
					);
				}

				// Check refunded amount.
				if ( $total_amount->get_value() === $remaining_amount->get_value() ) {
					echo esc_html( $total_amount->format_i18n() );

					break;
				}

				// Show original amount and remaining amount.
				\printf(
					'<del class="pronamic-pay-tip" data-toggle="tooltip" data-placement="top" title="%3$s">%1$s</del> %2$s',
					esc_html( $total_amount->format_i18n() ),
					\esc_html( $remaining_amount->format_i18n() ),
					\esc_html( implode( ', ', $tip ) )
				);

				break;
			case 'pronamic_payment_date':
				echo esc_html( $payment->date->format_i18n() );

				break;
			case 'pronamic_payment_customer':
				$customer = $payment->get_customer();

				if ( null !== $customer ) {
					$text = \strval( $customer->get_name() );

					if ( empty( $text ) ) {
						$text = \strval( $customer->get_email() );
					}

					echo \esc_html( $text );
				}

				break;
		}
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
			'pronamic_payment',
			__( 'Payment', 'pronamic_ideal' ),
			[ $this, 'meta_box_info' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_payment_lines',
			__( 'Payment Lines', 'pronamic_ideal' ),
			[ $this, 'meta_box_lines' ],
			$post_type,
			'normal',
			'high'
		);

		$modules = \apply_filters( 'pronamic_pay_modules', [] );

		if ( \in_array( 'subscriptions', $modules, true ) ) {
			\add_meta_box(
				'pronamic_payment_subscription',
				\__( 'Subscription', 'pronamic_ideal' ),
				[ $this, 'meta_box_subscription' ],
				$post_type,
				'normal',
				'high'
			);
		}

		\add_meta_box(
			'pronamic_payment_refunds',
			\__( 'Refunds', 'pronamic_ideal' ),
			[ $this, 'meta_box_refunds' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_payment_notes',
			__( 'Notes', 'pronamic_ideal' ),
			[ $this, 'meta_box_notes' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_payment_update',
			__( 'Update', 'pronamic_ideal' ),
			[ $this, 'meta_box_update' ],
			$post_type,
			'side',
			'high'
		);

		// @link http://kovshenin.com/2012/how-to-remove-the-publish-box-from-a-post-type/.
		remove_meta_box( 'submitdiv', $post_type, 'side' );
	}

	/**
	 * Pronamic Pay gateway config meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_info( $post ) {
		$plugin  = $this->plugin;
		$payment = get_pronamic_payment( $post->ID );

		if ( null === $payment ) {
			return;
		}

		include __DIR__ . '/../../views/meta-box-payment-info.php';
	}

	/**
	 * Pronamic Pay payment lines meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_lines( $post ) {
		$payment = get_pronamic_payment( $post->ID );

		if ( null === $payment ) {
			return;
		}

		$lines = $payment->get_lines();

		include __DIR__ . '/../../views/meta-box-payment-lines.php';
	}

	/**
	 * Pronamic Pay payment refunds meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_refunds( $post ) {
		$payment = get_pronamic_payment( $post->ID );

		if ( null === $payment ) {
			return;
		}

		include __DIR__ . '/../../views/meta-box-payment-refunds.php';
	}

	/**
	 * Pronamic Pay gateway config meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_notes( $post ) {
		$notes = get_comments(
			[
				'post_id' => $post->ID,
				'type'    => 'payment_note',
				'orderby' => [ 'comment_date_gmt', 'comment_ID' ],
			]
		);

		include __DIR__ . '/../../views/meta-box-notes.php';
	}

	/**
	 * Pronamic Pay payment subscription meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_subscription( $post ) {
		$payment = get_pronamic_payment( $post->ID );

		if ( null === $payment ) {
			return;
		}

		include __DIR__ . '/../../views/meta-box-payment-subscription.php';
	}

	/**
	 * Post row actions.
	 *
	 * @param array   $actions Actions array.
	 * @param WP_Post $post    WordPress post.
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( self::POST_TYPE === $post->post_type ) {
			return [ '' ];
		}

		return $actions;
	}

	/**
	 * Post updated messages.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 * @link https://github.com/WordPress/WordPress/blob/4.4.2/wp-admin/edit-form-advanced.php#L134-L173
	 * @link https://github.com/woothemes/woocommerce/blob/2.5.5/includes/admin/class-wc-admin-post-types.php#L111-L168
	 * @param array $messages Message.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post;

		// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352797&filters[translation_id]=37948900
		$scheduled_date = date_i18n( __( 'M j, Y @ H:i', 'pronamic_ideal' ), strtotime( $post->post_date ) );

		$messages[ self::POST_TYPE ] = [
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Payment updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352799&filters[translation_id]=37947229.
			2  => $messages['post'][2],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352800&filters[translation_id]=37947870.
			3  => $messages['post'][3],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352798&filters[translation_id]=37947230.
			4  => __( 'Payment updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352801&filters[translation_id]=37947231.
			/* phpcs:disable WordPress.Security.NonceVerification.Recommended */
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Payment restored to revision from %s.', 'pronamic_ideal' ), strval( wp_post_revision_title( (int) $_GET['revision'], false ) ) ) : false,
			/* phpcs:enable WordPress.Security.NonceVerification.Recommended */
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352802&filters[translation_id]=37949178.
			6  => __( 'Payment published.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352803&filters[translation_id]=37947232.
			7  => __( 'Payment saved.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352804&filters[translation_id]=37949303.
			8  => __( 'Payment submitted.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352805&filters[translation_id]=37949302.
			/* translators: %s: scheduled date */
			9  => sprintf( __( 'Payment scheduled for: %s.', 'pronamic_ideal' ), '<strong>' . $scheduled_date . '</strong>' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352806&filters[translation_id]=37949301.
			10 => __( 'Payment draft updated.', 'pronamic_ideal' ),
		];

		return $messages;
	}

	/**
	 * Pronamic Pay payment update meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_update( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is used in include.
		$post
	) {
		wp_nonce_field( 'pronamic_payment_update', 'pronamic_payment_update_nonce' );

		include __DIR__ . '/../../views/meta-box-payment-update.php';
	}

	/**
	 * Admin init.
	 *
	 * @return void
	 */
	public function admin_init() {
		$this->maybe_update_payment();
	}

	/**
	 * Maybe update payment status.
	 *
	 * @return void
	 */
	private function maybe_update_payment() {
		if ( ! \array_key_exists( 'pronamic_payment_update', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_payment_id', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_payment_status', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_payment_update_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_POST['pronamic_payment_update_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_payment_update' ) ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		$payment_id = \sanitize_text_field( \wp_unslash( $_POST['pronamic_payment_id'] ) );

		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		$status = \sanitize_text_field( \wp_unslash( $_POST['pronamic_payment_status'] ) );

		if ( '' === $status ) {
			return;
		}

		$payment->set_status( $status );
		$payment->save();
	}
}
