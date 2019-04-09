<?php
/**
 * Editor block.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

/**
 * Block
 *
 * @author  Reüel van der Steege
 * @since   2.1.7
 * @version 2.1.7
 */
class Block {
	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Init.
	 */
	public function init() {
		// Register block type.
		$this->register_block_type();
	}

	/**
	 * Get block type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Register block type.
	 */
	public function register_block_type() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Block arguments.
		$args = wp_parse_args(
			array(
				'render_callback' => array( $this, 'render_block' ),
			),
			$this->register_block()
		);

		// Register block type.
		register_block_type(
			$this->get_type(),
			$args
		);
	}

	/**
	 * Register block scripts and styles and return array for `register_block_type` arguments.
	 *
	 * @return array
	 */
	public function register_block() {
		return array();
	}

	/**
	 * Render block.
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return string
	 */
	public function render_block( $attributes = array() ) {
		return '';
	}

	/**
	 * Preview block.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function preview_block( $args = array() ) {
		return $this->render_block( $args );
	}
}
