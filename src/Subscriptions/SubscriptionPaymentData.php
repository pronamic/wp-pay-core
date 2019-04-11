<?php
/**
 * Subscription Payment Data
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Payments
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;
use Pronamic\WordPress\Pay\Payments\PaymentData;

/**
 * WordPress subscription payment data
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.0.1
 */
class SubscriptionPaymentData extends PaymentData {
	/**
	 * The subscription.
	 *
	 * @var Subscription $subscription
	 */
	public $subscription;

	/**
	 * Constructs and initializes WordPress subscription payment data.
	 *
	 * @param Subscription $subscription The subscription.
	 */
	public function __construct( Subscription $subscription ) {
		parent::__construct();

		$this->subscription = $subscription;
	}

	/**
	 * Get config id.
	 *
	 * @return int
	 */
	public function get_config_id() {
		return $this->subscription->config_id;
	}

	/**
	 * Get user id.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->subscription->post->post_author;
	}

	/**
	 * Get source.
	 *
	 * @return string
	 */
	public function get_source() {
		return $this->subscription->source;
	}

	/**
	 * Get source id.
	 *
	 * @return string
	 */
	public function get_source_id() {
		return $this->subscription->source_id;
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->subscription->description;
	}

	/**
	 * Get order id.
	 *
	 * @return string
	 */
	public function get_order_id() {
		$this->subscription->order_id;
	}

	/**
	 * Get items.
	 *
	 * @return Items
	 */
	public function get_items() {
		// Items.
		$items = new Items();

		// Item.
		$item = new Item();
		$item->set_number( $this->get_order_id() );
		$item->set_description( $this->get_description() );
		$item->set_price( $this->subscription->get_total_amount()->get_value() );
		$item->set_quantity( 1 );

		$items->add_item( $item );

		return $items;
	}

	/**
	 * Get currency alphabetic code.
	 *
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return $this->subscription->get_total_amount()->get_currency()->get_alphabetic_code();
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_customer_name() {
		return $this->subscription->customer_name;
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->subscription->email;
	}
}
