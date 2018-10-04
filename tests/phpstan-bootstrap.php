<?php
/**
 * Bootstrap PHPStan
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\MemberPress
 */

putenv( 'WP_PHPUNIT__TESTS_CONFIG=tests/wp-config.php' );

require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
