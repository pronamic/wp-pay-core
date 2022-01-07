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
	 */
	private function removed_support_notices() {
		$items = array(
			(object) array(
				'id'          => 'removed-extension-active-event-espresso-legacy',
				'name'        => \__( 'Event Espresso 3', 'pronamic_ideal' ),
				'condition'   => \defined( '\EVENT_ESPRESSO_VERSION' ) && \version_compare( \EVENT_ESPRESSO_VERSION, '4.0.0', '<' ),
				'dismissible' => true,
				'version'     => '8',
			),
			(object) array(
				'id'          => 'removed-extension-active-s2member',
				'name'        => \__( 's2Member', 'pronamic_ideal' ),
				'condition'   => \defined( '\WS_PLUGIN__S2MEMBER_VERSION' ),
				'dismissible' => true,
				'version'     => '8',
			),
			(object) array(
				'id'          => 'removed-extension-active-wp-e-commerce',
				'name'        => \__( 'WP eCommerce', 'pronamic_ideal' ),
				'condition'   => \class_exists( '\WP_eCommerce' ),
				'dismissible' => true,
				'version'     => '8',
			),
		);

		foreach ( $items as $item ) {
			$this->removed_support_notice( $item );
		}
	}

	/**
	 * Removed support notice.
	 *
	 * @param object $item Item.
	 * @retun void
	 */
	private function removed_support_notice( $item ) {
		if ( false === $item->condition ) {
			return;
		}

		$is_dismissed = (bool) \get_user_option( 'pronamic_pay_dismissed_notification_' . $item->id, \get_current_user_id() );

		if ( true === $is_dismissed ) {
			return;
		}

		$message = \sprintf(
			'We notice that the "%1$s" plugin is active, support for the "%1$s" plugin has been removed from the Pronamic Pay plugin since version %2$s.',
			$item->name,
			$item->version
		);

		$dismiss_notification_url = \add_query_arg( 'pronamic_pay_dismiss_notification', $item->id );

		?>
		<div class="error notice is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?></strong> —
				<?php echo \esc_html( $message ); ?>
			</p>

			<a href="<?php echo \esc_url( $dismiss_notification_url ); ?>" class="notice-dismiss"><span class="screen-reader-text"><?php \esc_html_e( 'Dismiss this notice.', 'pronamic_ideal' ); ?></span></a>
		</div>
		<?php
	}
}
