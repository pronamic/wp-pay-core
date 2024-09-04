<?php
/**
 * Action Scheduler Controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

/**
 * Action Scheduler Controller class
 */
class ActionSchedulerController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'action_scheduler_begin_execute', [ $this, 'begin_execute' ], 10, 2 );
	}

	/**
	 * Action scheduler begin execute.
	 *
	 * @link https://github.com/woocommerce/action-scheduler/blob/3.7.1/classes/abstracts/ActionScheduler_Abstract_QueueRunner.php#L84
	 * @param int    $action_id Action ID.
	 * @param string $context   Context.
	 * @return void
	 */
	public function begin_execute( $action_id, $context ) {
		if ( \defined( 'PRONAMIC_ACTION_SCHEDULER_CONTEXT' ) ) {
			return;
		}

		\define( 'PRONAMIC_ACTION_SCHEDULER_CONTEXT', $context );
	}
}
