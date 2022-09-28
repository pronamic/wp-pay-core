<?php
/**
 * Webhook manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Webhooks;

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Admin\AdminModule;
use WP_Query;

/**
 * Webhook manager class
 *
 * @author  Reüel van der Steege
 * @version 2.2.6
 * @since   2.1.6
 */
class WebhookManager {
	/**
	 * Construct webhook manager.
	 */
	public function __construct() {
		// Admin notices.
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	/**
	 * Admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$outdated_urls = get_transient( 'pronamic_outdated_webhook_urls' );

		if ( false === $outdated_urls ) {
			$outdated_urls = [];

			// Get gateways for which a webhook log exists.
			$query = new WP_Query(
				[
					'post_type'  => 'pronamic_gateway',
					'orderby'    => 'post_title',
					'order'      => 'ASC',
					'nopaging'   => true,
					'meta_query' => [
						[
							'key' => '_pronamic_gateway_webhook_log',
						],
					],
				]
			);

			$posts = \array_filter(
				$query->posts,
				function( $post ) {
					return ( $post instanceof \WP_Post );
				}
			);

			// Loop gateways.
			foreach ( $posts as $post ) {
				try {
					$log = get_post_meta( $post->ID, '_pronamic_gateway_webhook_log', true );

					$log = json_decode( $log );

					$request_info = WebhookRequestInfo::from_json( $log );
				} catch ( \Exception $e ) {
					continue;
				}

				// Check if manual configuration is needed for webhook.
				$gateway_id = get_post_meta( $post->ID, '_pronamic_gateway_id', true );

				$integration = pronamic_pay_plugin()->gateway_integrations->get_integration( $gateway_id );

				if ( null === $integration ) {
					// Integration unknown.
					continue;
				}

				if ( ! $integration->supports( 'webhook' ) || $integration->supports( 'webhook_no_config' ) ) {
					continue;
				}

				// Validate log request URL against current home URL.
				if ( self::validate_request_url( $request_info ) ) {
					continue;
				}

				$outdated_urls[] = $post->ID;
			}

			/**
			 * The WordPress Transients API will not always store empty array
			 * values correctly, therefore we convert an empty array to true.
			 *
			 * @todo We should probably schedule a daily event to check for
			 * possible broken webhooks.
			 */
			if ( empty( $outdated_urls ) ) {
				$outdated_urls = true;
			}

			set_transient( 'pronamic_outdated_webhook_urls', $outdated_urls, DAY_IN_SECONDS );
		}

		if ( ! empty( $outdated_urls ) ) {
			include __DIR__ . '/../../views/notice-webhook-url.php';
		}
	}

	/**
	 * Validate log URL against current home URL.
	 *
	 * @param WebhookRequestInfo $request_info Request info.
	 *
	 * @return bool
	 */
	public static function validate_request_url( WebhookRequestInfo $request_info ) {
		// Check if current home URL is the same as in the logged URL.
		$home_url = home_url( '/' );

		return substr( $request_info->get_request_url(), 0, strlen( $home_url ) ) === $home_url;
	}
}
