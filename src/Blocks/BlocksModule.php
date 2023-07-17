<?php
/**
 * Editor Blocks.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Blocks;

use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Number\Parser as NumberParser;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Forms\FormsSource;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use WP_Error;

/**
 * Blocks
 *
 * @author  Reüel van der Steege
 * @since   2.5.0
 * @version 2.1.7
 */
class BlocksModule {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		global $wp_version;

		add_filter( 'block_categories_all', [ $this, 'block_categories' ], 10, 2 );

		if ( \version_compare( $wp_version, '5.8', '<' ) ) {
			add_filter( 'block_categories', [ $this, 'block_categories' ], 10, 2 );
		}
	}

	/**
	 * Block categories.
	 *
	 * @param array    $categories Block categories.
	 * @param \WP_Post $post       Post being loaded.
	 *
	 * @return array
	 */
	public function block_categories( $categories, $post ) {
		$categories[] = [
			'slug'  => 'pronamic-pay',
			'title' => __( 'Pronamic Pay', 'pronamic-ideal' ),
			'icon'  => null,
		];

		return $categories;
	}
}
