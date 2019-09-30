<?php
/**
 * Log level.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Log level.
 *
 * @link https://tools.ietf.org/html/rfc5424
 *
 * @author  Reüel van der Steege
 * @version 2.2.4
 * @since   2.2.4
 */
class LogLevel {
	/**
	 * Constant for level 'Emergency'.
	 */
	const EMERGENCY = 'emergency';

	/**
	 * Constant for level 'Alert'.
	 */
	const ALERT = 'alert';

	/**
	 * Constant for level 'Critical'.
	 */
	const CRITICAL = 'critical';

	/**
	 * Constant for level 'Error'.
	 */
	const ERROR = 'error';

	/**
	 * Constant for level 'Warning'.
	 */
	const WARNING = 'warning';

	/**
	 * Constant for level 'Notice'.
	 */
	const NOTICE = 'notice';

	/**
	 * Constant for level 'Informational'.
	 */
	const INFO = 'info';

	/**
	 * Constant for level 'Debug'.
	 */
	const DEBUG = 'debug';
}
