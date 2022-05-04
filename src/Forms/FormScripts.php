<?php
/**
 * Form Scripts
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Forms
 */

namespace Pronamic\WordPress\Pay\Forms;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Form Scripts
 *
 * @author Remco Tolsma
 * @version 2.2.6
 * @since 3.7.0
 */
class FormScripts {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructs and initialize an form scripts object.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		/**
		 * We register the form style in the 'init' action so the style
		 * is available on the front end and admin pages. This is
		 * important for the block editor to work. According to the
		 * `_wp_scripts_maybe_doing_it_wrong` function it is allowed
		 * to register scripts in the 'init' action.
		 *
		 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
		 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-includes/script-loader.php#L2645-L2680
		 * @link https://github.com/WordPress/WordPress/blob/5.1/wp-includes/functions.wp-scripts.php#L28-L52
		 */
		add_action( 'init', [ $this, 'register' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Register.
	 *
	 * @return void
	 */
	public function register() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		wp_register_style(
			'pronamic-pay-forms',
			plugins_url( 'css/forms' . $min . '.css', dirname( dirname( __FILE__ ) ) ),
			[],
			$this->plugin->get_version()
		);
	}

	/**
	 * Enqueue.
	 *
	 * @link https://mikejolley.com/2013/12/02/sensible-script-enqueuing-shortcodes/
	 * @link http://wordpress.stackexchange.com/questions/165754/enqueue-scripts-styles-when-shortcode-is-present
	 * @return void
	 */
	public function enqueue() {
		if (
			has_shortcode( get_post_field( 'post_content' ), 'pronamic_payment_form' )
				||
			is_singular( 'pronamic_pay_form' )
		) {
			wp_enqueue_style( 'pronamic-pay-forms' );
		}
	}
}
