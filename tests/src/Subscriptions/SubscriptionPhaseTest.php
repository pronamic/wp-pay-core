<?php
/**
 * Subscription Phase Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Subscription Phase Test
 *
 * @author Remco Tolsma
 * @version unreleased
 */
class SubscriptionPhaseTest extends \WP_UnitTestCase {
	/**
	 * New period definition.
	 *
	 * @return SubscriptionPhase
	 */
	private function new_subscription_phase() {
		$subscription_phase = new SubscriptionPhase( new \DateTimeImmutable(), 'Y', 5, new Money( 50, 'EUR' ) );

		return $subscription_phase;
	}

	/**
	 * Test date interval.
	 */
	public function test_date_interval() {
		$subscription_phase = $this->new_subscription_phase();

		$date_interval = $subscription_phase->get_date_interval();

		$this->assertInstanceOf( \DateInterval::class, $date_interval );
		$this->assertEquals( 5, $date_interval->y );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertTrue( $subscription_phase->is_infinite() );

		$subscription_phase->set_number_recurrences( 5 );

		$this->assertFalse( $subscription_phase->is_infinite() );
	}

	/**
	 * Test completed.
	 */
	public function test_completed() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertFalse( $subscription_phase->is_completed() );

		$subscription_phase->set_status( 'completed' );

		$this->assertTrue( $subscription_phase->is_completed() );
	}

	/**
	 * Test trial.
	 */
	public function test_trial() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertFalse( $subscription_phase->is_trial() );

		$subscription_phase->set_type( 'trial' );

		$this->assertTrue( $subscription_phase->is_trial() );
	}

	/**
	 * Test sequence number.
	 */
	public function test_sequence_number() {
		$subscription_phase = $this->new_subscription_phase();

		$this->assertEquals( 1, $subscription_phase->get_sequence_number() );

		$subscription_phase->set_sequence_number( 3 );

		$this->assertEquals( 3, $subscription_phase->get_sequence_number() );
	}
}
