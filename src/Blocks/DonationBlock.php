<?php
/**
 * DonationBlock.php.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

/**
 * DonationBlock.php
 *
 * @author  Reüel van der Steege
 * @since   x.x.x
 * @version x.x.x
 */
class DonationBlock extends Block {
	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $type = 'pronamic-pay/donation';

	/**
	 * Render block on frontend.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function render_block( $args = array() ) {
		ob_start();

		echo esc_html( sprintf( 'Hello, this is a %s block!', $this->get_type() ) );

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Preview block.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function preview_block( $args = array() ) {
		ob_start();

		echo esc_html( sprintf( 'Hello, this is a %s block preview!', $this->get_type() ) );

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
