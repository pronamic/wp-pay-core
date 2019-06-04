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

use InvalidArgumentException;
use JsonSerializable;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Webhook request info class
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class WebhookRequestInfo implements JsonSerializable {
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
	public function __construct( $request_date, $request_url, $post_data ) {
		$this->request_date = $request_date;
		$this->request_url  = $request_url;
		$this->post_data    = $post_data;
	}

	public function get_request_date() {
		return $this->request_date;
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

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 *
	 * @return object
	 */
	public function jsonSerialize() {
		return $this->get_json();
	}

	/**
	 * Create webhook request info from object.
	 *
	 * @param mixed $json JSON.
	 * @return Payment
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$request_date = new DateTime( $json->request_date );

		$webhook_request_info = new WebhookRequestInfo( $request_date, $json->request_url, $json->post_data );

		if ( isset( $json->payment_id ) ) {
			$payment = get_pronamic_payment( $json->payment_id );

			$webhook_request_info->set_payment( $payment );
		}

		return $webhook_request_info;
	}
}
