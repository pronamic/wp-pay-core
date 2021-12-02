<?php
/**
 * Subscription Phase Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\GatewayConfig;
use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\GatewayIntegrations;

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound

/**
 * Test gateway integration.
 */
class TestGatewayIntegration extends AbstractGatewayIntegration {
	/**
	 * Construct test integration.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id' => 'test',
			)
		);
	}

	/**
	 * Get gateway.
	 * 
	 * @param int $post_id Post ID.
	 * @return TestGateway
	 */
	public function get_gateway( $post_id ) {
		return new TestGateway( new TestGatewayConfig() );
	}
}

/**
 * Test gateway config.
 */
class TestGatewayConfig extends GatewayConfig {

}

/**
 * Test gateway.
 */
class TestGateway extends Gateway {
	/**
	 * Construct test gateway.
	 * 
	 * @param TestGatewayConfig $config Test gateway configuration.
	 */
	public function __construct( TestGatewayConfig $config ) {
		parent::__construct( $config );

		$this->supports[] = 'recurring';
	}
}

/**
 * Subscription Phase Test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionsFollowUpPaymentsControllerTest extends \WP_UnitTestCase {
	/**
	 * Test follow-up payment.
	 */
	public function test_follow_up_payment() {
		/**
		 * Plugin.
		 */
		$plugin = \Pronamic\WordPress\Pay\Plugin::instance();

		/**
		 * Integration.
		 */
		$plugin->gateway_integrations = new GatewayIntegrations(
			array(
				new TestGatewayIntegration(),
			)
		);

		/**
		 * Gateway.
		 */
		$config_id = $this->factory->post->create(
			array(
				'post_type'  => 'pronamic_gateway',
				'meta_input' => array(
					'_pronamic_gateway_id'   => 'test',
					'_pronamic_gateway_mode' => 'test',
				),
			)
		);

		/**
		 * Follow-up payments controller.
		 */
		$follow_up_payments_controller = new SubscriptionsFollowUpPaymentsController();

		/**
		 * Subscription.
		 */
		$subscription = new Subscription();

		$subscription->set_mode( 'test' );

		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTime( '-1 month midnight', new \DateTimeZone( 'GMT' ) ),
			new SubscriptionInterval( 'P1M' ),
			new Money( '10', 'EUR' )
		);

		$subscription->add_phase( $phase );

		$period = $subscription->new_period();

		$subscription->save();

		/**
		 * Test.
		 */
		$this->assertNotNull( $subscription->get_id() );

		/**
		 * Next date.
		 */
		$next_date = $subscription->get_next_payment_date();

		$expected = new \DateTime( 'midnight', new \DateTimeZone( 'GMT' ) );

		$this->assertEquals( $expected, $next_date );

		/**
		 * Create follow-up payment.
		 */
		$follow_up_payments_controller->create_subscription_follow_up_payment( $subscription );

		/**
		 * Next date.
		 */
		$next_date = $subscription->get_next_payment_date();

		$expected = new \DateTime( '+1 month midnight', new \DateTimeZone( 'GMT' ) );

		$this->assertEquals( $expected, $next_date );
	}
}
