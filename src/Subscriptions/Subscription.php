<?php
/**
 * Subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateInterval;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\DateTime\DateTimeInterface;
use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Pay\Payments\PaymentInfo;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentInfoHelper;

/**
 * Subscription
 *
 * @author  Remco Tolsma
 * @version 2.7.1
 * @since   1.0.0
 */
class Subscription extends PaymentInfo implements \JsonSerializable {
	use SubscriptionPhasesTrait;

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
	 * Activated at.
	 *
	 * The datetime this subscription was activated or reactivated.
	 *
	 * @var DateTime
	 */
	private $activated_at;

	/**
	 * Construct and initialize subscription object.
	 *
	 * @throws \Exception Throws exception on invalid post date.
	 */
	public function __construct() {
		parent::__construct();

		$this->activated_at    = new DateTime();
		$this->meta_key_prefix = '_pronamic_subscription_';
	}

	/**
	 * Get date interval.
	 *
	 * @link http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
	 * @deprecated
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
	 * @return string|null
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the status of this subscription.
	 *
	 * @todo Check constant?
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
	 * Get source text.
	 *
	 * @return string
	 */
	public function get_source_text() {
		$pieces = array(
			\ucfirst( (string) $this->get_source() ),
			$this->get_source_id(),
		);

		$pieces = array_filter( $pieces );

		$text = implode( '<br />', $pieces );

		$source = $this->get_source();

		$subscription = $this;

		if ( null !== $source ) {
			/**
			 * Filters the subscription source text by plugin integration source.
			 *
			 * @param string       $text         Source text.
			 * @param Subscription $subscription Subscription.
			 */
			$text = apply_filters( 'pronamic_subscription_source_text_' . $source, $text, $subscription );
		}

		/**
		 * Filters the subscription source text.
		 *
		 * @param string       $text         Source text.
		 * @param Subscription $subscription Subscription.
		 */
		$text = apply_filters( 'pronamic_subscription_source_text', $text, $subscription );

		return $text;
	}

	/**
	 * Get source description.
	 *
	 * @return string
	 */
	public function get_source_description() {
		$subscription = $this;

		$source = $subscription->get_source();

		$description = $source;

		if ( null !== $source ) {
			/**
			 * Filters the subscription source description by plugin integration source.
			 *
			 * @param string       $description  Source description.
			 * @param Subscription $subscription Subscription.
			 */
			$description = apply_filters( 'pronamic_subscription_source_description_' . $source, $description, $subscription );
		}

		/**
		 * Filters the subscription source description.
		 *
		 * @param string       $description  Source description.
		 * @param Subscription $subscription Subscription.
		 */
		$description = apply_filters( 'pronamic_subscription_source_description', $description, $subscription );

		return $description;
	}

	/**
	 * Get source link for this subscription.
	 *
	 * @return string|null
	 */
	public function get_source_link() {
		$url = null;

		$subscription = $this;

		$source = $subscription->get_source();

		/**
		 * Filters the subscription source URL.
		 *
		 * @param null|string  $url          Source URL.
		 * @param Subscription $subscription Subscription.
		 */
		$url = apply_filters( 'pronamic_subscription_source_url', $url, $subscription );

		if ( null !== $source ) {
			/**
			 * Filters the subscription source URL by plugin integration source.
			 *
			 * @param null|string  $url          Source URL.
			 * @param Subscription $subscription Subscription.
			 */
			$url = apply_filters( 'pronamic_subscription_source_url_' . $source, $url, $subscription );
		}

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
			home_url( '/' )
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
			home_url( '/' )
		);

