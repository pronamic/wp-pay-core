<?php
/**
 * Period Definition Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Period Definition Test
 *
 * @author Remco Tolsma
 * @version unreleased
 */
class PeriodDefinitionTest extends \WP_UnitTestCase {
	/**
	 * New period definition.
	 *
	 * @return PeriodDefinition
	 */
	private function new_period_definition() {
		$period_definition = new PeriodDefinition( new \DateTimeImmutable(), 'Y', 5, new Money( 50, 'EUR' ) );

		return $period_definition;
	}

	/**
	 * Test date interval.
	 */
	public function test_date_interval() {
		$period_definition = $this->new_period_definition();

		$date_interval = $period_definition->get_date_interval();

		$this->assertInstanceOf( \DateInterval::class, $date_interval );
		$this->assertEquals( 5, $date_interval->y );
	}

	/**
	 * Test infinite.
	 */
	public function test_infinite() {
		$period_definition = $this->new_period_definition();

		$this->assertTrue( $period_definition->is_infinite() );

		$period_definition->set_number_recurrences( 5 );

		$this->assertFalse( $period_definition->is_infinite() );
	}

	/**
	 * Test completed.
	 */
	public function test_completed() {
		$period_definition = $this->new_period_definition();

		$this->assertFalse( $period_definition->is_completed() );

		$period_definition->set_status( 'completed' );

		$this->assertTrue( $period_definition->is_completed() );
	}

	/**
	 * Test trial.
	 */
	public function test_trial() {
		$period_definition = $this->new_period_definition();

		$this->assertFalse( $period_definition->is_trial() );

		$period_definition->set_type( 'trial' );

		$this->assertTrue( $period_definition->is_trial() );
	}

	/**
	 * Test sequence number.
	 */
	public function test_sequence_number() {
		$period_definition = $this->new_period_definition();

		$this->assertEquals( 1, $period_definition->get_sequence_number() );

		$period_definition->set_sequence_number( 3 );

		$this->assertEquals( 3, $period_definition->get_sequence_number() );
	}
}
