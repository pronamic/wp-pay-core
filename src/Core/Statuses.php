<?php
/**
 * Statuses
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Core;

/**
 * Title: WordPress pay statuses constants
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.2.4
 * @since   1.0.0
 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
 */
class Statuses {
	/**
	 * Status indicator for success
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const SUCCESS = 'Success';

	/**
	 * Status indicator for cancelled
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const CANCELLED = 'Cancelled';

	/**
	 * Status indicator for expired
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const EXPIRED = 'Expired';

	/**
	 * Status indicator for failure
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const FAILURE = 'Failure';

	/**
	 * Status indicator for on hold
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const ON_HOLD = 'On Hold';

	/**
	 * Status indicator for open
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const OPEN = 'Open';

	/**
	 * Status indicator for refunded
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const REFUNDED = 'Refunded';

	/**
	 * Status indicator for reserved
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const RESERVED = 'Reserved';

	/**
	 * Status indicator for active
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const ACTIVE = 'Active';

	/**
	 * Status indicator for completed
	 *
	 * @deprecated 2.2.4 Use \Pronamic\WordPress\Pay\Payments\PaymentStatus or \Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus instead.
	 *
	 * @var string
	 */
	const COMPLETED = 'Completed';
}
