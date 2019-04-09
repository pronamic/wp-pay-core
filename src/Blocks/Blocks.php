<?php
/**
 * Editor Blocks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @since   2.1.7
 * @version 2.1.7
 */
class Blocks {
	/**
	 * Blocks.
	 *
	 * @var Block[]
	 */
	private $blocks;

	/**
	 * Blocks constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		// Register blocks.
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		// Register blocks.
		$this->register( new FixedPricePaymentButtonBlock() );
	}

	/**
	 * Register block.
	 *
	 * @param Block $block Gutenberg block.
	 */
	public function register( Block $block ) {
		$type = $block->get_type();

		// Check if a block with this type is already registered.
		if ( ! $this->get( $type ) ) {
			$this->blocks[] = $block;

			$block->init();
		}
	}

	/**
	 * Get block by type.
	 *
	 * @param string $type Block type.
	 *
	 * @return bool|Block
	 */
	public function get( $type ) {
		if ( isset( $this->blocks[ $type ] ) ) {
			return $this->blocks[ $type ];
		}

		return false;
	}
}
