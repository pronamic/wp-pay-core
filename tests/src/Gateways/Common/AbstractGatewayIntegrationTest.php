<?php
/**
 * Abstract gateway integration test
 *
 * @author    Reüel van der Steege
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Common
 */

namespace Pronamic\WordPress\Pay;

/**
 * Abstract gateway integration test
 *
 * @author  Reüel van der Steege
 * @version 2.2.8
 * @since   2.2.6
 */
class AbstractGatewayIntegrationTest extends \WP_UnitTestCase {
	/**
	 * Gateway integration mock.
	 *
	 * @var AbstractGatewayIntegration
	 */
	private $integration;

	/**
	 * Setup.
	 */
	public function setUp() {
		// Abstract integration class mock.
		$this->integration = $this->getMockForAbstractClass( AbstractGatewayIntegration::class );

		$this->integration->set_manual_url( 'https://domain.tld/manuals/test/' );
	}

	/**
	 * Test manual URL.
	 */
	public function test_get_manual_url() {
		$expected = 'https://domain.tld/manuals/test/';

		$this->assertEquals( $expected, $this->integration->get_manual_url() );
	}
}
