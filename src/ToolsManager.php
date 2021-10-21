<?php
/**
 * Tools Manager.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use ActionScheduler;
use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Payments\StatusChecker;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionHelper;

/**
 * Tools manager.
 *
 * @author  Reüel van der Steege
 * @version 3.3.0
 * @since   3.3.0
 */
class ToolsManager {
	/**
	 * Plugin.
	 *
	 * @var Plugin $plugin
	 */
	public $plugin;

	/**
	 * Tools.
	 *
	 * @var array<string,object>
	 */
	private $tools;

	/**
	 * Construct tools manager.
	 *
	 * @param Plugin $plugin The plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'admin_init', array( $this, 'admin_init' ) );

		// REST API.
		\add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

		$this->register_tools();
	}

	/**
	 * Admin script.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Check tool.
		$action = $this->get_current_action();

		$tool = $this->get_tool( $action );

		if ( null === $tool ) {
			return;
		}

		// Register and enqueue script.
		$min = SCRIPT_DEBUG ? '' : '.min';

		\wp_register_script(
			'pronamic-pay-admin-debug-scheduler',
			plugins_url( '../js/dist/admin-debug' . $min . '.js', __FILE__ ),
			array(),
			$this->plugin->get_version(),
			true
		);

		// Localize script.
		wp_localize_script(
			'pronamic-pay-admin-debug-scheduler',
			'pronamicPayAdminDebugScheduler',
			array(
				'action'       => $this->get_current_action(),
				'count_url'    => \rest_url( 'pronamic-pay/v1/tools/' . $action . '/count' ),
				'schedule_url' => \rest_url( 'pronamic-pay/v1/tools/' . $action . '/schedule' ),
				'nonce'        => \wp_create_nonce( 'wp_rest' ),
				'labelPause'   => \__( 'Pause', 'pronamic_ideal' ),
				'labelResume'  => \__( 'Resume', 'pronamic_ideal' ),
			)
		);
	}

	/**
	 * Register tools.
	 *
	 * @return void
	 */
	public function register_tools() {
		// Trash follow-up payments without transaction ID.
		$this->register_tool(
			'pronamic_pay_trash_follow_up_payments_without_transaction_id',
			\__( 'Follow-up payments without transaction ID', 'pronamic_ideal' ),
			array(
				'label'    => \__( 'Trash follow-up payments without transaction ID', 'pronamic_ideal' ),
				'callback' => array( $this, 'action_trash_follow_up_payment_without_transaction_id' ),
				'query'    => array(
					'post_type'      => 'pronamic_payment',
					'post_status'    => 'any',
					'fields'         => 'ids',
					'posts_per_page' => 10,
				),
			)
		);

		// Check status of pending payments.
		$this->register_tool(
			'pronamic_pay_check_pending_payments_status',
			\__( 'Pending payments status check', 'pronamic_ideal' ),
			array(
				'label'    => \__( 'Check pending payments status', 'pronamic_ideal' ),
				'callback' => array( $this, 'action_check_payment_status' ),
				'query'    => array(
					'post_type'      => 'pronamic_payment',
					'post_status'    => 'payment_pending',
					'fields'         => 'ids',
					'posts_per_page' => 10,
				),
			)
		);

		// Delete canceled follow-up payments.
		$this->register_tool(
			'pronamic_pay_delete_canceled_follow_up_payments',
			\__( 'Canceled follow-up payments', 'pronamic_ideal' ),
			array(
				'label'    => \__( 'Delete canceled follow-up payments', 'pronamic_ideal' ),
				'callback' => array( $this, 'action_delete_canceled_follow_up_payment' ),
				'query'    => array(
					'post_type'      => 'pronamic_payment',
					'post_status'    => 'payment_cancelled',
					'fields'         => 'ids',
					'posts_per_page' => 10,
				),
			)
		);

		// Reset subscription next payment dates based on successful payments.
		$this->register_tool(
			'pronamic_pay_reset_subscription_next_payment_dates_with_successful_payments',
			\__( 'Correct subscription next payment dates', 'pronamic_ideal' ),
			array(
				'label'    => \__( 'Reset subscription next payment dates based on successful payments', 'pronamic_ideal' ),
				'callback' => array( $this, 'action_reset_subscription_next_payment_date_with_successful_payments' ),
				'query'    => array(
					'post_type'      => 'pronamic_pay_subscr',
					'post_status'    => array( 'subscr_active', 'subscr_failed' ),
					'fields'         => 'ids',
					'posts_per_page' => 10,
				),
			)
		);
	}

