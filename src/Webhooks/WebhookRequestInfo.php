<?php
/**
 * Webhook request info class
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Webhooks;

use JsonSerializable;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Webhook request info class
 *
 * @author  Reüel van der Steege
 * @version 2.2.6
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
	 * Construct webhook request info object.
	 *
	 * @param DateTime $request_date Request date.
	 * @param string   $request_url  Request URL.
	 */
	public function __construct( DateTime $request_date, $request_url ) {
		$this->request_date = $request_date;
		$this->request_url  = $request_url;
	}

	/**
	 * Get request date.
	 *
	 * @return DateTime
	 */
	public function get_request_date() {
		return $this->request_date;
	}

	/**
	 * Get request URL.
	 *
	 * @return string
	 */
	public function get_request_url() {
		return $this->request_url;
	}

	/**
	 * Get payment.
	 *
	 * @return Payment|null
	 */
	public function get_payment() {
		return $this->payment;
	}

	/**
	 * Set payment.
	 *
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function set_payment( Payment $payment ) {
		$this->payment = $payment;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$properties = [
			'request_date' => $this->request_date->format( DATE_ATOM ),
			'request_url'  => $this->request_url,
		];

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
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->get_json();
	}

	/**
	 * Create webhook request info from object.
	 *
	 * @param mixed $json JSON.
	 *
	 * @return WebhookRequestInfo
	 *
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON does not contain `request_date` property.
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON does not contain `request_url` property.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'JSON value must be an object (%s).',
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
					\esc_html( \var_export( $json, true ) )
				)
			);
		}

		if ( ! isset( $json->request_date ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'JSON must contain `request_date` property (%s).',
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
					\esc_html( \var_export( $json, true ) )
				)
			);
		}

		if ( ! isset( $json->request_url ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'JSON must contain `request_url` property (%s).',
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
					\esc_html( \var_export( $json, true ) )
				)
			);
		}

		$request_date = new DateTime( $json->request_date );

		$webhook_request_info = new WebhookRequestInfo( $request_date, $json->request_url );

		if ( isset( $json->payment_id ) ) {
			$payment = get_pronamic_payment( $json->payment_id );

			if ( $payment ) {
				$webhook_request_info->set_payment( $payment );
			}
		}

		return $webhook_request_info;
	}
}
