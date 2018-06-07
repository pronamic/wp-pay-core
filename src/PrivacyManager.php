<?php
/**
 * Privacy manager
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Class PrivacyManager
 *
 * @package Pronamic\WordPress\Pay
 */
class PrivacyManager {
	/**
	 * Exporters.
	 *
	 * @var array
	 */
	private $exporters = array();

	/**
	 * Erasers.
	 *
	 * @var array
	 */
	private $erasers = array();

	/**
	 * Privacy manager constructor.
	 */
	public function __construct() {
		// Filters.
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 10 );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ), 10 );
	}

	/**
	 * Register exporters.
	 *
	 * @param array $exporters Privacy exporters.
	 *
	 * @return array
	 */
	public function register_exporters( $exporters ) {
		do_action( 'pronamic_pay_privacy_register_exporters', $this );

		foreach ( $this->exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter;
		}

		return $exporters;
	}

	/**
	 * Register erasers.
	 *
	 * @param array $erasers Privacy erasers.
	 *
	 * @return array
	 */
	public function register_erasers( $erasers ) {
		do_action( 'pronamic_pay_privacy_register_erasers', $this );

		foreach ( $this->exporters as $id => $eraser ) {
			$erasers[ $id ] = $eraser;
		}

		return $erasers;
	}

	/**
	 * Add exporter.
	 *
	 * @param string $id       ID of the exporter.
	 * @param string $name     Exporter name.
	 * @param array  $callback Exporter callback.
	 */
	public function add_exporter( $id, $name, $callback ) {
		$id = 'pronamic-pay-' . $id;

		$this->exporters[ $id ] = array(
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
		);
	}

	/**
	 * Add eraser.
	 *
	 * @param string $id       ID of the eraser.
	 * @param string $name     Eraser name.
	 * @param array  $callback Eraser callback.
	 */
	public function add_eraser( $id, $name, $callback ) {
		$id = 'pronamic-pay-' . $id;

		$this->erasers[ $id ] = array(
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
		);
	}
}
