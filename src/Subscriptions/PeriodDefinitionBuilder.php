<?php
/**
 * Period Definition Builder
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Money\Money;

/**
 * Period Definition Builder
 *
 * @author  Remco Tolsma
 * @version unreleased
 * @since   unreleased
 */
class PeriodDefinitionBuilder {
	private $start_date;

	private $type;

	private $number_recurrences;

	public static function new() {
		return new self();
	}

	public function with_start_date( \DateTimeImmutable $start_date ) {
		$this->start_date = $start_date;

		return $this;
	}

	public function with_type( $type ) {
		$this->type = $type;

		return $this;
	}

	public function with_number_recurrences( $number_recurrences ) {
		$this->number_recurrences = $number_recurrences;

		return $this;
	}

	public function with_amount( $amount ) {
		$this->amount = $amount;

		return $this;
	}

	public function with_interval( $interval_value, $interval_unit ) {
		$this->interval_value = $interval_value;
		$this->interval_unit  = $interval_unit;

		return $this;
	}

	public function create() {
		$period_definition = new PeriodDefinition( $this->start_date, $this->interval_unit, $this->interval_value, $this->amount );

		$period_definition->set_type( $this->type );
		$period_definition->set_number_recurrences( $this->number_recurrences );

		return $period_definition;
	}
}
