<?php
/**
 * HTTP Response
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Http;

/**
 * HTTP Response
 *
 * @link https://laravel.com/docs/8.x/http-client
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
class Response {
	/**
	 * Remote request response array.
	 *
	 * @var array
	 */
	private $array;

	/**
	 * Construct response array.
	 *
	 * @param array $array WordPress remote request response array.
	 */
	public function __construct( $array ) {
		$this->array = $array;
	}

	/**
	 * Status.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_retrieve_response_code/
	 * @return int|string
	 */
	public function status() {
		return \wp_remote_retrieve_response_code( $this->array );
	}

	/**
	 * Message.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_retrieve_response_message/
	 * @return string
	 */
	public function message() {
		return \wp_remote_retrieve_response_message( $this->array );
	}

	/**
	 * Body.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_retrieve_body/
	 * @return string
	 */
	public function body() {
		return \wp_remote_retrieve_body( $this->array );
	}

	/**
	 * JSON.
	 *
	 * @return mixed
	 * @throws \Exception Throw exception on empty response.
	 */
	public function json() {
		$body = $this->body();

		/**
		 * On PHP 7 or higher the `json_decode` function will return `null` and
		 * `json_last_error` will return `4` (Syntax error). On PHP 5.6 or lower
		 * the `json_decode` will also return `null`, but json_last_error` will
		 * return `0` (No error). Therefore we check if the HTTP response body
		 * is an empty string.
		 *
		 * @link https://3v4l.org/
		 */
		if ( '' === $body ) {
			throw new \Exception(
				\sprintf(
					'Response is empty, HTTP response: "%s %s".',
					\wp_remote_retrieve_response_code( $this->array ),
					\wp_remote_retrieve_response_message( $this->array )
				)
			);
		}

		// JSON.
		$data = \json_decode( $body );

		// JSON error.
		$json_error = \json_last_error();

		if ( \JSON_ERROR_NONE !== $json_error ) {
			throw new \Exception(
				\sprintf(
					'Could not JSON decode response, HTTP response: "%s %s", HTTP body length: "%d", JSON error: "%s".',
					\wp_remote_retrieve_response_code( $this->array ),
					\wp_remote_retrieve_response_message( $this->array ),
					\strlen( $body ),
					\json_last_error_msg()
				),
				$json_error
			);
		}

		return $data;
	}
}
