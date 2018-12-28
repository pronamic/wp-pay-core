<?php

namespace Pronamic\WordPress\Pay\Gateways\Common;

/**
 * Title: Abstract Integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.1
 * @since   1.0.0
 * @link    https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class AbstractIntegration implements IntegrationInterface {
	protected $id;

	protected $name;

	public $url;

	public $product_url;

	public $dashboard_url;

	public $provider;

	public function get_id() {
		return $this->id;
	}

	public function set_id( $id ) {
		$this->id = $id;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name( $name ) {
		$this->name = $name;
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

	public function get_product_url() {
		$url = false;

		if ( isset( $this->product_url ) ) {
			$url = $this->product_url;
		} elseif ( isset( $this->url ) ) {
			$url = $this->url;
		}

		return $url;
	}
}
