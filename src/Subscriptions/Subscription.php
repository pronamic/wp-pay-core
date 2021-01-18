<?php
/**
 * Subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateInterval;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Payments\LegacyPaymentInfo;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentInfoHelper;

/**
 * Subscription
 *
 * @author  Remco Tolsma
 * @version 2.5.0
 * @since   1.0.0
 */
class Subscription extends LegacyPaymentInfo implements \JsonSerializable {
	use SubscriptionPhasesTrait;

	/**
	 * The key of this subscription, used in URL's for security.
	 *
	 * @var string|null
	 */
	public $key;

	/**
	 * The title of this subscription.
	 *
	 * @var string|null
	 */
	public $title;

	/**
	 * The frequency of this subscription, also known as `times` or `product length`.
	 * If the frequency is `2` then there will be in total `3` payments for the
	 * subscription. One (`1`) at the start of the subscription and `2` follow-up
	 * payments.
	 *
	 * @link https://docs.mollie.com/reference/v2/subscriptions-api/create-subscription
	 *
	 * @var int|null
	 */
	public $frequency;

	/**
	 * The interval of this subscription, for example: 1, 2, 3, etc.
	 *
	 * @todo Improve documentation?
	 *
	 * @var int|null
	 */
	public $interval;

	/**
	 * The interval period of this subscription.
	 *
	 * @todo Improve documentation?
	 *
	 * @var string|null
	 */
	public $interval_period;

	/**
	 * The interval date of this subscription.
	 *
	 * @var string|null
	 */
	public $interval_date;

	/**
	 * The interval date day of this subscription.
	 *
	 * @var string|null
	 */
	public $interval_date_day;

	/**
	 * The interval date month of this subscription.
	 *
	 * @var string|null
	 */
	public $interval_date_month;

	/**
	 * The status of this subscription, for example 'Success'.
	 *
	 * @todo How to reference to a class constant?
	 * @see  PaymentStatus
	 *
	 * @var string|null
	 */
	public $status;

	/**
	 * The payment method which was used to create this subscription.
	 *
	 * @var string|null
	 */
	public $payment_method;

	/**
	 * The end date of the last succesfull payment.
	 *
	 * @var DateTime|null
	 */
	public $expiry_date;

	/**
	 * The next payment date.
	 *
	 * @var DateTime|null
	 */
	public $next_payment_date;

	/**
	 * The next payment delivery date.
	 *
	 * @var DateTime|null
	 */
	public $next_payment_delivery_date;

	/**
	 * Array for extra meta data to store with this subscription.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Activated at.
	 *
	 * The datetime this subscription was activated or reactived.
	 *
	 * @var DateTime
	 */
	private $activated_at;

	/**
	 * Construct and initialize subscription object.
	 *
	 * @param int|null $post_id A subscription post ID or null.
	 *
	 * @throws \Exception Throws exception on invalid post date.
	 */
	public function __construct( $post_id = null ) {
		parent::__construct( $post_id );

		$this->meta = array();

		$this->activated_at = new DateTime();

		if ( ! empty( $post_id ) ) {
			pronamic_pay_plugin()->subscriptions_data_store->read( $this );
		}
	}

	/**
	 * Get the unique key of this subscription.
	 *
	 * @return string|null
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get the frequency of this subscription.
	 *
	 * @return int|null
	 */
	public function get_frequency() {
		return $this->frequency;
	}

	/**
	 * Get the interval, for example: 1, 2, 3, 4, etc., this specifies for example:
	 * - Repeat every *2* days
	 * - Repeat every *1* months
	 * - Repeat every *2* year
	 *
	 * @return int|null
	 */
	public function get_interval() {
		return $this->interval;
	}

	/**
	 * Get the interval period, for example 'D', 'M', 'Y', etc.
	 *
	 * @link http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
	 *
	 * @return string|null
	 */
	public function get_interval_period() {
		return $this->interval_period;
	}

	/**
	 * Get the interval period date (1-31).
	 *
	 * @return string|null
	 */
	public function get_interval_date() {
		return $this->interval_date;
	}

