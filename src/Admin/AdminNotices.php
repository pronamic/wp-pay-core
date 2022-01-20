<?php
/**
 * Admin Notices
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin notices
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 3.7.0
 */
class AdminNotices {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initializes an notices object.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.4.3/includes/admin/class-wc-admin-notices.php
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 11 );
	}

	/**
	 * Admin notices.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/4.3.1/wp-admin/admin-header.php#L245-L250
	 * @return void
	 */
	public function admin_notices() {
		// Show notices only to options managers (administrators).
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Jetpack.
		$screen = get_current_screen();

		if ( null !== $screen && 'jetpack' === $screen->parent_base ) {
			return;
		}

		// License notice.
		if ( 'valid' !== get_option( 'pronamic_pay_license_status' ) ) {
			$class = Plugin::get_number_payments() > 20 ? 'error' : 'updated';

			$license = get_option( 'pronamic_pay_license_key' );

			if ( '' === $license ) {
				$notice = sprintf(
					/* translators: 1: Pronamic Pay settings page URL, 2: Pronamic.eu plugin page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>, please <a href="%2$s" target="_blank">get your key at pronamic.eu</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/'
				);
			} else {
				$notice = sprintf(
					/* translators: 1: Pronamic Pay settings page URL, 2: Pronamic.eu plugin page URL, 3: Pronamic.eu account page URL */
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>. Please <a href="%2$s" target="_blank">get your key at pronamic.eu</a> or login to <a href="%3$s" target="_blank">check your license status</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/',
					'https://www.pronamic.eu/account/'
				);
			}

			printf(
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				wp_kses_post( $notice )
			);
		}

		$this->removed_support_notices();
	}

	/**
	 * Removed support notices.
	 *
	 * @link https://github.com/pronamic/wp-pronamic-pay/issues/293
	 * @return void
	 */
	private function removed_support_notices() {
		$notifications = array();

		/**
		 * Filters the removed extensions notifications.
		 *
		 * @param AdminNotification[] $notifications Notifications for removed extensions.
		 */
		$notifications = \apply_filters( 'pronamic_pay_removed_extension_notifications', $notifications );

		foreach ( $notifications as $notification ) {
			$this->removed_support_notice( $notification );
		}
	}

	/**
	 * Removed support notice.
	 *
	 * @param AdminNotification $notification Notification.
	 * @return void
	 */
	private function removed_support_notice( $notification ) {
		if ( ! $notification->is_met() ) {
			return;
		}

		$is_dismissed = (bool) \get_user_option( 'pronamic_pay_dismissed_notification:' . $notification->get_id(), \get_current_user_id() );

		if ( true === $is_dismissed ) {
			return;
		}

		$dismiss_notification_url = \add_query_arg( 'pronamic_pay_dismiss_notification', $notification->get_id() );
		$dismiss_notification_url = \wp_nonce_url( $dismiss_notification_url, 'pronamic_pay_dismiss_notification:' . $notification->get_id(), 'pronamic_pay_dismiss_notification_nonce' )

		?>
		<div class="error notice is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?></strong> —
				<?php echo \esc_html( $notification->get_message() ); ?>
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
		if ( ! \array_key_exists( 'pronamic_pay_dismiss_notification', $_GET ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_pay_dismiss_notification_nonce', $_GET ) ) {
			return;
		}

		$id    = \sanitize_text_field( \wp_unslash( $_GET['pronamic_pay_dismiss_notification'] ) );
		$nonce = \sanitize_text_field( \wp_unslash( $_GET['pronamic_pay_dismiss_notification_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_pay_dismiss_notification:' . $id ) ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'You don’t have permission to do this.', 'pronamic_ideal' ) );
		}

		$result = \update_user_option( \get_current_user_id(), 'pronamic_pay_dismissed_notification:' . $id, true );

		if ( false === $result ) {
			\wp_die( \esc_html__( 'Action failed. Please refresh the page and retry.', 'pronamic_ideal' ) );
		}

		// Redirect.
		$url = \add_query_arg(
			array(
				'pronamic_pay_dismiss_notification'       => false,
				'pronamic_pay_dismiss_notification_nonce' => false,
				'pronamic_pay_dismissed_notification'     => $id,
			),
			\wp_get_referer()
		);

		\wp_safe_redirect( $url );

		exit;
	}
}
