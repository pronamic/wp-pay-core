<?php
/**
 * Subscription Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPeriod;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Pay\Util;
use WP_Post;
use WP_Query;

/**
 * WordPress admin subscription post type
 *
 * @author  Reüel van der Steege
 * @version 2.5.0
 * @since   1.0.0
 */
class AdminSubscriptionPostType {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'pronamic_pay_subscr';

	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

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
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE, [ $this, 'bulk_actions' ] );
		add_filter( 'list_table_primary_column', [ $this, 'primary_column' ], 10, 2 );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );

		add_action( 'load-post.php', [ $this, 'maybe_process_subscription_action' ] );

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );

		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );

		add_filter( 'removable_query_args', [ $this, 'removable_query_args' ] );

		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
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
		$vars['post_status']   = array_keys( SubscriptionPostType::get_states() );
		$vars['post_status'][] = 'publish';

		return $vars;
	}

	/**
	 * Removable query arguments.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/misc.php#L1204-L1230
	 * @link https://developer.wordpress.org/reference/functions/wp_removable_query_args/
	 * @param array $args Arguments.
	 * @return array
	 */
	public function removable_query_args( $args ) {
		$args[] = 'pronamic_payment_created';

		return $args;
	}

	/**
	 * Custom bulk actions.
	 *
	 * @link https://make.wordpress.org/core/2016/10/04/custom-bulk-actions/
	 * @link https://github.com/WordPress/WordPress/blob/4.7/wp-admin/includes/class-wp-list-table.php#L440-L452
	 * @param array $bulk_actions Bulk actions.
	 * @return array
	 */
	public function bulk_actions( $bulk_actions ) {
		// Don't allow edit in bulk.
		unset( $bulk_actions['edit'] );

		return $bulk_actions;
	}

	/**
	 * Maybe process subscription action.
	 *
	 * @return void
	 */
	public function maybe_process_subscription_action() {
		// Current user.
		if ( ! \current_user_can( 'edit_payments' ) ) {
			return;
		}

		// Screen.
		$screen = \get_current_screen();

		if ( null === $screen ) {
			return;
		}

		if ( ! ( 'post' === $screen->base && 'pronamic_pay_subscr' === $screen->post_type ) ) {
			return;
		}

		$post_id = \filter_input( \INPUT_GET, 'post', \FILTER_SANITIZE_NUMBER_INT );

		if ( false === $post_id || null === $post_id ) {
			return;
		}

		$subscription = \get_pronamic_subscription( (int) $post_id );

		if ( null === $subscription ) {
			return;
		}

		// Start payment for next period action.
		if ( \filter_input( \INPUT_GET, 'period_payment', \FILTER_VALIDATE_BOOLEAN ) && \check_admin_referer( 'pronamic_period_payment_' . $post_id ) ) {
			try {
				$sequence_number = \filter_input( INPUT_GET, 'sequence_number', \FILTER_VALIDATE_INT );

				if ( false === $sequence_number || null === $sequence_number ) {
					return;
				}

				$phase = $subscription->get_phase_by_sequence_number( $sequence_number );

				if ( null === $phase ) {
					return;
				}

				if ( ! isset( $_GET['start_date'] ) || ! isset( $_GET['end_date'] ) ) {
					return;
				}

				$start_date = new DateTimeImmutable( \sanitize_text_field( \wp_unslash( $_GET['start_date'] ) ) );
				$end_date   = new DateTimeImmutable( \sanitize_text_field( \wp_unslash( $_GET['end_date'] ) ) );

				$period = new SubscriptionPeriod( $phase, $start_date, $end_date, $phase->get_amount() );

				$payment = $period->new_payment();

				$payment->set_meta( 'mollie_sequence_type', 'recurring' );

				$payment->set_lines( $subscription->get_lines() );

				$payment = Plugin::start_payment( $payment );

				// Redirect for notice.
				$url = \add_query_arg(
					'pronamic_payment_created',
					$payment->get_id(),
					\get_edit_post_link( (int) $post_id, 'raw' )
				);

				\wp_safe_redirect( $url );

				exit;
			} catch ( \Exception $e ) {
				Plugin::render_exception( $e );

				exit;
			}
		}
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		/* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$payment_ids = \array_key_exists( 'pronamic_payment_created', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['pronamic_payment_created'] ) ) : null;

		if ( null === $payment_ids ) {
			return;
		}

		// Payment created for period.
		$payment_ids = \wp_parse_id_list( $payment_ids );

		foreach ( $payment_ids as $payment_id ) {
			$edit_post_link = \sprintf(
				/* translators: %d: payment ID */
				__( 'Payment #%d', 'pronamic_ideal' ),
				$payment_id
			);

			// Add post edit link.
			$edit_post_url = \get_edit_post_link( $payment_id );

			if ( null !== $edit_post_url ) {
				$edit_post_link = \sprintf(
					'<a href="%1$s" title="%2$s">%2$s</a>',
					\esc_url( $edit_post_url ),
					$edit_post_link
				);
			}

			// Display notice.
			\printf(
				'<div class="notice notice-info"><p>%1$s</p></div>',
				\wp_kses_post(
					\sprintf(
						/* translators: %s: payment post edit link */
						__( '%s has been created.', 'pronamic_ideal' ),
						\wp_kses_post( $edit_post_link )
					)
				)
			);
		}
	}

	/**
	 * Pre get posts.
	 *
	 * @param WP_Query $query WordPress query.
	 * @return void
	 */
	public function pre_get_posts( $query ) {
		/**
		 * The `WP_Query::get` function can return different variable type.
		 * For now this function can only handle one specific string orderby.
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_query/get/
		 * @link https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters
		 * @link https://github.com/WordPress/WordPress/blob/5.2/wp-includes/class-wp-query.php#L1697-L1713
		 */
		$orderby = $query->get( 'orderby' );

		if ( ! is_string( $orderby ) ) {
			return;
		}

		$map = [
			'pronamic_subscription_next_payment' => '_pronamic_subscription_next_payment',
		];

		if ( ! isset( $map[ $orderby ] ) ) {
			return;
		}

		$meta_key = $map[ $orderby ];

		$query->set( 'meta_key', $meta_key );
		$query->set( 'orderby', 'meta_value' );
	}

	/**
	 * Columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$columns = [
			'cb'                                 => '<input type="checkbox" />',
			'pronamic_subscription_status'       => sprintf(
				'<span class="pronamic-pay-tip pronamic-pay-icon" title="%s">%s</span>',
				esc_html__( 'Status', 'pronamic_ideal' ),
				esc_html__( 'Status', 'pronamic_ideal' )
			),
			'pronamic_subscription_method'       => '',
			'pronamic_subscription_title'        => __( 'Subscription', 'pronamic_ideal' ),
			'pronamic_subscription_customer'     => __( 'Customer', 'pronamic_ideal' ),
			'pronamic_subscription_amount'       => __( 'Amount', 'pronamic_ideal' ),
			'pronamic_subscription_recurring'    => __( 'Recurrence', 'pronamic_ideal' ),
			'pronamic_subscription_next_payment' => __( 'Next payment', 'pronamic_ideal' ),
			'pronamic_subscription_date'         => __( 'Date', 'pronamic_ideal' ),
		];

		return $columns;
	}

	/**
	 * Sortable columns.
	 *
	 * @param array $sortable_columns Sortable columns.
	 * @return array
	 */
	public function sortable_columns( $sortable_columns ) {
		$sortable_columns['pronamic_subscription_title']        = 'ID';
		$sortable_columns['pronamic_subscription_next_payment'] = 'pronamic_subscription_next_payment';
		$sortable_columns['pronamic_subscription_date']         = 'date';

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
		if ( 'edit-pronamic_pay_subscr' !== $screen_id ) {
			return $column_name;
		}

		return 'pronamic_subscription_title';
	}

	/**
	 * Custom columns.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-admin/includes/class-wp-posts-list-table.php#L1183-L1193
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function custom_columns( $column, $post_id ) {
		$subscription = get_pronamic_subscription( $post_id );

		if ( null === $subscription ) {
			return;
		}

		$phase = $subscription->get_display_phase();

		switch ( $column ) {
			case 'pronamic_subscription_status':
				$post_status = get_post_status( $post_id );

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
			case 'pronamic_subscription_method':
				$payment_method = $subscription->get_payment_method();

				$icon_url = PaymentMethods::get_icon_url( $payment_method );

				if ( null !== $icon_url ) {
					\printf(
						'<span class="pronamic-pay-tip" title="%2$s"><img src="%1$s" alt="%2$s" title="%2$s" width="32" valign="bottom" /></span> ',
						\esc_url( $icon_url ),
						\esc_attr( (string) PaymentMethods::get_name( $payment_method ) )
					);
				}

				break;
			case 'pronamic_subscription_title':
				$source_id          = $subscription->get_source_id();
				$source_description = $subscription->get_source_description();

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

				$source_id_text = '#' . strval( $source_id );

				$source_link = $subscription->get_source_link();

				if ( null !== $source_link ) {
					$source_id_text = sprintf(
						'<a href="%s">%s</a>',
						esc_url( $source_link ),
						$source_id_text
					);
				}

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
			case 'pronamic_subscription_gateway':
				$payment = get_pronamic_payment_by_meta( '_pronamic_payment_subscription_id', $post_id );

				$config_id = null;

				if ( $payment ) {
					$payment_id = $payment->get_id();

					if ( null !== $payment_id ) {
						$config_id = get_post_meta( $payment_id, '_pronamic_payment_config_id', true );
					}
				}

				echo empty( $config_id ) ? '—' : esc_html( get_the_title( $config_id ) );

				break;
			case 'pronamic_subscription_description':
				echo esc_html( get_post_meta( $post_id, '_pronamic_subscription_description', true ) );

				break;
			case 'pronamic_subscription_amount':
				echo esc_html( null === $phase ? '—' : $phase->get_amount()->format_i18n() );

				break;
			case 'pronamic_subscription_recurring':
				$total_periods = ( null === $phase ? null : $phase->get_total_periods() );

				if ( null === $phase || 1 === $total_periods ) :
					// No recurrence.
					echo '—';

				elseif ( null === $total_periods ) :
					// Infinite.
					echo esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) );

				else :
					// Fixed number of recurrences.
					printf(
						'%s<br />%s',
						esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) ),
						esc_html( strval( Util::format_frequency( $total_periods ) ) )
					);

				endif;

				break;
			case 'pronamic_subscription_next_payment':
				$next_payment_date = $subscription->get_next_payment_date();

				if ( SubscriptionStatus::ACTIVE !== $subscription->get_status() ) {
					$next_payment_date = null;
				}

				echo empty( $next_payment_date ) ? '—' : esc_html( $next_payment_date->format_i18n( \__( 'D j M Y', 'pronamic_ideal' ) ) );

				break;
			case 'pronamic_subscription_date':
				if ( null !== $subscription->date ) {
					echo esc_html( $subscription->date->format_i18n() );
				}

				break;
			case 'pronamic_subscription_customer':
				$text = get_post_meta( $post_id, '_pronamic_subscription_customer_name', true );

				$customer = $subscription->get_customer();

				if ( null !== $customer ) {
					$contact_name = $customer->get_name();

					if ( null !== $contact_name ) {
						$text = strval( $contact_name );
					}

					if ( empty( $text ) ) {
						$text = $customer->get_email();
					}
				}

				echo esc_html( $text );

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
			'pronamic_subscription',
			__( 'Subscription', 'pronamic_ideal' ),
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

		add_meta_box(
			'pronamic_subscription_phases',
			__( 'Phases', 'pronamic_ideal' ),
			[ $this, 'meta_box_phases' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_subscription_payments',
			__( 'Payments', 'pronamic_ideal' ),
			[ $this, 'meta_box_payments' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_subscription_notes',
			__( 'Notes', 'pronamic_ideal' ),
			[ $this, 'meta_box_notes' ],
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'pronamic_subscription_update',
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
	 * Pronamic Pay subscription info meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_info( $post ) {
		$plugin       = $this->plugin;
		$subscription = get_pronamic_subscription( $post->ID );

		if ( null === $subscription ) {
			return;
		}

		include __DIR__ . '/../../views/meta-box-subscription-info.php';
	}

	/**
	 * Pronamic Pay payment lines meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_lines( $post ) {
		$subscription = get_pronamic_subscription( $post->ID );

		if ( null === $subscription ) {
			return;
		}

		$lines = $subscription->get_lines();

		include __DIR__ . '/../../views/meta-box-payment-lines.php';
	}

	/**
	 * Pronamic Pay subscription notes meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_notes( $post ) {
		$notes = get_comments(
			[
				'post_id' => $post->ID,
				'type'    => 'subscription_note',
				'orderby' => [ 'comment_date_gmt', 'comment_ID' ],
			]
		);

		include __DIR__ . '/../../views/meta-box-notes.php';
	}

	/**
	 * Pronamic Pay subscription phases meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_phases( $post ) {
		$subscription = get_pronamic_subscription( $post->ID );

		if ( null === $subscription ) {
			return;
		}

		$phases = $subscription->get_phases();

		include __DIR__ . '/../../views/meta-box-subscription-phases.php';
	}

	/**
	 * Pronamic Pay subscription payments meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_payments( $post ) {
		$subscription = get_pronamic_subscription( $post->ID );

		if ( null === $subscription ) {
			return;
		}

		$plugin = $this->plugin;

		include __DIR__ . '/../../views/meta-box-subscription-payments.php';
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
			$actions = [ '' ];
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
			1  => __( 'Subscription updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352799&filters[translation_id]=37947229.
			2  => $messages['post'][2],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352800&filters[translation_id]=37947870.
			3  => $messages['post'][3],
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352798&filters[translation_id]=37947230.
			4  => __( 'Subscription updated.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352801&filters[translation_id]=37947231.
			/* phpcs:disable WordPress.Security.NonceVerification.Recommended */
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Subscription restored to revision from %s.', 'pronamic_ideal' ), strval( wp_post_revision_title( (int) $_GET['revision'], false ) ) ) : false,
			/* phpcs:enable WordPress.Security.NonceVerification.Recommended */
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352802&filters[translation_id]=37949178.
			6  => __( 'Subscription published.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352803&filters[translation_id]=37947232.
			7  => __( 'Subscription saved.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352804&filters[translation_id]=37949303.
			8  => __( 'Subscription submitted.', 'pronamic_ideal' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352805&filters[translation_id]=37949302.
			/* translators: %s: scheduled date */
			9  => sprintf( __( 'Subscription scheduled for: %s.', 'pronamic_ideal' ), '<strong>' . $scheduled_date . '</strong>' ),
			// @link https://translate.wordpress.org/projects/wp/4.4.x/admin/nl/default?filters[status]=either&filters[original_id]=2352806&filters[translation_id]=37949301.
			10 => __( 'Subscription draft updated.', 'pronamic_ideal' ),
		];

		return $messages;
	}

	/**
	 * Pronamic Pay subscription update meta box.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @return void
	 */
	public function meta_box_update( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter is used in include.
		$post
	) {
		wp_nonce_field( 'pronamic_subscription_update', 'pronamic_subscription_update_nonce' );

		include __DIR__ . '/../../views/meta-box-subscription-update.php';
	}

	/**
	 * Admin init.
	 *
	 * @return void
	 */
	public function admin_init() {
		$this->maybe_update_subscription();
	}

	/**
	 * Maybe update subscription.
	 *
	 * @return void
	 */
	private function maybe_update_subscription() {
		if ( ! \array_key_exists( 'pronamic_subscription_update', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_subscription_id', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_subscription_status', $_POST ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_subscription_update_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_POST['pronamic_subscription_update_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_subscription_update' ) ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		$subscription_id = (int) \sanitize_text_field( \wp_unslash( $_POST['pronamic_subscription_id'] ) );

		$subscription = \get_pronamic_subscription( $subscription_id );

		if ( null === $subscription ) {
			return;
		}

		$status = \sanitize_text_field( \wp_unslash( $_POST['pronamic_subscription_status'] ) );

		if ( '' !== $status ) {
			$subscription->set_status( $status );
		}

		if ( \array_key_exists( 'hidden_pronamic_pay_next_payment_date', $_POST ) && \array_key_exists( 'pronamic_subscription_next_payment_date', $_POST ) ) {
			$old_value = \sanitize_text_field( \wp_unslash( $_POST['hidden_pronamic_pay_next_payment_date'] ) );

			$new_value = \sanitize_text_field( \wp_unslash( $_POST['pronamic_subscription_next_payment_date'] ) );

			if ( ! empty( $new_value ) && $old_value !== $new_value ) {
				$new_date = new DateTimeImmutable( $new_value );

				$next_payment_date = $subscription->get_next_payment_date();

				$updated_date = null === $next_payment_date ? clone $new_date : clone $next_payment_date;

				$updated_date = $updated_date->setDate( (int) $new_date->format( 'Y' ), (int) $new_date->format( 'm' ), (int) $new_date->format( 'd' ) );

				if ( false !== $updated_date ) {
					$subscription->set_next_payment_date( $updated_date );

					$note = \sprintf(
						/* translators: %1: old formatted date, %2: new formatted date */
						\__( 'Next payment date updated from %1$s to %2$s.', 'pronamic_ideal' ),
						null === $next_payment_date ? '' : $next_payment_date->format_i18n( \__( 'D j M Y', 'pronamic_ideal' ) ),
						$updated_date->format_i18n( \__( 'D j M Y', 'pronamic_ideal' ) )
					);

					$subscription->add_note( $note );
				}
			}
		}

		$subscription->save();
	}
}
