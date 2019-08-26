<?php
/**
 * Abstract Integration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay\Gateways\Common;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\GatewayConfig;

/**
 * Title: Abstract Integration
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class AbstractIntegration {
	/**
	 * ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * URL.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Product URL.
	 *
	 * @var string
	 */
	public $product_url;

	/**
	 * Dashboard URL.
	 *
	 * @var string|array
	 */
	public $dashboard_url;

	/**
	 * Provider.
	 *
	 * @var string
	 */
	public $provider;

	/**
	 * Supported features.
	 *
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Get ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param string $id ID.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string $name Name.
	 */
	public function set_name( $name ) {
		$this->name = $name;
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
		return array();
	}

	/**
	 * Get settings fields.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		return array();
	}

	/**
	 * Get dashboard URL.
	 *
	 * @return array
	 */
	public function get_dashboard_url() {
		$url = array();

		if ( isset( $this->dashboard_url ) ) {
			if ( is_string( $this->dashboard_url ) ) {
				$url = array( $this->dashboard_url );
			} elseif ( is_array( $this->dashboard_url ) ) {
				$url = $this->dashboard_url;
			}
		}

		return $url;
	}

	/**
	 * Get product URL.
	 *
	 * @return string|false
	 */
	public function get_product_url() {
		$url = false;

		if ( isset( $this->product_url ) ) {
			$url = $this->product_url;
		} elseif ( isset( $this->url ) ) {
			$url = $this->url;
		}

		return $url;
	}

	/**
	 * Get provider URL.
	 *
	 * @return string|false
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Check if this intengration supports a given feature.
	 *
	 * @param string $feature The feature to check.
	 * @return bool True if supported, false otherwise.
	 */
	public function supports( $feature ) {
		return in_array( $feature, $this->supports, true );
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
	 */
	public function save_post( $post_id ) {

	}
}
