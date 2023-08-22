<?php
/**
 * Bootstrap tests
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

/**
 * Composer.
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * WorDBless.
 */
\WorDBless\Load::load();

/**
 * Psalm.
 */
if ( defined( 'PSALM_VERSION' ) ) {
	return;
}

/**
 * Plugin.
 */
$pronamic_pay_plugin = \Pronamic\WordPress\Pay\Plugin::instance(
	array(
		'action_scheduler' => __DIR__ . '/../vendor/woocommerce/action-scheduler/action-scheduler.php',
	)
);

$pronamic_pay_plugin->plugins_loaded();
