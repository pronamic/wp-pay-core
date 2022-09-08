<?php
/**
 * Query actions scheduler
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Subscriptions
 */

namespace Pronamic\WordPress\Pay;

use WP_CLI;
use WP_Post;
use WP_Query;

/**
 * Query actions scheduler class
 */
class QueryActionsScheduler {
	/**
	 * Action name.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Query args.
	 *
	 * @var array
	 */
	private array $query_args;

	/**
	 * Action callback.
	 *
	 * @var callable
	 */
	private $callback;

	/**
	 * Construct and initialize a query actions scheduler.
	 *
	 * @return void
	 */
	public function __construct( string $name, array $query_args, callable $callback ) {
		$this->name       = $name;
		$this->query_args = $query_args;
		$this->callback   = $callback;

		\add_action( 'pronamic_pay_schedule_' . $this->name, [ $this, 'schedule_pages' ] );
		\add_action( 'pronamic_pay_schedule_page_' . $this->name, [ $this, 'schedule_actions' ], 10, 1 );
		\add_action( 'pronamic_pay_' . $this->name, [ $this, 'process_action' ], 10, 1 );

		$this->cli();
	}

	/**
	 * CLI.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-woocommerce.php#L365-L369
	 * @link https://github.com/woocommerce/woocommerce/blob/3.3.1/includes/class-wc-cli.php
	 * @link https://make.wordpress.org/cli/handbook/commands-cookbook/
	 * @return void
	 */
	private function cli() : void {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		WP_CLI::add_command(
			'pronamic-pay scheduler list ' . $this->name,
			function( $args, $assoc_args ) {
				WP_CLI::debug( 'Query posts to schedule actions for.' );

				$query = $this->get_query();

				WP_CLI::debug( \sprintf( 'Query executed: `found_posts` = %s, `max_num_pages`: %s.', $query->found_posts, $query->max_num_pages ) );

				WP_CLI\Utils\format_items(
					'table',
					$query->posts,
					[
						'ID',
						'post_title',
						'post_status',
					]
				);
			}
		);

		WP_CLI::add_command(
			'pronamic-pay scheduler schedule ' . $this->name,
			function( $args, $assoc_args ) {
				/**
				 * Schedule all pages.
				 */
				$all = \WP_CLI\Utils\get_flag_value( $assoc_args, 'all', false );

				if ( $all ) {
					WP_CLI::line( 'Scheduling all pages…' );

					$this->schedule_pages();

					return;
				}

				/**
				 * Schedule one page.
				 */
				$page = (int) \WP_CLI\Utils\get_flag_value( $assoc_args, 'page' );

				if ( $page > 0 ) {
					WP_CLI::line( \sprintf( 'Scheduling page %s…', $page ) );

					$action_id = $this->schedule_page( $page );

					WP_CLI::line( \sprintf( 'Action scheduled: %s', (string) $action_id ) );

					return;
				}
			}
		);

		WP_CLI::add_command(
			'pronamic-pay scheduler run ' . $this->name,
			function( $args, $assoc_args ) {
				/**
				 * Run action for specific post(s).
				 */
				foreach ( $args as $id ) {
					$post = \get_post( $id );

					if ( null === $post ) {
						WP_CLI::error( \sprintf( 'Could not find post with ID: %s', $id ) );

						exit;
					}

					WP_CLI::line( \sprintf( 'Calling action for post `%s`…', $id ) );

					\do_action( 'pronamic_pay_' . $this->name, $id );
				}
			}
		);
	}

	/**
	 * Get WordPress query.
	 *
	 * @param array $args Arguments.
	 * @return WP_Query
	 */
	private function get_query( $args = [] ) : WP_Query {
		$args = \wp_parse_args( $args, $this->query_args );

		if ( \array_key_exists( 'paged', $args ) ) {
			$args['no_found_rows'] = true;
		}

		return new WP_Query( $args );
	}

	/**
	 * Schedule start action.
	 *
	 * @return void
	 */
	public function schedule() : void {
		$hook = sprintf( 'pronamic_pay_schedule_%s', $this->name );

		$this->enqueue_async_action( $hook );
	}

	/**
	 * Schedule pages.
	 *
	 * @return void
	 */
	public function schedule_pages() : void {
		$query = $this->get_query();

		$num_pages = $query->max_num_pages;

		if ( $num_pages > 0 ) {
			$pages = \range( $num_pages, 1 );

			foreach ( $pages as $page ) {
				$this->schedule_page( $page );
			}
		}
	}

	/**
	 * Schedule page.
	 *
	 * @param int $page Page.
	 * @return int|null
	 */
	private function schedule_page( $page ) : ?int {
		return $this->enqueue_async_action(
			'pronamic_pay_schedule_page_' . $this->name,
			[
				'page' => $page,
			]
		);
	}

	/**
	 * Schedule actions.
	 *
	 * @param int $page Page.
	 * @return void
	 */
	public function schedule_actions( $page ) : void {
		$query = $this->get_query( [ 'paged' => $page ] );

		$posts = \array_filter(
			$query->posts,
			function( $post ) {
				return ( $post instanceof WP_Post );
			}
		);

		foreach ( $posts as $post ) {
			$this->schedule_action( $post );
		}
	}

	/**
	 * Schedule action.
	 *
	 * @param WP_Post $post Post.
	 * @return int|null
	 */
	private function schedule_action( WP_Post $post ) : ?int {
		$action_id_meta_key = sprintf( 'pronamic_pay_scheduler_%s_action_id', $this->name );

		// Check pending action ID.
		$action_id = \get_post_meta( $post->ID, $action_id_meta_key, true );

		if ( ! empty( $action_id ) ) {
			return $action_id;
		}

		// Enqueue async action.
		$action_id = $this->enqueue_async_action(
			\sprintf( 'pronamic_pay_%s', $this->name ),
			[
				'post_id' => $post->ID,
			]
		);

		if ( ! empty( $action_id ) ) {
			\update_post_meta( $post->ID, $action_id_meta_key, $action_id );
		}

		return $action_id;
	}

	/**
	 * Process action.
	 *
	 * @param string $post_id Post ID.
	 * @return void
	 */
	public function process_action( string $post_id ) : void {
		// Delete action ID post meta.
		$action_id_meta_key = sprintf( 'pronamic_pay_scheduler_%s_action_id', $this->name );

		\delete_post_meta( (int) $post_id, $action_id_meta_key );

		// Call callback.
		$callback = $this->callback;

		if ( \is_callable( $callback ) ) {
			\call_user_func( $callback, $post_id );
		}
	}

	/**
	 * Enqueue async action.
	 *
	 * @param string $hook Action hook name.
	 * @param array  $args Action arguments.
	 * @return int|null
	 */
	private function enqueue_async_action( string $hook, array $args = [] ) : ?int {
		if ( false !== \as_next_scheduled_action( $hook, $args, 'pronamic-pay' ) ) {
			return null;
		}

		return \as_enqueue_async_action( $hook, $args, 'pronamic-pay' );
	}
}
