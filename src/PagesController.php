<?php
/**
 * Pages Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Pages Controller class
 */
class PagesController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function init() {
		// Pages.
		$pages = $this->get_pages();

		foreach ( $pages as $page ) {
			\register_setting(
				'pronamic_pay',
				$page['option_name'],
				[
					'type'              => 'integer',
					'sanitize_callback' => [ Settings::class, 'sanitize_published_post_id' ],
				]
			);
		}
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		$this->maybe_create_pages();

		\add_settings_section(
			'pronamic_pay_pages',
			__( 'Payment Status Pages', 'pronamic_ideal' ),
			[ $this, 'settings_section' ],
			'pronamic_pay'
		);

		foreach ( $this->get_pages() as $page ) {
			\add_settings_field(
				$page['option_name'],
				$page['post_title'],
				[ $this, 'input_page' ],
				'pronamic_pay',
				'pronamic_pay_pages',
				[
					'label_for' => $page['option_name'],
				]
			);
		}
	}

	/**
	 * Settings section.
	 *
	 * @return void
	 */
	public function settings_section() {
		echo '<p>';
		\esc_html_e( 'The page an user will get redirected to after payment, based on the payment status.', 'pronamic_ideal' );
		echo '</p>';

		$pages = $this->get_pages();

		$statuses = \array_map(
			function ( $page ) {
				$option_name = $page['option_name'];

				$page_id = \get_option( $option_name );

				return \get_post_status( $page_id );
			},
			$pages
		);

		if ( \in_array( false, $statuses, true ) ) {
			\submit_button(
				\__( 'Set default pages', 'pronamic_ideal' ),
				'',
				'pronamic_pay_create_pages',
				false
			);
		}
	}

	/**
	 * Input page.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_page( $args ) {
		$name = $args['label_for'];

		$selected = \get_option( $name, '' );

		if ( false === $selected ) {
			$selected = '';
		}

		\wp_dropdown_pages(
			[
				'name'             => \esc_attr( $name ),
				'post_type'        => \esc_attr( 'page' ),
				'selected'         => \esc_attr( $selected ),
				'show_option_none' => \esc_attr( \__( '— Select Page —', 'pronamic_ideal' ) ),
				'class'            => 'regular-text',
			]
		);
	}

	/**
	 * Maybe create pages.
	 *
	 * @return void
	 */
	public function maybe_create_pages() {
		if ( ! \array_key_exists( 'pronamic_pay_create_pages', $_POST ) ) {
			return;
		}

		if ( ! \check_admin_referer( 'pronamic_pay_settings', 'pronamic_pay_nonce' ) ) {
			return;
		}

		$pages = $this->get_pages();

		$url_args = [
			'page'    => 'pronamic_pay_settings',
			'message' => 'pages-generated',
		];

		try {
			$this->create_pages( $pages );
		} catch ( \Exception $e ) {
			$url_args = [
				'page'    => 'pronamic_pay_settings',
				'message' => 'pages-not-generated',
			];
		}

		$url = \add_query_arg(
			$url_args,
			admin_url( 'admin.php' )
		);

		\wp_safe_redirect( $url );

		exit;
	}

	/**
	 * Create pages.
	 *
	 * @param array $pages Pages.
	 * @return void
	 * @throws \Exception When creating page fails.
	 */
	private function create_pages( $pages ) {
		foreach ( $pages as $page ) {
			// Check if page already exists.
			$page_id = \get_option( $page['option_name'] );

			if ( false !== \get_post_status( $page_id ) ) {
				continue;
			}

			$post = [
				'post_title'     => $page['post_title'],
				'post_name'      => $page['post_name'],
				'post_content'   => $page['post_content'],
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			];

			$result = \wp_insert_post( $post, true );

			if ( $result instanceof \WP_Error ) {
				throw new \Exception( \esc_html( $result->get_error_message() ) );
			}

			\update_post_meta( $result, '_yoast_wpseo_meta-robots-noindex', true );

			\update_option( $page['option_name'], $result );
		}
	}

	/**
	 * Get pages.
	 *
	 * @return array
	 */
	public function get_pages() {
		return [
			[
				'post_title'   => \__( 'Payment Completed', 'pronamic_ideal' ),
				'post_name'    => \__( 'payment-completed', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'The payment has been successfully completed.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_completed_page_id',
			],
			[
				'post_title'   => \__( 'Payment Canceled', 'pronamic_ideal' ),
				'post_name'    => \__( 'payment-canceled', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'You have canceled the payment.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_cancel_page_id',
			],
			[
				'post_title'   => \__( 'Payment Expired', 'pronamic_ideal' ),
				'post_name'    => \__( 'payment-expired', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'Your payment session has expired.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_expired_page_id',
			],
			[
				'post_title'   => \__( 'Payment Error', 'pronamic_ideal' ),
				'post_name'    => \__( 'payment-error', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'An error has occurred during payment.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_error_page_id',
			],
			[
				'post_title'   => \__( 'Payment Status Unknown', 'pronamic_ideal' ),
				'post_name'    => \__( 'payment-unknown', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'The payment status is unknown.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_unknown_page_id',
			],
			[
				'post_title'   => \__( 'Subscription Canceled', 'pronamic_ideal' ),
				'post_name'    => \__( 'subscription-canceled', 'pronamic_ideal' ),
				'post_content' => \sprintf(
					'<p>%s</p>',
					\__( 'The subscription has been canceled.', 'pronamic_ideal' )
				),
				'option_name'  => 'pronamic_pay_subscription_canceled_page_id',
			],
		];
	}
}
