<?php
/**
 * Bootstrap tests
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

/**
 * Give access to tests_add_filter() function.
 *
 * @link https://github.com/wp-phpunit/example-plugin/blob/master/tests/bootstrap.php
 */
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

/**
 * Psalm.
 */
if ( defined( 'PSALM_VERSION' ) ) {
	return;
}

/**
 * SQLite integration.
 *
 * @link https://github.com/WordPress/sqlite-database-integration/issues/7#issuecomment-1646660980
 * @link https://github.com/wp-phpunit/example-plugin/blob/master/tests/bootstrap.php
 * @link https://github.com/WordPress/wordpress-playground/blob/23c0fc6aae5d090a14d352160c34d39988167406/packages/playground/wordpress/build/Dockerfile#L25-L42
 */
if ( ! is_dir( __DIR__ . '/../wordpress/wp-content/' ) ) {
	// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
	mkdir( __DIR__ . '/../wordpress/wp-content/' );
}

$db_dropin_file = __DIR__ . '/../wordpress/wp-content/db.php';

if ( ! is_file( $db_dropin_file ) ) {
	// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
	file_put_contents(
		$db_dropin_file,
		str_replace(
			[
				'{SQLITE_IMPLEMENTATION_FOLDER_PATH}',
				'{SQLITE_PLUGIN}',
			],
			[
				__DIR__ . '/../vendor/wordpress/sqlite-database-integration',
				'sqlite-database-integration/load.php',
			],
			file_get_contents( __DIR__ . '/../vendor/wordpress/sqlite-database-integration/db.copy' )
		)
	);
}

/**
 * Plugin.
 */
tests_add_filter(
	'muplugins_loaded',
	function () {
		require __DIR__ . '/../vendor/wordpress/sqlite-database-integration/load.php';

		require __DIR__ . '/../pronamic-pay-core.php';
	}
);

/**
 * Start up the WP testing environment.
 *
 * @link https://github.com/wp-phpunit/example-plugin/blob/master/tests/bootstrap.php
 */
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
