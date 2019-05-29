<?php
/**
 * Form Post Type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\Parser as MoneyParser;
use Pronamic\WordPress\Pay\Plugin;
use WP_Post;

/**
 * Form Post Type
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class FormPostType {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'pronamic_pay_form';

	/**
	 * Amount method input fixed.
	 *
	 * @var string
	 */
	const AMOUNT_METHOD_INPUT_FIXED = 'fixed';

	/**
	 * Amount method input only.
	 *
	 * @var string
	 */
	const AMOUNT_METHOD_INPUT_ONLY = 'input_only';

	/**
	 * Amount method choices only.
	 *
	 * @var string
	 */
	const AMOUNT_METHOD_CHOICES_ONLY = 'choices_only';

	/**
	 * Amount method choices and input.
	 *
	 * @var string
	 */
	const AMOUNT_METHOD_CHOICES_AND_INPUT = 'choices_and_input';

	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initializes an admin form post type object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		/**
		 * Priotiry of the initial post types function should be set to < 10.
		 *
		 * @link https://core.trac.wordpress.org/ticket/28488.
		 * @link https://core.trac.wordpress.org/changeset/29318.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/4.0/wp-includes/post.php#L167.
		 */
		add_action( 'init', array( $this, 'register_post_type' ), 0 ); // Highest priority.

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'edit_columns' ) );

		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );

		/*
		 * Add meta box, we use priority 9 to make sure it loads before Yoast SEO meta box.
		 * @link https://github.com/Yoast/wordpress-seo/blob/2.3.4/admin/class-metabox.php#L20.
		 */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 9 );

		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_post' ) );

		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
	}

	/**
	 * Register post type.
	 */
	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'label'              => __( 'Payment Forms', 'pronamic_ideal' ),
				'labels'             => array(
					'name'                     => __( 'Payment Forms', 'pronamic_ideal' ),
					'singular_name'            => __( 'Payment Form', 'pronamic_ideal' ),
					'add_new'                  => __( 'Add New', 'pronamic_ideal' ),
					'add_new_item'             => __( 'Add New Payment Form', 'pronamic_ideal' ),
					'edit_item'                => __( 'Edit Payment Form', 'pronamic_ideal' ),
					'new_item'                 => __( 'New Payment Form', 'pronamic_ideal' ),
					'all_items'                => __( 'All Payment Forms', 'pronamic_ideal' ),
					'view_item'                => __( 'View Payment Form', 'pronamic_ideal' ),
					'search_items'             => __( 'Search Payment Forms', 'pronamic_ideal' ),
					'not_found'                => __( 'No payment forms found.', 'pronamic_ideal' ),
					'not_found_in_trash'       => __( 'No payment forms found in Trash.', 'pronamic_ideal' ),
					'menu_name'                => __( 'Payment Forms', 'pronamic_ideal' ),
					'filter_items_list'        => __( 'Filter payment forms list', 'pronamic_ideal' ),
					'items_list_navigation'    => __( 'Payment forms list navigation', 'pronamic_ideal' ),
					'items_list'               => __( 'Payment forms list', 'pronamic_ideal' ),

					/*
					 * New Post Type Labels in 5.0.
					 * @link https://make.wordpress.org/core/2018/12/05/new-post-type-labels-in-5-0/
					 */
					'item_published'           => __( 'Payment form published.', 'pronamic_ideal' ),
					'item_published_privately' => __( 'Payment form published privately.', 'pronamic_ideal' ),
					'item_reverted_to_draft'   => __( 'Payment form reverted to draft.', 'pronamic_ideal' ),
					'item_scheduled'           => __( 'Payment form scheduled.', 'pronamic_ideal' ),
					'item_updated'             => __( 'Payment form updated.', 'pronamic_ideal' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_nav_menus'  => true,
				'show_in_menu'       => false,
				'show_in_admin_bar'  => false,
				'supports'           => array(
					'title',
					'revisions',
				),
				'rewrite'            => array(
					'slug' => _x( 'payment-forms', 'slug', 'pronamic_ideal' ),
				),
				'query_var'          => false,
				'capabilities'       => self::get_capabilities(),
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * Edit columns.
	 *
	 * @param array $columns Edit columns.
	 * @return array
	 */
	public function edit_columns( $columns ) {
		$columns = array(
			'cb'                              => '<input type="checkbox" />',
			'title'                           => __( 'Title', 'pronamic_ideal' ),
			'pronamic_payment_form_gateway'   => __( 'Gateway', 'pronamic_ideal' ),
			'pronamic_payment_form_payments'  => __( 'Payments', 'pronamic_ideal' ),
			'pronamic_payment_form_earnings'  => __( 'Earnings', 'pronamic_ideal' ),
			'pronamic_payment_form_shortcode' => __( 'Shortcode', 'pronamic_ideal' ),
			'date'                            => __( 'Date', 'pronamic_ideal' ),
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
		global $post;
		global $wpdb;

		switch ( $column ) {
			case 'pronamic_payment_form_gateway':
				$config_id = get_post_meta( $post_id, '_pronamic_payment_form_config_id', true );

				if ( ! empty( $config_id ) ) {
					echo esc_html( get_the_title( $config_id ) );
				} else {
					echo '—';
				}

				break;
			case 'pronamic_payment_form_payments':
				/* phpcs:ignore WordPress.DB.DirectDatabaseQuery */
				$value = $wpdb->get_var(
					$wpdb->prepare(
						"
						SELECT
							COUNT( post.ID ) AS value
						FROM
							$wpdb->posts AS post
								LEFT JOIN
							$wpdb->postmeta AS meta_amount
									ON post.ID = meta_amount.post_id AND meta_amount.meta_key = '_pronamic_payment_amount'
								LEFT JOIN
							$wpdb->postmeta AS meta_source
									ON post.ID = meta_source.post_id AND meta_source.meta_key = '_pronamic_payment_source'
								LEFT JOIN
							$wpdb->postmeta AS meta_source_id
									ON post.ID = meta_source_id.post_id AND meta_source_id.meta_key = '_pronamic_payment_source_id'
						WHERE
							post.post_type = 'pronamic_payment'
								AND
							post.post_status = 'payment_completed'
								AND
							meta_source.meta_value = 'payment_form'
								AND
							meta_source_id.meta_value = %s
						GROUP BY
							post.ID
						;
						",
						$post_id
					)
				);

				echo esc_html( number_format_i18n( $value ) );

				break;
			case 'pronamic_payment_form_earnings':
				/* phpcs:ignore WordPress.DB.DirectDatabaseQuery */
				$value = $wpdb->get_var(
					$wpdb->prepare(
						"
						SELECT
							SUM( meta_amount.meta_value ) AS value
						FROM
							$wpdb->posts AS post
								LEFT JOIN
							$wpdb->postmeta AS meta_amount
									ON post.ID = meta_amount.post_id AND meta_amount.meta_key = '_pronamic_payment_amount'
								LEFT JOIN
							$wpdb->postmeta AS meta_source
									ON post.ID = meta_source.post_id AND meta_source.meta_key = '_pronamic_payment_source'
								LEFT JOIN
							$wpdb->postmeta AS meta_source_id
									ON post.ID = meta_source_id.post_id AND meta_source_id.meta_key = '_pronamic_payment_source_id'
						WHERE
							post.post_type = 'pronamic_payment'
								AND
							post.post_status = 'payment_completed'
								AND
							meta_source.meta_value = 'payment_form'
								AND
							meta_source_id.meta_value = %s
						GROUP BY
							post.ID
						;
						",
						$post_id
					)
				);

				$money = new Money( $value, 'EUR' );

				echo esc_html( $money->format_i18n() );

				break;
			case 'pronamic_payment_form_shortcode':
				printf(
					'<input onclick="this.setSelectionRange( 0, this.value.length )" type="text" class="pronamic-pay-shortcode-input" readonly="" value="%s" />',
					esc_attr( $this->get_shortcode( $post_id ) )
				);

				break;
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
				'pronamic_payment_form_options',
				__( 'Form Options', 'pronamic_ideal' ),
				array( $this, 'meta_box_form_options' ),
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
	public function meta_box_form_options( $post ) {
		include __DIR__ . '/../../views/meta-box-form-options.php';
	}

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post( $post_id ) {
		// Check if our nonce is set.
		if ( ! filter_has_var( INPUT_POST, 'pronamic_pay_nonce' ) ) {
			return $post_id;
		}

		$nonce = filter_input( INPUT_POST, 'pronamic_pay_nonce', FILTER_SANITIZE_STRING );

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'pronamic_pay_save_form_options' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// OK, its safe for us to save the data now.
		$definition = array(
			// General.
			'_pronamic_payment_form_config_id'      => FILTER_SANITIZE_NUMBER_INT,
			'_pronamic_payment_form_button_text'    => FILTER_SANITIZE_STRING,
			'_pronamic_payment_form_amount_method'  => FILTER_SANITIZE_STRING,
			'_pronamic_payment_form_amount_choices' => array(
				'flags' => FILTER_REQUIRE_ARRAY,
			),
		);

		$data = filter_input_array( INPUT_POST, $definition );

		// Convert amount choices to cents.
		if ( isset( $data['_pronamic_payment_form_amount_choices'] ) ) {
			$money_parser = new MoneyParser();

			foreach ( $data['_pronamic_payment_form_amount_choices'] as $i => $amount ) {
				$amount = $money_parser->parse( $amount );

				$data['_pronamic_payment_form_amount_choices'][ $i ] = $amount->get_cents();
			}

			// Remove empty choices.
			$data['_pronamic_payment_form_amount_choices'] = array_filter( $data['_pronamic_payment_form_amount_choices'] );
		}

		// Update post meta data.
		pronamic_pay_update_post_meta_data( $post_id, $data );
	}

	/**
	 * Get shortcode of the specified form post ID.
	 *
	 * @param int|null $post_id Post ID.
	 * @return string
	 */
	private function get_shortcode( $post_id = null ) {
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

		$shortcode = sprintf( '[pronamic_payment_form id="%s"]', esc_attr( strval( $post_id ) ) );

		return $shortcode;
	}

	/**
	 * Post submit box miscellaneous actions.
	 */
	public function post_submitbox_misc_actions() {
		if ( self::POST_TYPE !== get_post_type() ) {
			return false;
		}

		?>
<div class="misc-pub-section">
	<label for="pronamic-pay-shortcode"><?php esc_html_e( 'Shortcode:', 'pronamic_ideal' ); ?></label>

	<input id="pronamic-pay-shortcode" class="pronamic-pay-shortcode-input" onClick="this.setSelectionRange( 0, this.value.length )" type="text" class="shortcode-input" readonly value="<?php echo esc_attr( $this->get_shortcode() ); ?>" />
</div>
		<?php
	}

	/**
	 * Get capabilities for this post type.
	 *
	 * @return array
	 */
	public static function get_capabilities() {
		return array(
			'edit_post'              => 'edit_form',
			'read_post'              => 'read_form',
			'delete_post'            => 'delete_form',
			'edit_posts'             => 'edit_forms',
			'edit_others_posts'      => 'edit_others_forms',
			'publish_posts'          => 'publish_forms',
			'read_private_posts'     => 'read_private_forms',
			'read'                   => 'read',
			'delete_posts'           => 'delete_forms',
			'delete_private_posts'   => 'delete_private_forms',
			'delete_published_posts' => 'delete_published_forms',
			'delete_others_posts'    => 'delete_others_forms',
			'edit_private_posts'     => 'edit_private_forms',
			'edit_published_posts'   => 'edit_published_forms',
			'create_posts'           => 'create_forms',
		);
	}
}