		return $renewal_url;
	}

	/**
	 * Get mandate selection URL for this subscription.
	 *
	 * @return string
	 */
	public function get_mandate_selection_url() {
		$url = add_query_arg(
			array(
				'subscription' => $this->get_id(),
				'key'          => $this->get_key(),
				'action'       => 'mandate',
			),
			home_url( '/' )
		);

		return $url;
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
				continue;
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
	 * Check if the payment is the first for this subscription.
	 *
	 * @param Payment $payment Payment.
	 * @return bool True if payment is the first, false otherwise.
	 */
	public function is_first_payment( Payment $payment ) {
		$phases = $this->get_phases();

		$phase = \reset( $phases );

		if ( false === $phase ) {
			return false;
		}

		$periods = $payment->get_periods();

		if ( null === $periods ) {
			return false;
		}

		foreach ( $periods as $period ) {
			if ( $period->get_phase()->get_subscription() !== $this ) {
				continue;
			}

			// Compare formatted dates instead of date objects,
			// to account for differences in microseconds.
			$period_start = $period->get_start_date()->format( 'Y-m-d H:i:s' );
			$phase_start  = $phase->get_start_date()->format( 'Y-m-d H:i:s' );

			if ( $period_start === $phase_start ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the next payment date of this subscription.
	 *
	 * @return DateTimeImmutable|null
	 */
	public function get_next_payment_date() {
		return $this->next_payment_date;
	}

	/**
	 * Set the next payment date of this subscription.
	 *
	 * @param $date Date.
	 * @return void
	 */
	public function set_next_payment_date( $date ) {
		$end_date = $this->get_end_date();

		if ( null !== $end_date && $date >= $end_date ) {
			$this->next_payment_date = null;

			return;
		}

		$this->next_payment_date = ( null === $date ) ? null : DateTimeImmutable::create_from_interface( $date );
	}

	/**
	 * Get the next payment delivery date of this subscription.
	 *
	 * @return DateTimeInterface|null
	 */
	public function get_next_payment_delivery_date() {
		$next_payment_date = $this->get_next_payment_date();

		// Check if there is next payment date.
		if ( null === $next_payment_date ) {
			return null;
		}

		$next_payment_delivery_date = clone $next_payment_date;

		$subscription = $this;

		/**
		 * Filters the subscription next payment delivery date.
		 *
		 * @param DateTimeImmutable $next_payment_delivery_date Next payment delivery date.
		 * @param Subscription      $subscription               Subscription.
		 * @since unreleased
		 */
		$next_payment_delivery_date = \apply_filters( 'pronamic_pay_subscription_next_payment_delivery_date', $next_payment_delivery_date, $subscription );

		return $next_payment_delivery_date;
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
	 * New subscription payment.
	 *
	 * Subscriptions lines and amount are deliberately not set in the payment for now.
	 *
	 * @return Payment
	 */
	public function new_payment() {
		$payment = new Payment();

		$payment->order_id = $this->get_order_id();

		$payment->add_subscription( $this );

		$payment->set_payment_method( $this->get_payment_method() );

		$payment->set_description( $this->get_description() );
		$payment->set_config_id( $this->get_config_id() );
		$payment->set_origin_id( $this->get_origin_id() );

		$payment->set_source( $this->get_source() );
		$payment->set_source_id( $this->get_source_id() );

		$payment->set_customer( $this->get_customer() );
		$payment->set_billing_address( $this->get_billing_address() );
		$payment->set_shipping_address( $this->get_shipping_address() );

		return $payment;
	}

	/**
	 * Get renewal period.
	 *
	 * @return SubscriptionPeriod|null
	 */
	public function get_renewal_period() {
		$renewal_period = null;

		// Get next period for current phase.
		$current_phase = $this->get_current_phase();

		if ( null !== $current_phase ) {
			$renewal_period = $current_phase->get_next_period();
		}

		// Check if last period failed.
		$now = new DateTimeImmutable();

		$periods = $this->get_payments_by_period();

		$last_period = array_shift( $periods );

		if ( null !== $last_period ) {
			// Can period be re-tried?
			if ( false === $last_period['can_retry'] ) {
				return $renewal_period;
			}

			// Can payment be re-tried?
			$payment = array_shift( $last_period['payments'] );

			if ( ! \pronamic_pay_plugin()->subscriptions_module->can_retry_payment( $payment ) ) {
				return $renewal_period;
			}

			// Is last period end date in the future?
			if ( $last_period['period']->get_end_date() > $now ) {
				$renewal_period = $last_period['period'];
			}
		}

		return $renewal_period;
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

		if ( \property_exists( $json, 'next_payment_date' ) ) {
			if ( null !== $json->next_payment_date ) {
				$subscription->set_next_payment_date( new DateTimeImmutable( $json->next_payment_date ) );
			}
		}

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

		if ( null !== $this->get_status() ) {
			$properties['status'] = $this->get_status();
		}

		$properties['activated_at'] = $this->get_activated_at()->format( \DATE_ATOM );

		$properties['next_payment_date'] = ( null === $this->next_payment_date ) ? null : $this->next_payment_date->format( \DATE_ATOM );

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
