<?php
/**
 * Subscription Period
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\TaxedMoneyJsonTransformer;

/**
 * Subscription Period
 *
 * @author  Remco Tolsma
 * @version 2.4.0
 * @since   2.4.0
 */
class SubscriptionPeriod {
	/**
	 * Phase.
	 *
	 * @var SubscriptionPhase
	 */
	private $phase;

	/**
	 * The start date of this period.
	 *
	 * @var DateTime
	 */
	private $start_date;

	/**
	 * The end date of this period.
	 *
	 * @var DateTime
	 */
	private $end_date;

	/**
	 * The amount to pay for this period.
	 *
	 * @var TaxedMoney
	 */
	private $amount;

	/**
	 * Construct and initialize subscription period object.
	 *
	 * @param SubscriptionPhase $phase        Subscription phase.
	 * @param DateTime          $start_date   Start date.
	 * @param DateTime          $end_date     End date.
	 * @param TaxedMoney        $amount       Taxed amount.
	 */
	public function __construct( SubscriptionPhase $phase, DateTime $start_date, DateTime $end_date, TaxedMoney $amount ) {
		$this->phase      = $phase;
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
		$this->amount     = $amount;
	}

	/**
	 * Get phase.
	 *
	 * @return SubscriptionPhase
	 */
	public function get_phase() {
		return $this->phase;
	}

	/**
	 * Set phase.
	 *
	 * @param SubscriptionPhase $phase Phase.
	 * @return void
	 */
	public function set_phase( SubscriptionPhase $phase ) {
		$this->phase = $phase;
	}

	/**
	 * Get start date.
	 *
	 * @return DateTime
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Get end date.
	 *
	 * @return DateTime
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Get amount.
	 *
	 * @return TaxedMoney
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Is trial period?
	 *
	 * @return bool
	 */
	public function is_trial() {
		return $this->phase->is_trial();
	}

	/**
	 * From JSON.
	 *
	 * @param object $json Subscription period JSON.
	 * @return SubscriptionPeriod
	 * @throws \InvalidArgumentException Throws exception on invalid JSON.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( ! isset( $json->phase ) ) {
			throw new \InvalidArgumentException( 'Object must contain `phase` property.' );
		}

		if ( ! isset( $json->start_date ) ) {
			throw new \InvalidArgumentException( 'Object must contain `start_date` property.' );
		}

		if ( ! isset( $json->end_date ) ) {
			throw new \InvalidArgumentException( 'Object must contain `end_date` property.' );
		}

		if ( ! isset( $json->amount ) ) {
			throw new \InvalidArgumentException( 'Object must contain `amount` property.' );
		}

		$start_date = new DateTime( $json->start_date );

		$end_date = new DateTime( $json->end_date );

		$amount = TaxedMoneyJsonTransformer::from_json( $json->amount );

		return new self( $json->subscription_id, $start_date, $end_date, $amount );
	}

	/**
	 * To JSON.
	 *
	 * @return object
	 */
	public function to_json() {
		$subscription = $this->phase->get_subscription();

		$json = (object) array(
			'subscription' => (object) array(
				'$ref' => \rest_url(
					\sprintf(
						'/%s/%s/%d',
						'pronamic-pay/v1',
						'subscriptions',
						$subscription->get_id()
					)
				),
			),
			'phase'      => (object) array(
				'$ref' => \rest_url(
					\sprintf(
						'/%s/%s/%d/%s/phases/%d',
						'pronamic-pay/v1',
						'subscriptions',
						$subscription->get_id(),
						$this->phase->get_sequence_number()
					)
				),
			),
			'start_date' => $this->start_date->format( \DATE_ATOM ),
			'end_date'   => $this->end_date->format( \DATE_ATOM ),
			'amount'     => TaxedMoneyJsonTransformer::to_json( $this->amount ),
		);

		return $json;
	}
}
