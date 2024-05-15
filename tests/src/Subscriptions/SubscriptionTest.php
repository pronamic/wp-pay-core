<?php
/**
 * Subscription test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Subscription test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionTest extends TestCase {
	/**
	 * Test construct subscription object.
	 */
	public function test_construct() {
		$subscription = new Subscription();

		$this->assertInstanceOf( __NAMESPACE__ . '\Subscription', $subscription );
	}

	/**
	 * Test set and get.
	 *
	 * @dataProvider get_and_set_provider
	 *
	 * @param string $set_function Setter function name.
	 * @param string $get_function Getter function name.
	 * @param string $value        Expected value.
	 */
	public function test_set_and_get( $set_function, $get_function, $value ) {
		$subscription = new Subscription();

		$subscription->$set_function( $value );

		$this->assertEquals( $value, $subscription->$get_function() );
	}

	/**
	 * Get and set provider.
	 *
	 * @return array
	 */
	public static function get_and_set_provider() {
		return [
			[ 'set_id', 'get_id', uniqid() ],
			[ 'set_status', 'get_status', 'completed' ],
		];
	}

	/**
	 * Test get.
	 *
	 * @dataProvider get_provider
	 *
	 * @param string $property     Property name.
	 * @param string $get_function Getter function name.
	 * @param string $value        Expected value.
	 */
	public function test_get( $property, $get_function, $value ) {
		$subscription = new Subscription();

		$subscription->$property = $value;

		$this->assertEquals( $value, $subscription->$get_function() );
	}

	/**
	 * Get provider.
	 *
	 * @return array
	 */
	public static function get_provider() {
		return [
			[ 'key', 'get_key', uniqid() ],
			[ 'source', 'get_source', 'woocommerce' ],
			[ 'source_id', 'get_source_id', '1234' ],
		];
	}

	/**
	 * Test getting no subscription.
	 *
	 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/2.8.18/tests/tests-payment-class.php#L70-L79
	 */
	public function test_getting_no_subscription() {
		$subscription = new Subscription();

		$this->assertNull( $subscription->get_id() );
	}

	/**
	 * Test JSON.
	 */
	public function test_json() {
		/*
		 * Subscription.
		 */
		$subscription = new Subscription();
		$subscription->set_id( 1 );

		// Dates.
		$subscription->set_activated_at( new DateTime( '2005-05-05' ) );

		// Test.
		$json_file = __DIR__ . '/../../json/subscription.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$json_string = wp_json_encode( $subscription->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Test from object.
	 */
	public function test_from_object() {
		$json_file = __DIR__ . '/../../json/subscription.json';

		$json_data = json_decode( file_get_contents( $json_file, true ) );

		$subscription = Subscription::from_json( $json_data );

		$json_string = wp_json_encode( $subscription->get_json(), JSON_PRETTY_PRINT );

		$this->assertEquals( wp_json_encode( $json_data, JSON_PRETTY_PRINT ), $json_string );

		$this->assertJsonStringEqualsJsonFile( $json_file, $json_string );
	}

	/**
	 * Get monthly subscription.
	 *
	 * @return Subscription
	 */
	private function get_monthly_subscription() {
		// Subscription.
		$subscription = new Subscription();

		// Phase.
		$phase = new SubscriptionPhase(
			$subscription,
			new \DateTimeImmutable( '2005-05-05' ),
			new SubscriptionInterval( 'P1M' ),
			new TaxedMoney( 89.95, 'EUR' )
		);

		$subscription->add_phase( $phase );

		return $subscription;
	}

	/**
	 * Test new period.
	 */
	public function test_new_period() {
		$subscription = $this->get_monthly_subscription();

		$period = $subscription->new_period();

		$this->assertEquals( $subscription, $period->get_phase()->get_subscription() );
		$this->assertEquals( '2005-05-05', $period->get_start_date()->format( 'Y-m-d' ) );
		$this->assertEquals( '2005-06-05', $period->get_end_date()->format( 'Y-m-d' ) );
		$this->assertEquals( 89.95, $period->get_amount()->get_value() );
	}
	/**
	 * Create new subscription.
	 *
	 * @return Subscription
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	private function new_subscription() {
		$subscription = new Subscription();

		return $subscription;
	}

	/**
	 * New phase for subscription.
	 *
	 * @param Subscription $subscription Subscription.
	 * @return SubscriptionPhase
	 * @throws \Exception Throws exception on invalid date interval.
	 */
	private function new_phase( $subscription ) {
		$phase = $subscription->new_phase( new \DateTimeImmutable(), 'P1W', new TaxedMoney( 50, 'EUR' ) );

		return $phase;
	}

	/**
	 * Test new period definition.
	 */
	public function test_new_period_definition() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );

		$this->assertInstanceOf( SubscriptionPhase::class, $phase_1 );

		$phase_2 = $this->new_phase( $subscription );

		$this->assertInstanceOf( SubscriptionPhase::class, $phase_2 );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );
		$phase_2 = $this->new_phase( $subscription );

		$this->assertTrue( $subscription->is_infinite() );
	}

	/**
	 * Test current period definition.
	 */
	public function test_current_period_definition() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );
		$phase_2 = $this->new_phase( $subscription );
		$phase_3 = $this->new_phase( $subscription );

		$phase_1->set_total_periods( 1 );
		$phase_1->set_periods_created( 1 );

		$current_phase = $subscription->get_current_phase();

		$this->assertEquals( $phase_2, $current_phase );
	}

	/**
	 * Test in trial period.
	 */
	public function test_in_trial_period() {
		$subscription = $this->new_subscription();

		$phase_1 = $this->new_phase( $subscription );
		$phase_2 = $this->new_phase( $subscription );
		$phase_3 = $this->new_phase( $subscription );

		$current_phase = $subscription->get_current_phase();

		$this->assertFalse( $subscription->in_trial_period() );

		$phase_1->set_trial( true );

		$this->assertTrue( $subscription->in_trial_period() );

		$phase_1->set_total_periods( 1 );
		$phase_1->set_periods_created( 1 );

		$this->assertFalse( $subscription->in_trial_period() );
	}
}