	/**
	 * Register tools.
	 *
	 * @param string              $action Action name.
	 * @param string              $title  Tool title.
	 * @param array<string,mixed> $args   Arguments.
	 * @return void
	 */
	public function register_tool( $action, $title, $args ) {
		// Check non-empty action and title.
		if ( empty( $action ) || empty( $title ) ) {
			return;
		}

		// Add tool.
		$args = (object) \wp_parse_args(
			$args,
			array(
				'action'      => $action,
				'callback'    => null,
				'title'       => $title,
				'label'       => $title,
				'description' => '',
				'query'       => array(),
			)
		);

		$this->tools[ $action ] = $args;

		// Add action.
		$callback = $args->callback;

		if ( null !== $callback ) {
			\add_action( $action, $callback, 10, 1 );
		}
	}

	/**
	 * Get all tools.
	 *
	 * @return array<string,object>
	 */
	public function get_tools() {
		return $this->tools;
	}

	/**
	 * Get a tool by action.
	 *
	 * @param string|null $action Action.
	 * @return object|null
	 */
	public function get_tool( $action ) {
		// Check empty action.
		if ( null === $action ) {
			return null;
		}

		// Check tool.
		if ( ! \array_key_exists( $action, $this->tools ) ) {
			return null;
		}

		return $this->tools[ $action ];
	}

	/**
	 * Get current tool.
	 *
	 * @return object|null
	 */
	public function get_current_action() {
		// Check action.
		if ( ! filter_has_var( \INPUT_GET, 'pronamic_pay_action' ) ) {
			return null;
		}

		$action = filter_input( \INPUT_GET, 'pronamic_pay_action', \FILTER_SANITIZE_STRING );

		// Check nonce.
		if (
			! filter_has_var( \INPUT_GET, 'pronamic_pay_nonce' )
				||
			! wp_verify_nonce( \filter_input( \INPUT_GET, 'pronamic_pay_nonce', \FILTER_SANITIZE_STRING ), $action )
		) {
			return null;
		}

		return $action;
	}

	/**
	 * REST API init.
	 *
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
	 * @return void
	 */
	public function rest_api_init() {
		\register_rest_route(
			'pronamic-pay/v1',
			'/tools/(?P<action>\w+)/count',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_count' ),
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
				'args'                => array(
					'action' => array(
						'description' => __( 'Tool action name.', 'pronamic_ideal' ),
						'type'        => 'string',
					),
				),
			)
		);

