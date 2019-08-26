<?php
/**
 * Subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

use DateInterval;
use Exception;
use InvalidArgumentException;
use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\Parser as MoneyParser;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentInfo;
use Pronamic\WordPress\Pay\Payments\PaymentInfoHelper;
use WP_Post;

/**
 * Subscription
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Subscription extends LegacySubscription {
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
	 * @see  Statuses
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
	 * Construct and initialize subscription object.
	 *
	 * @param int|null $post_id A subscription post ID or null.
	 */
	public function __construct( $post_id = null ) {
		parent::__construct( $post_id );

		$this->meta = array();

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
	 * @return \DateInterval|null
	 */
	public function get_date_interval() {
		$interval_spec = 'P' . $this->interval . $this->interval_period;

		try {
			$interval = new DateInterval( $interval_spec );
		} catch ( Exception $e ) {
			$interval = null;
		}

		return $interval;
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
		$this->status = $status;
	}

	/**
	 * Add the specified note to this subscription.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_insert_comment/
	 *
	 * @param string $note A Note.
	 * @return int|false The new comment's ID on success, false on failure.
	 */
	public function add_note( $note ) {
		$commentdata = array(
			'comment_post_ID'  => $this->id,
			'comment_content'  => $note,
			'comment_type'     => 'subscription_note',
			'user_id'          => get_current_user_id(),
			'comment_approved' => true,
		);

		$comment_id = wp_insert_comment( $commentdata );

		return $comment_id;
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

		// Fallback to first payment source text.
		if ( $default_text === $text ) {
			$payment = $this->get_first_payment();

			if ( null !== $payment ) {
				$text = $payment->get_source_text();
			}
		}

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

		// Fallback to first payment source description.
		if ( $default_text === $text ) {
			$payment = $this->get_first_payment();

			if ( $payment ) {
				$text = $payment->get_source_description();
			}
		}

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

		if ( null === $url ) {
			$payment = $this->get_first_payment();

			if ( $payment ) {
				$url = apply_filters( 'pronamic_payment_source_url', $url, $payment );
				$url = apply_filters( 'pronamic_payment_source_url_' . $this->source, $url, $payment );
			}
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
	 * Get all the payments for this subscription.
	 *
	 * @return array
	 */
	public function get_payments() {
		if ( null === $this->id ) {
			return array();
		}

		return get_pronamic_payments_by_meta( '_pronamic_payment_subscription_id', $this->id );
	}

	/**
	 * Get the first payment of this subscription.
	 *
	 * @return Payment|null
	 */
	public function get_first_payment() {
		$payments = $this->get_payments();

		if ( count( $payments ) > 0 ) {
			return $payments[0];
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
	 * Update meta.
	 *
	 * @todo  Not sure how and when this function is used.
	 * @param array $meta The meta data to update.
	 * @return void
	 */
	public function update_meta( $meta ) {
		if ( ! is_array( $meta ) || count( $meta ) === 0 ) {
			return;
		}

		$note = sprintf(
			'<p>%s:</p>',
			__( 'Subscription changed', 'pronamic_ideal' )
		);

		$note .= '<dl>';

		$add_note = false;

		foreach ( $meta as $key => $value ) {
			$current_value = $this->get_meta( strval( $key ) );

			// Convert string to amount for comparison.
			if ( 'amount' === $key && false !== $current_value ) {
				$money_parser = new MoneyParser();

				$current_value = $money_parser->parse( $current_value )->get_value();
			}

			if ( $current_value === $value ) {
				continue;
			}

			$add_note = true;

			$this->set_meta( strval( $key ), $value );

			if ( $value instanceof DateTime ) {
				$value = date_i18n( __( 'l jS \o\f F Y, h:ia', 'pronamic_ideal' ), $value->getTimestamp() );
			}

			$note .= sprintf( '<dt>%s</dt>', esc_html( strval( $key ) ) );
			$note .= sprintf( '<dd>%s</dd>', esc_html( strval( $value ) ) );
		}

		$note .= '</dl>';

		if ( ! $add_note ) {
			return;
		}

		$this->add_note( $note );
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
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json, $subscription = null ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( null === $subscription ) {
			$subscription = new self();
		}

		PaymentInfoHelper::from_json( $json, $subscription );

		if ( isset( $json->status ) ) {
			$subscription->set_status( $json->status );
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

		if ( null !== $this->get_status() ) {
			$properties['status'] = $this->get_status();
		}

		$object = (object) $properties;

		return $object;
	}
}
