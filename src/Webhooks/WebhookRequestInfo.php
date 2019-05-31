<?php
/**
 * Webhook request info class
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Webhooks;

use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Webhook request info class
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class WebhookRequestInfo {
	/**
	 * Date.
	 *
	 * @var DateTime
	 */
	private $request_date;

	/**
	 * Request URL.
	 *
	 * @var string
	 */
	private $request_url;

	/**
	 * Payment.
	 *
	 * @var Payment|null
	 */
	private $payment;

	/**
	 * Post data.
	 *
	 * @var string
	 */
	private $post_data;

	/**
	 * Construct webhook request info object.
	 */
	public function __construct() {
		$this->request_date = new DateTime();
		$this->request_url  = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$this->post_data    = file_get_contents( 'php://input' );
	}

	public function get_payment() {
		return $this->payment;
	}

	public function set_payment( $payment ) {
		$this->payment = $payment;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$properties = array(
			'request_date' => $this->request_date->format( DATE_ATOM ),
			'request_url'  => $this->request_url,
			'post_data'    => $this->post_data,
		);

		if ( null !== $this->payment ) {
			$properties['payment_id'] = $this->payment->get_id();
		}

		$object = (object) $properties;

		return $object;
	}
}
