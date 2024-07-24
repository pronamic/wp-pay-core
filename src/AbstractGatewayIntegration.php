<?php
/**
 * Abstract gateway integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\GatewayConfig;
use Pronamic\WordPress\Pay\Core\ModeTrait;
use Pronamic\WordPress\Pay\Core\SupportsTrait;

/**
 * Title: Abstract gateway integration
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.5.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class AbstractGatewayIntegration extends AbstractIntegration {
	/**
	 * URL.
	 *
	 * @var string|null
	 */
	public $url;

	/**
	 * Product URL.
	 *
	 * @var string|null
	 */
	public $product_url;

	/**
	 * Manual URL.
	 *
	 * @var string|null
	 */
	private $manual_url;

	/**
	 * Dashboard URL.
	 *
	 * @var string|null
	 */
	public $dashboard_url;

	/**
	 * Provider.
	 *
	 * @var string
	 */
	public $provider;

	use ModeTrait;

	use SupportsTrait;

	/**
	 * Construct.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'mode'          => 'live',
				'provider'      => null,
				'url'           => null,
				'product_url'   => null,
				'dashboard_url' => null,
				'manual_url'    => null,
				'supports'      => [],
			]
		);

		parent::__construct( $args );

		// Mode.
		$this->set_mode( $args['mode'] );

		// Provider.
		$this->provider = $args['provider'];

		// URL's.
		$this->url           = $args['url'];
		$this->product_url   = $args['product_url'];
		$this->dashboard_url = $args['dashboard_url'];
		$this->manual_url    = $args['manual_url'];

		// Supports.
		$this->supports = $args['supports'];
	}

	/**
	 * Get provider.
	 *
	 * @return string
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * Get required settings for this integration.
	 *
	 * @link https://github.com/wp-premium/gravityforms/blob/1.9.16/includes/fields/class-gf-field-multiselect.php#L21-L42
	 * @return array
	 */
	public function get_settings() {
		return [];
	}

	/**
	 * Get settings fields.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		return [];
	}

	/**
	 * Get dashboard URL.
	 *
	 * @return string|null
	 */
	public function get_dashboard_url() {
		return $this->dashboard_url;
	}

	/**
	 * Get product URL.
	 *
	 * @return string|null
	 */
	public function get_product_url() {
		$url = null;

		if ( isset( $this->product_url ) ) {
			$url = $this->product_url;
		} elseif ( isset( $this->url ) ) {
			$url = $this->url;
		}

		return $url;
	}

	/**
	 * Get manual URL.
	 *
	 * @return string|null
	 */
	public function get_manual_url() {
		return $this->manual_url;
	}

	/**
	 * Set manual URL.
	 *
	 * @param string|null $manual_url Manual URL.
	 * @return void
	 */
	public function set_manual_url( $manual_url ) {
		$this->manual_url = $manual_url;
	}

	/**
	 * Get provider URL.
	 *
	 * @return string|null
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get meta value.
	 *
	 * @since 2.0.8
	 *
	 * @param string|int $post_id Post ID.
	 * @param string     $key     Shortened meta key.
	 *
	 * @return string
	 */
	protected function get_meta( $post_id, $key ) {
		if ( empty( $post_id ) || empty( $key ) ) {
			return '';
		}

		$post_id = intval( $post_id );

		$meta_key = sprintf( '_pronamic_gateway_%s', $key );

		// Get post meta.
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		if ( false === $meta_value ) {
			$meta_value = '';
		}

		return $meta_value;
	}

	/**
	 * Get config by post ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return GatewayConfig|null
	 */
	public function get_config( $post_id ) {
		return null;
	}

	/**
	 * Get gateway.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return Gateway|null
	 */
	public function get_gateway( $post_id ) {
		return null;
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
	}
}
