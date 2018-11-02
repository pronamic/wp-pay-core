<?php
/**
 * Gutenberg block.
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
 * @since   x.x.x
 * @version x.x.x
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

		// Actions.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles' ) );
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

		register_block_type(
			$this->get_type(),
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueque scripts.
	 */
	public function enqueue_scripts() {
		// Get scripts.
		$scripts = $this->scripts();

		// Make sure scripts is an array.
		if ( ! is_array( $scripts ) ) {
			return;
		}

		$defaults = array(
			'handle'       => null,
			'src'          => false,
			'dependencies' => array(),
			'version'      => pronamic_pay_plugin()->get_version(),
			'in_footer'    => false,
			'callback'     => null,
		);

		// Loop scripts.
		foreach ( $scripts as $script ) {
			$args = wp_parse_args( $script, $defaults );

			// Enqueue script.
			wp_enqueue_script( $args['handle'], $args['src'], $args['dependencies'], $args['version'], $args['in_footer'] );

			// Callback.
			if ( is_callable( $args['callback'] ) ) {
				call_user_func( $args['callback'], $args );
			}
		}
	}

	/**
	 * Scripts.
	 *
	 * @return array
	 */
	public function scripts() {
		return array();
	}

	/**
	 * Enqueque styles.
	 */
	public function enqueue_styles() {
		// Get styles.
		$styles = $this->styles();

		// Make sure styles is an array.
		if ( ! is_array( $styles ) ) {
			return;
		}

		$defaults = array(
			'handle'       => null,
			'src'          => false,
			'dependencies' => array(),
			'version'      => pronamic_pay_plugin()->get_version(),
			'media'        => 'all',
		);

		// Loop styles.
		foreach ( $styles as $style ) {
			$args = wp_parse_args( $style, $defaults );

			// Enqueue style.
			wp_enqueue_style( $args['handle'], $args['src'], $args['dependencies'], $args['version'], $args['media'] );
		}
	}

	/**
	 * Styles.
	 *
	 * @return array
	 */
	public function styles() {
		return array();
	}

	/**
	 * Render block on frontend.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function render_block( $args = array() ) {
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
		return '';
	}
}
