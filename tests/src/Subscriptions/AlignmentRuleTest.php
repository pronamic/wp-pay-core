<?php
/**
 * Alignment Rule Test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Alignment Rule Test
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   2.5.0
 */
class AlignmentRuleTest extends \WP_UnitTestCase {
	/**
	 * Test prorating rule week.
	 */
	public function test_prorating_rule_week() {
		$prorating_rule = new AlignmentRule( 'W' );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-05-12T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule week.
	 */
	public function test_prorating_rule_week_day() {
		$prorating_rule = new AlignmentRule( 'W' );

		// Friday.
		$prorating_rule->by_numeric_day_of_the_week( 5 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-05-08T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule month.
	 */
	public function test_prorating_rule_month() {
		$prorating_rule = new AlignmentRule( 'M' );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-06-05T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule month day.
	 */
	public function test_prorating_rule_month_day() {
		$prorating_rule = new AlignmentRule( 'M' );

		// The 25th day of the month.
		$prorating_rule->by_numeric_day_of_the_month( 25 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-05-25T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule month day overflow.
	 */
	public function test_prorating_rule_month_day_overflow() {
		$prorating_rule = new AlignmentRule( 'M' );

		// The 31th day of the month.
		$prorating_rule->by_numeric_day_of_the_month( 31 );

		// Friday 31 January 2020.
		$date = new \DateTimeImmutable( '2020-01-31' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-03-02T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule year.
	 */
	public function test_prorating_rule_year_specific_month_this_year() {
		$prorating_rule = new AlignmentRule( 'Y' );

		// December.
		$prorating_rule->by_numeric_month( 12 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-12-05T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule year.
	 */
	public function test_prorating_rule_year_specific_month_next_year() {
		$prorating_rule = new AlignmentRule( 'Y' );

		// January.
		$prorating_rule->by_numeric_month( 1 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2021-01-05T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule year.
	 */
	public function test_prorating_rule_year_specific_month_and_day() {
		$prorating_rule = new AlignmentRule( 'Y' );

		// On the first day of January.
		$prorating_rule->by_numeric_month( 1 );
		$prorating_rule->by_numeric_day_of_the_month( 1 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2021-01-01T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule year.
	 */
	public function test_prorating_rule_year_same_year() {
		$prorating_rule = new AlignmentRule( 'Y' );

		// On the last day of December.
		$prorating_rule->by_numeric_month( 12 );
		$prorating_rule->by_numeric_day_of_the_month( 31 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-12-31T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}

	/**
	 * Test prorating rule year.
	 */
	public function test_prorating_rule_year_same_year_month() {
		$prorating_rule = new AlignmentRule( 'Y' );

		// On the last day of May.
		$prorating_rule->by_numeric_month( 5 );
		$prorating_rule->by_numeric_day_of_the_month( 31 );

		// Tuesday 5 May 2020.
		$date = new \DateTimeImmutable( '2020-05-05' );

		$next = $prorating_rule->get_date( $date );

		$this->assertEquals( '2020-05-31T00:00:00+00:00', $next->format( DATE_ATOM ) );
	}
}
