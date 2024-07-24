<?php
/**
 * Payment statuses
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Title: WordPress pay payment statuses constants
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.7.1
 * @since   1.0.0
 */
class PaymentStatus {
	/**
	 * Status indicator for success
	 *
	 * @var string
	 */
	const SUCCESS = 'Success';

	/**
	 * Status indicator for cancelled
	 *
	 * @var string
	 */
	const CANCELLED = 'Cancelled';

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

	/**
	 * Status indicator for refunded
	 *
	 * @var string
	 */
	const REFUNDED = 'Refunded';

	/**
	 * Status indicator for completed
	 *
	 * @var string
	 */
	const COMPLETED = 'Completed';

	/**
	 * Status indicator for authorized
	 *
	 * @var string
	 */
	const AUTHORIZED = 'Authorized';
}
