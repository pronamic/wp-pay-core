<?php
/**
 * Admin Tour
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin tour
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   1.0.0
 */
class AdminTour {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initializes an pointers object.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.2.4/wp-includes/js/wp-pointer.js
	 * @link https://github.com/WordPress/WordPress/blob/4.2.4/wp-admin/includes/template.php#L1955-L2016
	 * @link https://github.com/Yoast/wordpress-seo/blob/2.3.4/admin/class-pointers.php
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		if ( \array_key_exists( 'pronamic_pay_ignore_tour', $_GET ) && \array_key_exists( 'pronamic_pay_nonce', $_GET ) ) {
			$nonce = \sanitize_text_field( \wp_unslash( $_GET['pronamic_pay_nonce'] ) );

			if ( wp_verify_nonce( $nonce, 'pronamic_pay_ignore_tour' ) ) {
				$ignore = filter_var( $_GET['pronamic_pay_ignore_tour'], FILTER_VALIDATE_BOOLEAN );

				update_user_meta( get_current_user_id(), 'pronamic_pay_ignore_tour', $ignore );
			}
		}

		if ( ! get_user_meta( get_current_user_id(), 'pronamic_pay_ignore_tour', true ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		}
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$min = \SCRIPT_DEBUG ? '' : '.min';

		// Pointers.
		wp_register_style(
			'pronamic-pay-admin-tour',
			plugins_url( '../../css/admin-tour' . $min . '.css', __FILE__ ),
			[
				'wp-pointer',
			],
			$this->plugin->get_version()
		);

		wp_register_script(
			'pronamic-pay-admin-tour',
			plugins_url( '../../js/dist/admin-tour' . $min . '.js', __FILE__ ),
			[
				'jquery',
				'wp-pointer',
			],
			$this->plugin->get_version(),
			true
		);

		wp_localize_script(
			'pronamic-pay-admin-tour',
			'pronamicPayAdminTour',
			[
				'pointers' => $this->get_pointers(),
			]
		);

		// Enqueue.
		wp_enqueue_style( 'pronamic-pay-admin-tour' );
		wp_enqueue_script( 'pronamic-pay-admin-tour' );
	}

	/**
	 * Get pointer content.
	 *
	 * @param string $file File.
	 * @return string
	 * @throws \Exception When output buffering is not active.
	 */
	private function get_content( $pointer ) {
		$content = '';

		$path = __DIR__ . '/../../views/pointer-' . $pointer . '.php';

		if ( is_readable( $path ) ) {
			ob_start();

			$admin_tour = $this;

			include $path;

			$content = '';

			$output = ob_get_clean();

			if ( false !== $output ) {
				$content .= $output;
			}

			$content .= $this->get_navigation( $pointer );
		}

		return $content;
	}

