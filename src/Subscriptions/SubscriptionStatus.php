<?php
/**
 * Subscription statuses
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Title: WordPress pay subscription statuses constants
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.2.4
 * @since   2.2.4
 */
class SubscriptionStatus {
	/**
	 * Status indicator for active
	 *
	 * @var string
	 */
	public const ACTIVE = 'Active';

	/**
	 * Status indicator for cancelled
	 *
	 * @var string
	 */
	public const CANCELLED = 'Cancelled';

	/**
	 * Status indicator for completed
	 *
	 * @var string
	 */
	public const COMPLETED = 'Completed';

	/**
	 * Status indicator for expired
	 *
	 * @var string
	 */
	public const EXPIRED = 'Expired';

	/**
	 * Status indicator for failure
	 *
	 * @var string
	 */
	public const FAILURE = 'Failure';

	/**
	 * Status indicator for on hold
	 *
	 * @var string
	 */
	public const ON_HOLD = 'On Hold';

	/**
	 * Status indicator for open
	 *
	 * @var string
	 */
	public const OPEN = 'Open';
}