		\register_rest_route(
			'pronamic-pay/v1',
			'/tools/(?P<action>\w+)/schedule',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_schedule' ),
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
				'args'                => array(
					'action' => array(
						'description' => __( 'Tool action name.', 'pronamic_ideal' ),
						'type'        => 'string',
					),
				),
			)
		);
	}

	/**
	 * REST API tool count.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function rest_api_count( \WP_REST_Request $request ) {
		// Get tool.
		$action = $request->get_param( 'action' );

		$tool = $this->get_tool( $action );

		if ( null === $tool ) {
			$data = array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s tool action name */
					__( 'Tool `%s` not found.', 'pronamic_ideal' ),
					$action
				),
			);

			return new \WP_REST_Response( $data );
		}

		// Query.
		$query = \wp_parse_args(
			array(
				'fields'        => 'ids',
				'no_found_rows' => true,
				'nopaging'      => true,
			),
			$tool->query
		);

		$query = new \WP_Query( $query );

		// Response.
		$data = array(
			'success' => true,
			'data'    => array(
				'count' => count( $query->posts ),
			),
		);

		return new \WP_REST_Response( $data );
	}

	/**
	 * REST API tool schedule.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function rest_api_schedule( \WP_REST_Request $request ) {
		// Get tool.
		$action = $request->get_param( 'action' );

		$tool = $this->get_tool( $action );

		if ( null === $tool ) {
			$data = array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s tool action name */
					__( 'Tool `%s` not found.', 'pronamic_ideal' ),
					$action
				),
			);

			return new \WP_REST_Response( $data );
		}

		// Lock action scheduler async request runner to temporarily prevent
		// running async events (by default for one minute).
		ActionScheduler::lock()->set( 'async-request-runner' );

		/*
		 * Query.
		 */
		$args = $tool->query;

		// Page.
		$page = $request->get_param( 'page' );

		if ( null === $page || $page <= 0 ) {
			$page = 1;
		}

		// Posts per page.
		if ( ! \array_key_exists( 'posts_per_page', $args ) ) {
			$args['posts_per_page'] = 10;
		}

		// Args.
		$args = \wp_parse_args(
			array(
				'page' => $page,
			),
			$args
		);

		// Action argument name.
		$action_arg_name = 'post_id';

		if ( \array_key_exists( 'post_type', $args ) && \is_string( $args['post_type'] ) ) {
			$action_arg_name = sprintf( '%s_id', $args['post_type'] );
		}

		$count = 0;

		// Query.
		$query = new \WP_Query( $args );

		foreach ( $query->posts as $post_id ) {
			// Schedule action.
			\as_enqueue_async_action(
				$action,
				array( $action_arg_name => $post_id ),
				$action
			);

			$count++;
		}

		// Response.
		$page = $args['page'];

		$data = array(
			'success' => true,
			'data'    => array(
				'number_scheduled' => ( ( $page - 1 ) * $args['posts_per_page'] ) + $count,
			),
		);

		$response = new \WP_REST_Response( $data );

		$response->add_link(
			'next',
			\add_query_arg(
				array( 'page' => ( $page + 1 ) ),
				\rest_url( 'pronamic-pay/v1/tools/' . $action . '/schedule' )
			)
		);

		$response->add_link(
			'scheduler',
			\add_query_arg(
				array(
					'page'   => 'action-scheduler',
					'status' => 'pending',
					's'      => $action,
				),
				\admin_url( 'tools.php' )
			)
		);

		return $response;
	}

	/**
	 * Action to trash follow-up payments without a transaction ID.
	 *
	 * @param int $payment_id Payment ID.
	 * @return void
	 */
	public function action_trash_follow_up_payment_without_transaction_id( $payment_id ) {
		// Get payment.
		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check transaction ID.
		$transaction_id = $payment->get_transaction_id();

		if ( ! empty( $transaction_id ) ) {
			return;
		}

		// Check subscriptions.
		$subscriptions = $payment->get_subscriptions();

		if ( empty( $subscriptions ) ) {
			return;
		}

		// Check if follow-up payment.
		foreach ( $subscriptions as $subscription ) {
			if ( ! $subscription->is_first_payment( $payment ) ) {
				continue;
			}

			// Bail out, this is a first payment.
			return;
		}

		// Go ahead, trash post.
		\wp_trash_post( $payment_id );
	}

	/**
	 * Action to check payment status for pending payments.
	 *
	 * @param int $payment_id Payment ID.
	 * @return void
	 */
	public function action_check_payment_status( $payment_id ) {
		// Get payment.
		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check payment status.
		if ( PaymentStatus::OPEN !== $payment->get_status() ) {
			return;
		}

		// Check if gateway supports status requests.
		$gateway = $payment->get_gateway();

		if ( ! $gateway->supports( 'payment_status_request' ) ) {
			return;
		}

		// Go ahead, schedule async status check with retries.
		StatusChecker::schedule_event( $payment, true );
	}

	/**
	 * Action to delete canceled follow-up payments.
	 *
	 * @param int $payment_id Payment ID.
	 * @return void
	 */
	public function action_delete_canceled_follow_up_payment( $payment_id ) {
		// Get payment.
		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check canceled payment status.
		if ( PaymentStatus::CANCELLED !== $payment->get_status() ) {
			return;
		}

		// Check subscriptions.
		$subscriptions = $payment->get_subscriptions();

		if ( empty( $subscriptions ) ) {
			return;
		}

		// Check if follow-up payment.
		foreach ( $subscriptions as $subscription ) {
			if ( ! $subscription->is_first_payment( $payment ) ) {
				continue;
			}

			// Bail out, this is a first payment.
			return;
		}

		// Go ahead, delete post.
		\wp_delete_post( $payment_id );
	}

	/**
	 * Action to reset subscription next payment date
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return void
	 */
	public function action_reset_subscription_next_payment_date_with_successful_payments( $subscription_id ) {
		// Get subscription.
		$subscription = \get_pronamic_subscription( $subscription_id );

		if ( null === $subscription ) {
			return;
		}

		// Check phases.
		$phases = $subscription->get_phases();

		if ( empty( $phases ) ) {
			return;
		}

		// Check current phase.
		$current_phase = $subscription->get_current_phase();

		if ( null === $current_phase ) {
			return;
		}

		// Count successful payments in current phase.
		$payments = $subscription->get_payments();

		$periods_created = 0;

		foreach ( $payments as $payment ) {
			// Check payment status.
			if ( PaymentStatus::SUCCESS !== $payment->get_status() ) {
				continue;
			}

			// Check periods.
			$periods = $payment->get_periods();

			foreach ( $periods as $period ) {
				// Check subscription.
				if ( $period->get_phase()->get_subscription()->get_id() !== $subscription_id ) {
					continue;
				}

				// Check if payment period is before current phase start date.
				// @todo Check phase sequence number instead?
				if ( $period->get_start_date() < $current_phase->get_start_date() ) {
					continue 2;
				}
			}

			$periods_created++;
		}

		$current_phase->set_periods_created( $periods_created );

		// Go ahead, recalculate next payment date.
		try {
			$subscription->set_next_payment_date( SubscriptionHelper::calculate_next_payment_date( $subscription ) );
			$subscription->set_next_payment_delivery_date( SubscriptionHelper::calculate_next_payment_delivery_date( $subscription ) );
			$subscription->set_expiry_date( $subscription->get_next_payment_date() );

			$subscription->save();
		} catch ( \Exception $e ) {
			return;
		}
	}
}