	/**
	 * Get the interval period day (Monday-Sunday).
	 *
	 * @return string|null
	 */
	public function get_interval_date_day() {
		return $this->interval_date_day;
	}

	/**
	 * Get the interval period month (1-12).
	 *
	 * @return string|null
	 */
	public function get_interval_date_month() {
		return $this->interval_date_month;
	}

	/**
	 * Get date interval.
	 *
	 * @link http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
	 *
	 * @return SubscriptionInterval|null
	 */
	public function get_date_interval() {
		$phase = $this->get_current_phase();

		if ( null === $phase ) {
			return null;
		}

		return $phase->get_interval();
	}

	/**
	 * Get the status of this subscription.
	 *
	 * @todo Check constant?
	 *
	 * @return string|null
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the status of this subscription.
	 *
	 * @todo Check constant?
	 *
	 * @param string|null $status A status string.
	 * @return void
	 */
	public function set_status( $status ) {
		if ( SubscriptionStatus::ACTIVE === $status && $this->status !== $status ) {
			$this->set_activated_at( new DateTime() );
		}

		$this->status = $status;
	}

	/**
	 * Add the specified note to this subscription.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_insert_comment/
	 * @param string $note A Note.
	 * @return int The new comment's ID.
	 * @throws \Exception Throws exception when adding note fails.
	 */
	public function add_note( $note ) {
		$commentdata = array(
			'comment_post_ID'  => $this->id,
			'comment_content'  => $note,
			'comment_type'     => 'subscription_note',
			'user_id'          => get_current_user_id(),
			'comment_approved' => true,
		);

		$result = wp_insert_comment( $commentdata );

		if ( false === $result ) {
			throw new \Exception(
				\sprintf(
					'Could not add note "%s" to subscription with ID "%d".',
					$note,
					$this->id
				)
			);
		}

		return $result;
	}

