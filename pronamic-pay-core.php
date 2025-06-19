<?php
/**
 * Plugin Name: Pronamic Pay Core
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-pay-core/
 * Description: Core components for the WordPress payment processing library.
 *
 * Version: 4.26.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 *
 * Author: Pronamic
 * Author URI: https://www.pronamic.eu/
 *
 * Text Domain: pronamic-pay-core
 * Domain Path: /languages/
 *
 * License: GPL-3.0-or-later
 *
 * GitHub URI: https://github.com/pronamic/wp-pay-core
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
add_filter(
	'pronamic_pay_modules',
	function ( $modules ) {
		$modules[] = 'forms';
		$modules[] = 'subscriptions';

		return $modules;
	}
);

\Pronamic\WordPress\Pay\Plugin::instance(
	[
		'file'             => __FILE__,
		'action_scheduler' => __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php',
	]
);

\Pronamic\WordPress\Pay\LicenseManager::instance();
