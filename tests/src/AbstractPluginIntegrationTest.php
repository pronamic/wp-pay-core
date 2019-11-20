<?php
/**
 * Abstract plugin integration test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

/**
 * Abstract plugin integration test
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class AbstractPluginIntegrationTest extends \WP_UnitTestCase {
	/**
	 * Test plugin integration.
	 */
	public function test_plugin_integration() {
		$mock = $this->getMockForAbstractClass( AbstractPluginIntegration::class );

		$this->assertNull( $mock->get_version_option_name() );

		$mock->set_version_option_name( 'pronamic_pay_restrictcontentpro_db_version' );

		$this->assertEquals( 'pronamic_pay_restrictcontentpro_db_version', $mock->get_version_option_name() );
	}
}