	/**
	 * Get meta by the specified meta key.
	 *
	 * @param string $key A meta key.
	 * @return string|false
	 */
	public function get_meta( $key ) {
		if ( null === $this->id ) {
			return false;
		}

		$key = '_pronamic_subscription_' . $key;

		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * Set meta data.
	 *
	 * @param  string $key   A meta key.
	 * @param  mixed  $value A meta value.
	 *
	 * @return bool True on successful update, false on failure.
	 */
	public function set_meta( $key, $value = false ) {
		if ( null === $this->id ) {
			return false;
		}

		$key = '_pronamic_subscription_' . $key;

		if ( $value instanceof \DateTime ) {
			$value = $value->format( 'Y-m-d H:i:s' );
		}

		if ( empty( $value ) ) {
			return delete_post_meta( $this->id, $key );
		}

		$result = update_post_meta( $this->id, $key, $value );

		return ( false !== $result );
	}

	/**
	 * Get source text.
	 *
	 * @return string
	 */
	public function get_source_text() {
		$pieces = array(
			$this->get_source(),
			$this->get_source_id(),
		);

		$pieces = array_filter( $pieces );

		$default_text = implode( '<br />', $pieces );

		$text = apply_filters( 'pronamic_subscription_source_text_' . $this->get_source(), $default_text, $this );
		$text = apply_filters( 'pronamic_subscription_source_text', $text, $this );

		return $text;
	}

	/**
	 * Get source description.
	 *
	 * @return string
	 */
	public function get_source_description() {
		$default_text = $this->get_source();

		$text = apply_filters( 'pronamic_subscription_source_description_' . $this->get_source(), $default_text, $this );
		$text = apply_filters( 'pronamic_subscription_source_description', $text, $this );

		return $text;
	}

	/**
	 * Get source link for this subscription.
	 *
	 * @return string|null
	 */
	public function get_source_link() {
		$url = null;

		$url = apply_filters( 'pronamic_subscription_source_url', $url, $this );
		$url = apply_filters( 'pronamic_subscription_source_url_' . $this->source, $url, $this );

		return $url;
	}

	/**
	 * Get cancel URL for this subscription.
	 *
	 * @return string
	 */
	public function get_cancel_url() {
		$cancel_url = add_query_arg(
			array(
				'subscription' => $this->get_id(),
				'key'          => $this->get_key(),
				'action'       => 'cancel',
			),
			home_url()
		);

		return $cancel_url;
	}

	/**
	 * Get renewal URL for this subscription.
	 *
	 * @return string
	 */
	public function get_renewal_url() {
		$renewal_url = add_query_arg(
			array(
				'subscription' => $this->get_id(),
				'key'          => $this->get_key(),
				'action'       => 'renew',
			),
			home_url()
		);

		return $renewal_url;
	}

	/**
	 * Get mandate selection URL for this subscription.
	 *
	 * @return string
	 */
	public function get_mandate_selection_url() {
		$renewal_url = add_query_arg(
			array(
				'subscription' => $this->get_id(),
				'key'          => $this->get_key(),
				'action'       => 'mandate',
			),
			home_url()
		);

		return $renewal_url;
	}

	/**
	 * Get all the payments for this subscription.
	 *
	 * @return Payment[]
	 */
	public function get_payments() {
		if ( null === $this->id ) {
			return array();
		}

		$payments = get_pronamic_payments_by_meta( '_pronamic_payment_subscription_id', $this->id );

		return $payments;
	}

	/**
	 * Get payments by period.
	 *
	 * @return array
	 */
	public function get_payments_by_period() {
		$payments = $this->get_payments();

		$periods = array();

		foreach ( $payments as $payment ) {
			// Get period for this subscription.
			$period = null;

			$payment_periods = $payment->get_periods();

			if ( null === $payment_periods ) {
				break;
			}

			foreach ( $payment_periods as $period ) {
				if ( $this->get_id() === $period->get_phase()->get_subscription()->get_id() ) {
					break;
				}
			}

			if ( null === $period ) {
				continue;
			}

			// Add period to result.
			$start = $period->get_start_date()->getTimestamp();

			if ( ! \array_key_exists( $start, $periods ) ) {
				$periods[ $start ] = array(
					'period'    => $period,
					'payments'  => array(),
					'can_retry' => true,
				);
			}

			// Add payment to result.
			$periods[ $start ]['payments'][ $payment->get_date()->getTimestamp() ] = $payment;

			if ( \in_array( $payment->get_status(), array( PaymentStatus::OPEN, PaymentStatus::SUCCESS ), true ) ) {
				$periods[ $start ]['can_retry'] = false;
			}
		}

		// Sort periods and payments.
		\krsort( $periods );

		foreach ( $periods as &$period ) {
			\ksort( $period['payments'] );
		}

		return $periods;
	}

	/**
	 * Get the first payment of this subscription.
	 *
	 * @return Payment|null
	 */
	public function get_first_payment() {
		if ( null === $this->id ) {
			return null;
		}

		// Query arguments to get first payment.
		$args = array(
			'posts_per_page' => 1,
			'orderby'        => 'post_date',
			'order'          => 'ASC',
		);

		$first_payment = get_pronamic_payments_by_meta( '_pronamic_payment_subscription_id', $this->id, $args );

		if ( ! empty( $first_payment ) ) {
			return $first_payment[0];
		}

		return null;
	}

	/**
	 * Get the expiry date of this subscription.
	 *
	 * @return DateTime|null
	 */
	public function get_expiry_date() {
		return $this->expiry_date;
	}

	/**
	 * Set the expiry date of this subscription.
	 *
	 * @param DateTime|null $date Expiry date.
	 * @return void
	 */
	public function set_expiry_date( DateTime $date = null ) {
		$this->expiry_date = $date;
	}

	/**
	 * Set the next payment date of this subscription.
	 *
	 * @param DateTime|null $date Next payment date.
	 * @return void
	 */
	public function set_next_payment_date( DateTime $date = null ) {
		$this->next_payment_date = $date;
	}

	/**
	 * Get the next payment date of this subscription.
	 *
	 * @return DateTime|null
	 */
	public function get_next_payment_date() {
		return $this->next_payment_date;
	}

	/**
	 * Set the next payment delivery date of this subscription.
	 *
	 * @param DateTime|null $date Next payment delivery date.
	 *
	 * @return void
	 */
	public function set_next_payment_delivery_date( DateTime $date = null ) {
		$this->next_payment_delivery_date = $date;
	}

	/**
	 * Get the next payment delivery date of this subscription.
	 *
	 * @return DateTime|null
	 */
	public function get_next_payment_delivery_date() {
		return $this->next_payment_delivery_date;
	}

	/**
	 * Create new subscription period.
	 *
	 * @return SubscriptionPeriod|null
	 * @throws \UnexpectedValueException Throws exception when no date interval is available for this subscription.
	 */
	public function new_period() {
		$phase = $this->get_current_phase();

		if ( null === $phase ) {
			throw new \UnexpectedValueException( 'Cannot create new subscription period for subscription without phase.' );
		}

		return $this->next_period();
	}

	/**
	 * Save subscription.
	 *
	 * @return void
	 */
	public function save() {
		pronamic_pay_plugin()->subscriptions_data_store->save( $this );
	}

	/**
	 * Create subscription from object.
	 *
	 * @param mixed             $json         JSON.
	 * @param Subscription|null $subscription Subscription.
	 * @return Subscription
	 * @throws \InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, $subscription = null ) {
		if ( ! is_object( $json ) ) {
			throw new \InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( null === $subscription ) {
			$subscription = new self();
		}

		PaymentInfoHelper::from_json( $json, $subscription );

		if ( isset( $json->expiry_date ) ) {
			$subscription->set_expiry_date( new DateTime( $json->expiry_date ) );
		}

		if ( isset( $json->next_payment_date ) ) {
			$subscription->set_next_payment_date( new DateTime( $json->next_payment_date ) );
		}

		if ( isset( $json->next_payment_delivery_date ) ) {
			$subscription->set_next_payment_delivery_date( new DateTime( $json->next_payment_delivery_date ) );
		}

		if ( isset( $json->status ) ) {
			$subscription->set_status( $json->status );
		}

		if ( isset( $json->phases ) ) {
			foreach ( $json->phases as $json_phase ) {
				$json_phase->subscription = $subscription;

				$subscription->add_phase( SubscriptionPhase::from_json( $json_phase ) );
			}
		}

		$activated_at = $subscription->date;

		if ( property_exists( $json, 'activated_at' ) ) {
			$activated_at = new DateTime( $json->activated_at );
		}

		$subscription->set_activated_at( $activated_at );

		return $subscription;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$object = PaymentInfoHelper::to_json( $this );

		$properties = (array) $object;

		$properties['phases'] = $this->phases;

		if ( null !== $this->expiry_date ) {
			$properties['expiry_date'] = $this->expiry_date->format( \DATE_ATOM );
		}

		if ( null !== $this->next_payment_date ) {
			$properties['next_payment_date'] = $this->next_payment_date->format( \DATE_ATOM );
		}

		if ( null !== $this->next_payment_delivery_date ) {
			$properties['next_payment_delivery_date'] = $this->next_payment_delivery_date->format( \DATE_ATOM );
		}

		if ( null !== $this->get_status() ) {
			$properties['status'] = $this->get_status();
		}

		$properties['activated_at'] = $this->get_activated_at()->format( \DATE_ATOM );

		$object = (object) $properties;

		return $object;
	}

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return object
	 */
	public function jsonSerialize() {
		return $this->get_json();
	}

	/**
	 * Get activated datetime.
	 *
	 * @return DateTime
	 */
	public function get_activated_at() {
		return $this->activated_at;
	}

	/**
	 * Set activated datetime.
	 *
	 * @param DateTime $activated_at Activated at.
	 * @return void
	 */
	public function set_activated_at( DateTime $activated_at ) {
		$this->activated_at = $activated_at;
	}
}
