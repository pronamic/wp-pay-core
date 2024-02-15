<?php
/**
 * Home URL Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Home URL Controller class
 */
class HomeUrlController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'admin_init', [ $this, 'admin_init' ] );

		\add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function init() {
		$option = \get_option( 'pronamic_pay_home_url', null );

		if ( null === $option ) {
			\update_option( 'pronamic_pay_home_url', \get_option( 'home' ) );
		}

		\register_setting(
			/**
			 * We deliberately use the 'pronamic_pay_home_url' option group
			 * here, as this setting is not visible to administrators. Using
			 * the 'pronamic_pay' option group will clear the setting after
			 * saving.
			 *
			 * @link https://github.com/pronamic/wp-pay-core/issues/119
			 */
			'pronamic_pay_home_url',
			'pronamic_pay_home_url',
			[
				'type'              => 'string',
				'description'       => \__( 'Home URL setting to detect changes in the WordPress home URL.', 'pronamic_ideal' ),
				'sanitize_callback' => 'sanitize_url',
				'default'           => \get_option( 'home' ),
			]
		);
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		/**
		 * We use the `get_option( 'home' )` here and not `home_url()` to
		 * bypass the `home_url` filter. The WPML plugin hooks into the
		 * `home_url` filter and this causes the notice to be displayed
		 * unnecessarily. That's why we decided to compare on the
		 * unfiltered home URL directly from the options.
		 *
		 * @link https://github.com/pronamic/wp-pay-core/issues/121
		 */
		$home_url_a = \get_option( 'home' );
		$home_url_b = \get_option( 'pronamic_pay_home_url' );

		if ( $home_url_a === $home_url_b ) {
			return;
		}

		$dismiss_notification_url = \add_query_arg( 'pronamic_pay_dismiss_home_url_change', true );
		$dismiss_notification_url = \wp_nonce_url( $dismiss_notification_url, 'pronamic_pay_dismiss_home_url_change', 'pronamic_pay_dismiss_home_url_change_nonce' );

		?>
		<div class="error notice is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?></strong> —
				<?php

				echo \esc_html(
					\sprintf(
						/* translators: 1: Pronamic Pay home URL option, 2: home URL */
						__( 'We noticed the WordPress home URL has changed from "%1$s" to "%2$s". Please verify the payment gateway settings. For example, you might want to switch between live and test mode or need to update an URL at the gateway to continue receiving payment status updates. Also keep an eye on pending payments to discover possible configuration issues.', 'pronamic_ideal' ),
						$home_url_b,
						$home_url_a
					)
				);

				?>
			</p>

			<?php

			$modules = \apply_filters( 'pronamic_pay_modules', [] );

			if ( \in_array( 'subscriptions', $modules, true ) ) {

				printf(
					'<p>%s</p>',
					\esc_html__( 'If you use subscriptions, you may want to update processing of recurring payments in the plugin debug settings to prevent duplicate payments being started in a development environment.', 'pronamic_ideal' )
				);

			}

			?>

			<p>
				<strong><a href="<?php echo \esc_url( \add_query_arg( 'post_type', 'pronamic_gateway', \get_admin_url( null, 'edit.php' ) ), ); ?>"><?php \esc_html_e( 'Payment Gateway Configurations', 'pronamic_ideal' ); ?></a></strong>
			</p>

			<a href="<?php echo \esc_url( $dismiss_notification_url ); ?>" class="notice-dismiss"><span class="screen-reader-text"><?php \esc_html_e( 'Dismiss this notice.', 'pronamic_ideal' ); ?></span></a>
		</div>
		<?php
	}

	/**
	 * Maybe dismiss notification.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/c3405cf06f7ddea3aad2185dc8541955853c2575/plugins/woocommerce/includes/admin/class-wc-admin-notices.php#L160-L181
	 * @return void
	 */
	public function admin_init() {
		if ( ! \array_key_exists( 'pronamic_pay_dismiss_home_url_change', $_GET ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_pay_dismiss_home_url_change_nonce', $_GET ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_GET['pronamic_pay_dismiss_home_url_change_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_pay_dismiss_home_url_change' ) ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'You don’t have permission to do this.', 'pronamic_ideal' ) );
		}

		$result = \update_option( 'pronamic_pay_home_url', \get_option( 'home' ) );

		if ( false === $result ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		// Redirect.
		$url = \add_query_arg(
			[
				'pronamic_pay_dismiss_home_url_change'   => false,
				'pronamic_pay_dismiss_home_url_change_nonce' => false,
				'pronamic_pay_dismissed_home_url_change' => true,
			],
			\wp_get_referer()
		);

		\wp_safe_redirect( $url );

		exit;
	}
}
