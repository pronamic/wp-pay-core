<?php
/**
 * Subscriptions follow-up payments controller test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\GatewayConfig;
use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\GatewayIntegrations;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

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
			[
				'id' => 'test',
			]
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
 * Subscriptions follow-up payments controller test.
 */
class SubscriptionsFollowUpPaymentsControllerTest extends TestCase {
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
			[
				new TestGatewayIntegration(),
			]
		);

		/**
		 * Gateway.
		 */
		$config_id = wp_insert_post(
			[
				'post_type'  => 'pronamic_gateway',
				'meta_input' => [
					'_pronamic_gateway_id'   => 'test',
					'_pronamic_gateway_mode' => 'test',
				],
			]
		);

		/**
		 * Follow-up payments controller.
		 */
		$follow_up_payments_controller = new SubscriptionsFollowUpPaymentsController();

		/**
		 * Subscription.
		 */
		$timezone = new \DateTimeZone( 'GMT' );
		$interval = new SubscriptionInterval( 'P1M' );

		$start = new \DateTimeImmutable( '-1 month midnight', $timezone );

		$subscription = new Subscription();

		$subscription->set_mode( 'test' );

		$phase = new SubscriptionPhase(
			$subscription,
			$start,
			$interval,
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

		$expected = $start->add( $interval );

		$this->assertEquals( $expected, $next_date );

		/**
		 * Create follow-up payment.
		 */
		$follow_up_payments_controller->create_subscription_follow_up_payment( $subscription );

		/**
		 * Next date.
		 */
		$next_date = $subscription->get_next_payment_date();

		$expected = $start->add( $interval )->add( $interval );

		$this->assertEquals( $expected, $next_date );
	}
}
