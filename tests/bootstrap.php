<?php
/**
 * Bootstrap tests
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
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
 * Plugin.
 */
$plugin = \Pronamic\WordPress\Pay\Plugin::instance(
    array(
        'action_scheduler' => __DIR__ . '/../wp-content/plugins/action-scheduler/action-scheduler.php',
    )
);

$plugin->plugins_loaded();
