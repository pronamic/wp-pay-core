<?php
/**
 * Bootstrap tests
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

putenv( 'WP_PHPUNIT__TESTS_CONFIG=tests/wp-config.php' );

/**
 * Composer.
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * WP CLI.
 *
 * @link https://polevaultweb.com/2016/11/unit-testing-custom-wp-cli-commands/
 * @link https://github.com/polevaultweb/phpunit-wp-cli-runner
 */
if ( ! defined( 'WP_CLI_ROOT' ) ) {
	define( 'WP_CLI_ROOT', __DIR__ . '/../vendor/wp-cli/wp-cli' );
}

include WP_CLI_ROOT . '/php/class-wp-cli.php';

/**
 * WP PHPUnit.
 *
 * @link https://github.com/wp-phpunit/wp-phpunit
 */
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

/**
 * Manually load plugin.
 */
function _manually_load_plugin() {
	global $pronamic_ideal;

	$pronamic_ideal = pronamic_pay_plugin();
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Bootstrap.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
