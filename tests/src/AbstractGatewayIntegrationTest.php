<?php
/**
 * Abstract gateway integration test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay;

/**
 * Abstract gateway integration test
 *
 * @author  Remco Tolsma
 * @version 2.2.8
 * @since   2.2.8
 */
class AbstractGatewayIntegrationTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test gateway integration.
	 */
	public function test_gateway_integration() {
		$mock = $this->getMockForAbstractClass( AbstractGatewayIntegration::class );

		$this->assertNull( $mock->get_version_option_name() );
		$this->assertNull( $mock->get_db_version_option_name() );

		$mock->set_manual_url( 'https://domain.tld/manuals/test/' );
		$mock->set_version( '1.0.0' );
		$mock->set_version_option_name( 'pronamic_pay_mollie_version' );
		$mock->set_db_version_option_name( 'pronamic_pay_mollie_db_version' );

		$this->assertEquals( 'https://domain.tld/manuals/test/', $mock->get_manual_url() );
		$this->assertEquals( '1.0.0', $mock->get_version() );
		$this->assertEquals( 'pronamic_pay_mollie_version', $mock->get_version_option_name() );
		$this->assertEquals( 'pronamic_pay_mollie_db_version', $mock->get_db_version_option_name() );

		$this->assertEquals( '', $mock->get_version_option() );
		$this->assertEquals( '', $mock->get_db_version_option() );

		$mock->update_version_option();

		$this->assertEquals( '1.0.0', $mock->get_version_option() );
	}
}
