<?php
/**
 * HTTP
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Facades;

use Pronamic\WordPress\Pay\Http\Response;
use WP_Error;

/**
 * HTTP
 *
 * @link https://laravel.com/docs/8.x/http-client
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
class Http {
	/**
	 * Result.
	 *
	 * @param array|WP_Error $result Remote request result.
	 * @return Response
	 * @throws \Exception Throw exception on request error.
	 */
	private static function result( $result ) {
		if ( $result instanceof \WP_Error ) {
			throw new \Exception( $result->get_error_message() );
		}

		return new Response( $result );
	}

	/**
	 * Request.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_request/
	 * @param string $url  URL.
	 * @param array  $args Arguments.
	 * @return Response
	 */
	public static function request( $url, $args ) {
		return self::result( \wp_remote_request( $url, $args ) );
	}

	/**
	 * GET.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_get/
	 * @param string $url  URL.
	 * @param array  $args Arguments.
	 * @return Response
	 */
	public static function get( $url, $args ) {
		return self::result( \wp_remote_get( $url, $args ) );
	}

	/**
	 * POST.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_post/
	 * @param string $url  URL.
	 * @param array  $args Arguments.
	 * @return Response
	 */
	public static function post( $url, $args ) {
		return self::result( \wp_remote_post( $url, $args ) );
	}

	/**
	 * HEAD.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_remote_head/
	 * @param string $url  URL.
	 * @param array  $args Arguments.
	 * @return Response
	 */
	public static function head( $url, $args ) {
		return self::result( \wp_remote_head( $url, $args ) );
	}
}
