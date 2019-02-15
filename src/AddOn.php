<?php
/**
 * Add-on.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Add-on.
 *
 * @author  Reüel van der Steege
 * @version 2.1.6
 * @since   2.1.6
 */
class AddOn {
	/**
	 * Add-on plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Gateways.
	 *
	 * @var array
	 */
	private $gateways = array();

	/**
	 * Add-on constructor.
	 *
	 * @param string $file     Add-on plugin file.
	 */
	public function __construct( $file ) {
		$this->file = $file;

		add_action( 'plugins_loaded', array( $this, 'check_pronamic_pay' ) );
	}

	/**
	 * Check Pronamic Pay.
	 */
	public function check_pronamic_pay() {
		if ( ! function_exists( 'pronamic_pay_plugin' ) ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'notice_pronamic_pay_required' ), 11 );
	}

	/**
	 * Notice Pronamic Pay required.
	 */
	public function notice_pronamic_pay_required() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$file = dirname( $this->file ) . '/views/notice-pronamic-pay-required.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Add gateways.
	 *
	 * @param array $gateways Gateways.
	 */
	public function add_gateways( array $gateways ) {
		$this->gateways = $gateways;

		add_filter( 'pronamic_pay_gateways', array( $this, 'gateways' ) );
	}

	/**
	 * Filter gateways.
	 *
	 * @param array $gateways Gateways.
	 *
	 * @return array
	 */
	public function gateways( array $gateways ) {
		$gateways = array_merge( $gateways, $this->gateways );

		return $gateways;
	}
}
