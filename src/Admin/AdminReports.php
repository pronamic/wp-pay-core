<?php
/**
 * Admin Report
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Admin
 */

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * WordPress admin reports
 *
 * @author  Remco Tolsma
 * @version 2.2.6
 * @since   1.0.0
 */
class AdminReports {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * AdminReports constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		add_action( 'admin_print_styles', [ $this, 'admin_css' ] );
	}

	/**
	 * Page reports.
	 *
	 * @return void
	 */
	public function page_reports() {
		$admin_reports = $this;

		include __DIR__ . '/../../views/page-reports.php';
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	public function admin_css() {
		// Check if this is the reports page.
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( 'pronamic_pay_reports' !== $page ) {
			return;
		}

		$min = \SCRIPT_DEBUG ? '' : '.min';

		// Flot - http://www.flotcharts.org/.
		$flot_version = '0.8.0-alpha';

		wp_register_script(
			'flot',
			plugins_url( '../../assets/flot/jquery.flot' . $min . '.js', __FILE__ ),
			[ 'jquery' ],
			$flot_version,
			true
		);

		wp_register_script(
			'flot-time',
			plugins_url( '../../assets/flot/jquery.flot.time' . $min . '.js', __FILE__ ),
			[ 'flot' ],
			$flot_version,
			true
		);

		wp_register_script(
			'flot-resize',
			plugins_url( '../../assets/flot/jquery.flot.resize' . $min . '.js', __FILE__ ),
			[ 'flot' ],
			$flot_version,
			true
		);

		// Accounting.js - http://openexchangerates.github.io/accounting.js.
		wp_register_script(
			'accounting',
			plugins_url( '../../assets/accounting/accounting' . $min . '.js', __FILE__ ),
			[ 'jquery' ],
			'0.4.1',
			true
		);

		// Reports.
		wp_register_script(
			'pronamic-pay-admin-reports',
			plugins_url( '../../js/dist/admin-reports' . $min . '.js', __FILE__ ),
			[
				'jquery',
				'flot',
				'flot-time',
				'flot-resize',
				'accounting',
			],
			$this->plugin->get_version(),
			true
		);

		global $wp_locale;

		wp_localize_script(
			'pronamic-pay-admin-reports',
			'pronamicPayAdminReports',
			[
				'data'       => $this->get_reports(),
				'monthNames' => array_values( $wp_locale->month_abbrev ),
			]
		);

		// Enqueue.
		wp_enqueue_script( 'pronamic-pay-admin-reports' );
	}

	/**
	 * Get reports.
	 *
	 * @return array
	 */
	public function get_reports() {
		$start = new \DateTime( 'First day of January' );
		$end   = new \DateTime( 'Last day of December' );

		$data = [
			(object) [
				'label'      => __( 'Number successful payments', 'pronamic_ideal' ),
				'data'       => $this->get_report( 'payment_completed', 'COUNT', $start, $end ),
				'color'      => '#dbe1e3',
				'bars'       => (object) [
					'fillColor' => '#dbe1e3',
					'fill'      => true,
					'show'      => true,
					'lineWidth' => 0,
					'barWidth'  => 2419200000 * 0.5,
					'align'     => 'center',
				],
				'shadowSize' => 0,
				'hoverable'  => false,
				'class'      => 'completed-count',
			],
			(object) [
				'label'            => __( 'Open payments', 'pronamic_ideal' ),
				'data'             => $this->get_report( 'payment_pending', 'SUM', $start, $end ),
				'yaxis'            => 2,
				'color'            => '#b1d4ea',
				'points'           => (object) [
					'show'      => true,
					'radius'    => 5,
					'lineWidth' => 2,
					'fillColor' => '#FFF',
					'fill'      => true,
				],
				'lines'            => (object) [
					'show'      => true,
					'lineWidth' => 2,
					'fill'      => false,
				],
				'shadowSize'       => 0,
				'tooltipFormatter' => 'money',
				'class'            => 'pending-sum',
			],
			(object) [
				'label'            => __( 'Successful payments', 'pronamic_ideal' ),
				'data'             => $this->get_report( 'payment_completed', 'SUM', $start, $end ),
				'yaxis'            => 2,
				'color'            => '#3498db',
				'points'           => (object) [
					'show'      => true,
					'radius'    => 6,
					'lineWidth' => 4,
					'fillColor' => '#FFF',
					'fill'      => true,
				],
				'lines'            => (object) [
					'show'      => true,
					'lineWidth' => 5,
					'fill'      => false,
				],
				'shadowSize'       => 0,
				'prepend_tooltip'  => '&euro;&nbsp;',
				'tooltipFormatter' => 'money',
				'class'            => 'completed-sum',
			],
			(object) [
				'label'            => __( 'Cancelled payments', 'pronamic_ideal' ),
				'data'             => $this->get_report( 'payment_cancelled', 'SUM', $start, $end ),
				'yaxis'            => 2,
				'color'            => '#F1C40F',
				'points'           => (object) [
					'show'      => true,
					'radius'    => 5,
					'lineWidth' => 2,
					'fillColor' => '#FFF',
					'fill'      => true,
				],
				'lines'            => (object) [
					'show'      => true,
					'lineWidth' => 2,
					'fill'      => false,
				],
				'shadowSize'       => 0,
				'prepend_tooltip'  => '&euro;&nbsp;',
				'tooltipFormatter' => 'money',
				'class'            => 'cancelled-sum',
			],
			(object) [
				'label'            => __( 'Expired payments', 'pronamic_ideal' ),
				'data'             => $this->get_report( 'payment_expired', 'SUM', $start, $end ),
				'yaxis'            => 2,
				'color'            => '#DBE1E3',
				'points'           => (object) [
					'show'      => true,
					'radius'    => 5,
					'lineWidth' => 2,
					'fillColor' => '#FFF',
					'fill'      => true,
				],
				'lines'            => (object) [
					'show'      => true,
					'lineWidth' => 2,
					'fill'      => false,
				],
				'shadowSize'       => 0,
				'prepend_tooltip'  => '&euro;&nbsp;',
				'tooltipFormatter' => 'money',
				'class'            => 'expired-sum',
			],
			(object) [
				'label'            => __( 'Failed payments', 'pronamic_ideal' ),
				'data'             => $this->get_report( 'payment_failed', 'SUM', $start, $end ),
				'yaxis'            => 2,
				'color'            => '#E74C3C',
				'points'           => (object) [
					'show'      => true,
					'radius'    => 5,
					'lineWidth' => 2,
					'fillColor' => '#FFF',
					'fill'      => true,
				],
				'lines'            => (object) [
					'show'      => true,
					'lineWidth' => 2,
					'fill'      => false,
				],
				'shadowSize'       => 0,
				'prepend_tooltip'  => '&euro;&nbsp;',
				'tooltipFormatter' => 'money',
				'class'            => 'failed-sum',
			],
		];

		foreach ( $data as $serie ) {
			// @codingStandardsIgnoreStart
			$serie->legendValue = array_sum( wp_list_pluck( $serie->data, 1 ) );
			// @codingStandardsIgnoreEnd
		}

		return $data;
	}

	/**
	 * Get report.
	 *
	 * @link https://github.com/woothemes/woocommerce/blob/2.3.11/assets/js/admin/reports.js
	 * @link https://github.com/woothemes/woocommerce/blob/master/includes/admin/reports/class-wc-report-sales-by-date.php
	 *
	 * @param string    $status   Status.
	 * @param string    $function Function.
	 * @param \DateTime $start    Start date.
	 * @param \DateTime $end      End date.
	 *
	 * @return array
	 *
	 * @throws \Exception Throws exception on date interval error.
	 */
	private function get_report( $status, $function, $start, $end ) {
		global $wpdb;

		$interval = new \DateInterval( 'P1M' );
		$period   = new \DatePeriod( $start, $interval, $end );

		$date_format = '%Y-%m';

		/* phpcs:ignore WordPress.DB.DirectDatabaseQuery */
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT
					DATE_FORMAT( post.post_date, %s ) AS month,
			  		post.ID
				FROM
					$wpdb->posts AS post
				WHERE
					post.post_type = 'pronamic_payment'
						AND
					post.post_date BETWEEN %s AND %s
						AND
					post.post_status = %s
				ORDER BY
					post_date
				;
				",
				$date_format,
				$start->format( 'Y-m-d' ),
				$end->format( 'Y-m-d' ),
				$status
			)
		);

		$months = wp_list_pluck( $results, 'month' );

		switch ( $function ) {
			case 'COUNT':
				$data = array_count_values( $months );

				break;
			case 'SUM':
				$data = array_fill_keys(
					$months,
					0
				);

				foreach ( $results as $post ) {
					$payment = new Payment( $post->ID );

					$data[ $post->month ] += $payment->get_total_amount()->get_value();
				}

				break;
		}

		$report = [];

		foreach ( $period as $date ) {
			$key = $date->format( 'Y-m' );

			$value = 0;

			if ( isset( $data[ $key ] ) ) {
				$value = (float) $data[ $key ];
			}

			$report[] = [
				// Flot requires milliseconds so multiply with 1000.
				$date->getTimestamp() * 1000,
				$value,
			];
		}

		return $report;
	}
}
