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
	 * The subscription this period is part of.
	 *
	 * @var int
	 */
	private $subscription_id;

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
	 * @param int        $subscription_id Subscription ID.
	 * @param DateTime   $start_date      Start date.
	 * @param DateTime   $end_date        End date.
	 * @param TaxedMoney $amount          Taxed amount.
	 */
	public function __construct( $subscription_id, DateTime $start_date, DateTime $end_date, TaxedMoney $amount ) {
		$this->subscription_id = $subscription_id;
		$this->start_date      = $start_date;
		$this->end_date        = $end_date;
		$this->amount          = $amount;
	}

	/**
	 * Get subscription ID.
	 *
	 * @return int
	 */
	public function get_subscription_id() {
		return $this->subscription_id;
	}

	/**
	 * Set subscription ID.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return void
	 */
	public function set_subscription_id( $subscription_id ) {
		$this->subscription_id = $subscription_id;
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

		if ( ! isset( $json->subscription_id ) ) {
			throw new \InvalidArgumentException( 'Object must contain `subscription_id` property.' );
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
		$json = (object) array(
			'subscription_id' => $this->subscription_id,
			'start_date'      => $this->start_date->format( \DATE_ATOM ),
			'end_date'        => $this->end_date->format( \DATE_ATOM ),
			'amount'          => TaxedMoneyJsonTransformer::to_json( $this->amount ),
		);

		return $json;
	}
}
