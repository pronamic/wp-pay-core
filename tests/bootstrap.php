<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once __DIR__ . '/../vendor/autoload.php';
} else {
	require_once __DIR__ . '/../vendor/autoload_52.php';
}

require $_tests_dir . '/includes/bootstrap.php';
