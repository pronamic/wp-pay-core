<?php
/**
 * Subscription statuses
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Subscriptions;

/**
 * Title: WordPress pay subscription statuses constants
 * Description:
 * Copyright: 2005-2023 Pronamic
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
	const ACTIVE = 'Active';

	/**
	 * Status indicator for cancelled
	 *
	 * @var string
	 */
	const CANCELLED = 'Cancelled';

	/**
	 * Status indicator for completed
	 *
	 * @var string
	 */
	const COMPLETED = 'Completed';

	/**
	 * Status indicator for expired
	 *
	 * @var string
	 */
	const EXPIRED = 'Expired';

	/**
	 * Status indicator for failure
	 *
	 * @var string
	 */
	const FAILURE = 'Failure';

	/**
	 * Status indicator for on hold
	 *
	 * @var string
	 */
	const ON_HOLD = 'On Hold';

	/**
	 * Status indicator for open
	 *
	 * @var string
	 */
	const OPEN = 'Open';
}
