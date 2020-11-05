<?php
/**
 * Subscription test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use WP_UnitTestCase;

/**
 * Subscription test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 */
class SubscriptionTest extends WP_UnitTestCase {
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
	public function get_and_set_provider() {
		return array(
			array( 'set_id', 'get_id', uniqid() ),
			array( 'set_status', 'get_status', 'completed' ),
		);
	}

	/**
	 * Test set.
	 *
	 * @dataProvider set_provider
	 *
	 * @param string $set_function Setter function name.
	 * @param string $property     Property name.
	 * @param string $value        Expected value.
	 */
	public function test_set( $set_function, $property, $value ) {
		$this->setExpectedDeprecated( $set_function );

		$subscription = new Subscription();

		$subscription->$set_function( $value );

		$this->assertEquals( $value, $subscription->$property );
	}

	/**
	 * Set provider.
	 *
	 * @return array
	 */
	public function set_provider() {
		return array(
			array( 'set_consumer_name', 'consumer_name', 'John Doe' ),
			array( 'set_consumer_iban', 'consumer_iban', 'NL56 RABO 0108 6347 79' ),
			array( 'set_consumer_bic', 'consumer_bic', 'RABONL2U' ),
		);
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
	public function get_provider() {
		return array(
			array( 'key', 'get_key', uniqid() ),
			array( 'source', 'get_source', 'woocommerce' ),
			array( 'source_id', 'get_source_id', '1234' ),
			array( 'frequency', 'get_frequency', 'daily' ),
			array( 'interval', 'get_interval', '1' ),
			array( 'interval_period', 'get_interval_period', 'Y' ),
			array( 'description', 'get_description', 'Lorem ipsum dolor sit amet, consectetur.' ),
		);
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
		$subscription->set_start_date( new DateTime( '2005-05-05' ) );
		$subscription->set_end_date( new DateTime( '2005-06-05' ) );
		$subscription->set_expiry_date( new DateTime( '2010-05-05' ) );
		$subscription->set_next_payment_date( new DateTime( '2005-06-05' ) );
		$subscription->set_next_payment_delivery_date( new DateTime( '2005-06-01' ) );

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

		// Dates.
		$subscription->set_end_date( new DateTime( '2005-06-05' ) );
		$subscription->set_expiry_date( new DateTime( '2010-05-05' ) );
		$subscription->set_next_payment_date( new DateTime( '2005-06-05' ) );
		$subscription->set_next_payment_delivery_date( new DateTime( '2005-06-01' ) );

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
}
