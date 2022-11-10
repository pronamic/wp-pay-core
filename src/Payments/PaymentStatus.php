<?php
/**
 * Payment statuses
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Core
 */

namespace Pronamic\WordPress\Pay\Payments;

/**
 * Title: WordPress pay payment statuses constants
 * Description:
 * Copyright: 2005-2022 Pronamic
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
	public const SUCCESS = 'Success';

	/**
	 * Status indicator for cancelled
	 *
	 * @var string
	 */
	public const CANCELLED = 'Cancelled';

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

	/**
	 * Status indicator for refunded
	 *
	 * @var string
	 */
	public const REFUNDED = 'Refunded';

	/**
	 * Status indicator for completed
	 *
	 * @var string
	 */
	public const COMPLETED = 'Completed';

	/**
	 * Status indicator for authorized
	 *
	 * @var string
	 */
	public const AUTHORIZED = 'Authorized';
}
