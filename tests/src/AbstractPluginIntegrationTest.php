<?php
/**
 * Abstract plugin integration test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Abstract plugin integration test
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.0.0
 */
class AbstractPluginIntegrationTest extends TestCase {
	/**
	 * Test plugin integration.
	 */
	public function test_plugin_integration() {
		$mock = $this->getMockForAbstractClass( AbstractGatewayIntegration::class );

		$this->assertNull( $mock->get_version_option_name() );

		$mock->set_version( '1.0.0' );
		$mock->set_version_option_name( 'pronamic_pay_restrictcontentpro_version' );

		$this->assertEquals( '1.0.0', $mock->get_version() );
		$this->assertEquals( 'pronamic_pay_restrictcontentpro_version', $mock->get_version_option_name() );

		$this->assertEquals( '', $mock->get_version_option() );

		$mock->update_version_option();

		$this->assertEquals( '1.0.0', $mock->get_version_option() );
	}
}