	/**
	 * Get pointers.
	 *
	 * @return array
	 */
	private function get_pointers() {
		$pointers = [];

		$screen = get_current_screen();

		if ( null !== $screen ) {
			switch ( $screen->id ) {
				case 'toplevel_page_pronamic_ideal':
					$pointers = [
						[
							// @link https://github.com/WordPress/WordPress/blob/4.7/wp-admin/edit.php#L321
							'selector' => '.wrap h1',
							'options'  => (object) [
								'content'      => $this->get_content( 'dashboard' ),
								'position'     => (object) [
									'edge'  => 'top',
									'align' => ( is_rtl() ) ? 'left' : 'right',
								],
								'pointerWidth' => 450,
							],
						],
					];

					break;
				case 'edit-pronamic_payment':
					$pointers = [
						[
							'selector' => '.wrap .wp-header-end',
							'options'  => (object) [
								'content'      => $this->get_content( 'payments' ),
								'position'     => (object) [
									'edge'  => 'top',
									'align' => ( is_rtl() ) ? 'left' : 'right',
								],
								'pointerWidth' => 450,
							],
						],
					];

					break;
				case 'edit-pronamic_gateway':
					$pointers = [
						[
							'selector' => '.wrap .wp-header-end',
							'options'  => (object) [
								'content'      => $this->get_content( 'gateways' ),
								'position'     => (object) [
									'edge'  => 'top',
									'align' => ( is_rtl() ) ? 'left' : 'right',
								],
								'pointerWidth' => 450,
							],
						],
					];

					break;
				case 'edit-pronamic_pay_form':
					$pointers = [
						[
							'selector' => '.wrap .wp-header-end',
							'options'  => (object) [
								'content'      => $this->get_content( 'forms' ),
								'position'     => (object) [
									'edge'  => 'top',
									'align' => ( is_rtl() ) ? 'left' : 'right',
								],
								'pointerWidth' => 450,
							],
						],
					];

					break;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = \array_key_exists( 'page', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : '';

		switch ( $page ) {
			case 'pronamic_pay_settings':
				$pointers = [
					[
						'selector' => '.wrap .wp-header-end',
						'options'  => (object) [
							'content'      => $this->get_content( 'settings' ),
							'position'     => (object) [
								'edge'  => 'top',
								'align' => ( is_rtl() ) ? 'left' : 'right',
							],
							'pointerWidth' => 450,
						],
					],
				];

				break;
			case 'pronamic_pay_reports':
				$pointers = [
					[
						'selector' => '.wrap .wp-header-end',
						'options'  => (object) [
							'content'      => $this->get_content( 'reports' ),
							'position'     => (object) [
								'edge'  => 'top',
								'align' => ( is_rtl() ) ? 'left' : 'right',
							],
							'pointerWidth' => 450,
						],
					],
				];

				break;
		}

		if ( empty( $pointers ) ) {
			$pointers = [
				[
					'selector' => 'li.toplevel_page_pronamic_ideal',
					'options'  => (object) [
						'content'  => $this->get_content( 'start' ),
						'position' => (object) [
							'edge'  => 'left',
							'align' => 'center',
						],
					],
				],
			];
		}

		return $pointers;
	}

	/**
	 * Get tour close URL.
	 *
	 * @return string
	 */
	public function get_close_url() {
		return wp_nonce_url(
			add_query_arg(
				[
					'pronamic_pay_ignore_tour' => true,
				]
			),
			'pronamic_pay_ignore_tour',
			'pronamic_pay_nonce'
		);
	}

	/**
	 * Get pages.
	 * 
	 * @return string[]
	 */
	private function get_pages() {
		$modules = \apply_filters( 'pronamic_pay_modules', [] );

		$pages = [
			'dashboard' => \add_query_arg( 'page', 'pronamic_ideal', \admin_url( 'edit.php' ) ),
			'payments'  => \add_query_arg( 'post_type', 'pronamic_payment', \admin_url( 'edit.php' ) ),
			'gateways'  => \add_query_arg( 'post_type', 'pronamic_gateway', \admin_url( 'edit.php' ) ),
			'settings'  => \add_query_arg( 'page', 'pronamic_pay_settings', \admin_url( 'admin.php' ) ),
		];

		if ( \in_array( 'forms', $modules, true ) ) {
			$pages['forms'] = \add_query_arg( 'post_type', 'pronamic_pay_form', \admin_url( 'edit.php' ) );
		}

		if ( \in_array( 'reports', $modules, true ) ) {
			$pages['reports'] = \add_query_arg( 'page', 'pronamic_pay_reports', \admin_url( 'admin.php' ) );
		}

		return $pages;
	}

	/**
	 * Get navigation.
	 * 
	 * @param string $current Current page.
	 * @return string
	 */
	private function get_navigation( $current ) {
		$content = '<div class="wp-pointer-buttons pp-pointer-buttons">';

		$previous_url = $this->get_previous_page( $current );

		if ( false !== $previous_url ) {
			$content .= \sprintf(
				'<a href="%s" class="button-secondary pp-pointer-button-prev">%s</a>',
				\esc_url( $previous_url ),
				\esc_html__( 'Previous', 'pronamic_ideal' )
			);

			$content .= ' ';
		}

		$content .= '<span class="pp-pointer-buttons-right">';

		if ( 'start' === $current ) {
			$content .= \sprintf(
				'<a href="%s" class="button-primary pp-pointer-button-next">%s</a>',
				\esc_url( add_query_arg( 'page', 'pronamic_ideal', admin_url( 'admin.php' ) ) ),
				\esc_html__( 'Start tour', 'pronamic_ideal' )
			);
		}

		$next_url = $this->get_next_page( $current );

		if ( false !== $next_url ) {
			$content .= \sprintf(
				'<a href="%s" class="button-primary pp-pointer-button-next">%s</a>',
				\esc_url( $next_url ),
				\esc_html__( 'Next', 'pronamic_ideal' )
			);

			$content .= ' ';
		}

		$content .= \sprintf(
			'<a href="%s" class="button-secondary pp-pointer-button-close">%s</a>',
			\esc_url( $this->get_close_url() ),
			\esc_html__( 'Close', 'pronamic_ideal' )
		);

		$content .= '</span>';
	
		$content .= '</div>';

		return $content;
	}

	/**
	 * Get next page URL.
	 * 
	 * @param string $current Current page key.
	 * @return string|false
	 */
	private function get_next_page( $current ) {
		$pages = $this->get_pages();

		do {
			if ( \key( $pages ) === $current ) {
				return \next( $pages );
			}
		} while( \next( $pages ) );

		return false;
	}

	/**
	 * Get previous page URL.
	 * 
	 * @param string $current Current page key.
	 * @return string|false
	 */
	private function get_previous_page( $current ) {
		$pages = $this->get_pages();

		do {
			if ( \key( $pages ) === $current ) {
				return \prev( $pages );
			}
		} while( \next( $pages ) );

		return false;
	}
}
