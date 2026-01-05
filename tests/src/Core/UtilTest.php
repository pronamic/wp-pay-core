<?php
/**
 * Util test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Core;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: WordPress pay util test
 * Description:
 * Copyright: 2005-2026 Pronamic
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.1.0
 */
class UtilTest extends TestCase {
	/**
	 * Test method exists.
	 *
	 * @dataProvider status_matrix_provider
	 *
	 * @param string $class_name    Class name to check.
	 * @param string $method_name   Method name to check.
	 * @param bool   $expected Expected result.
	 */
	public function test_class_method_exists( $class_name, $method_name, $expected ) {
		$exists = Util::class_method_exists( $class_name, $method_name );

		$this->assertEquals( $expected, $exists );
	}

	/**
	 * Status matrix provider.
	 *
	 * @return array
	 */
	public static function status_matrix_provider() {
		return [
			[ __NAMESPACE__ . '\Util', 'class_method_exists', true ],
			[ 'ClassDoesNotExist', 'method_does_not_exist', false ],
			[ '', '', false ],
			[ null, null, false ],
		];
	}

	/**
	 * Test get remote address.
	 *
	 * @dataProvider server_ip_matrix_provider
	 *
	 * @param array  $server   Global $_SERVER values.
	 * @param string $expected Expected result.
	 */
	public function test_get_remote_address( $server, $expected ) {
		foreach ( $server as $key => $value ) {
			$_SERVER[ $key ] = $value;
		}

		$address = Util::get_remote_address();

		$this->assertEquals( $expected, $address );
	}

	/**
	 * Status matrix provider.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
	 * @link https://stackoverflow.com/questions/10456044/what-is-a-good-invalid-ip-address-to-use-for-unit-tests
	 *
	 * @return array
	 */
	public static function server_ip_matrix_provider() {
		return [
			[
				[
					'HTTP_X_FORWARDED_FOR' => '2001:db8:85a3:8d3:1319:8a2e:370:7348',
				],
				'2001:db8:85a3:8d3:1319:8a2e:370:7348',
			],
			[
				[
					'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
				],
				'203.0.113.195',
			],
			[
				[
					'HTTP_X_FORWARDED_FOR' => '203.0.113.195, 70.41.3.18, 150.172.238.178',
				],
				'203.0.113.195',
			],
			[
				[
					'HTTP_CLIENT_IP'       => '203.0.113.194',
					'HTTP_X_FORWARDED_FOR' => '203.0.113.195, 70.41.3.18, 150.172.238.178',
				],
				'203.0.113.194',
			],
			[
				[
					'HTTP_CLIENT_IP'           => 'invalid ip',
					'HTTP_X_FORWARDED_FOR'     => 'invalid ip',
					'HTTP_X_FORWARDED'         => 'invalid ip',
					'HTTP_X_CLUSTER_CLIENT_IP' => 'invalid ip',
					'HTTP_FORWARDED_FOR'       => 'invalid ip',
					'HTTP_FORWARDED'           => 'invalid ip',
					'REMOTE_ADDR'              => '203.0.113.195',
				],
				'203.0.113.195',
			],
			[
				[
					'HTTP_CLIENT_IP'           => 'invalid ip',
					'HTTP_X_FORWARDED_FOR'     => 'invalid ip',
					'HTTP_X_FORWARDED'         => 'invalid ip',
					'HTTP_X_CLUSTER_CLIENT_IP' => 'invalid ip',
					'HTTP_FORWARDED_FOR'       => 'invalid ip',
					'HTTP_FORWARDED'           => 'invalid ip',
					'REMOTE_ADDR'              => 'invalid ip',
				],
				null,
			],
		];
	}
}
