<?php
/**
 * Gutenberg blocks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Pay\Plugin;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @since   x.x.x
 * @version x.x.x
 */
class Blocks {
	/**
	 * Blocks.
	 *
	 * @var Block[]
	 */
	private static $blocks;

	/**
	 * Blocks constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		add_action( 'rest_api_init', array( $this, 'register_preview_route' ) );

		// Register blocks.
		self::register( new PaymentButtonBlock() );
		self::register( new DonationBlock() );
	}

	/**
	 * Register block.
	 *
	 * @param Block $block Gutenberg block.
	 */
	public static function register( Block $block ) {
		$type = $block->get_type();

		// Check if a block with this type is already registered.
		if ( isset( self::$blocks[ $type ] ) ) {
			return;
		}

		self::$blocks[] = $block;

		call_user_func( array( $block, 'init' ) );
	}

	/**
	 * Get block by type.
	 *
	 * @param string $type Block type.
	 *
	 * @return bool|Block
	 */
	public function get( $type ) {
		if ( isset( self::$blocks[ $type ] ) ) {
			return self::$blocks[ $type ];
		}

		return false;
	}

	/**
	 * Register REST API route to preview block.
	 */
	public function register_preview_route() {
		register_rest_route(
			'pronamic-pay/v1',
			'/block/preview',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_block_preview' ),
					'args'     => array(),
				),
			)
		);
	}

	/**
	 * Prepare for block preview.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function get_block_preview( $request ) {
		// Get request arguments.
		$args = $request->get_params();

		// Return error on error.
		if ( 0 ) {
			wp_send_json_error();
		}

		// Get preview HTML.
		$html = self::get( $args['type'] ) ? self::get( $args['type'] )->preview_block( $args ) : false;

		if ( $html ) {
			wp_send_json_success( array( 'html' => trim( $html ) ) );
		} else {
			wp_send_json_error();
		}
	}
}
