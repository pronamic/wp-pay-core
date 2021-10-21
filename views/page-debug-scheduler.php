<?php
/**
 * Page Debug Scheduler
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 * @var object $tool Tool.
 */

?>

<div class="wrap pronamic-pay-debug">
	<h1 class="wp-heading-inline"><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<h2><?php echo \esc_html( $tool->title ); ?></h2>

	<table class="widefat">
		<tr>
			<td>
				<strong>
					<?php echo \esc_html( $tool->label ); ?>
				</strong>

				&mdash; <span class="pronamic-pay-debug-progress-count-scheduled">0</span> /
				<span class="pronamic-pay-debug-progress-count-total">…</span>
			</td>
		</tr>
		<tr>
			<td>
				<div class="pronamic-pay-debug-progress" style="background: #dddddd; border-radius: 3px;">
					<div class="pronamic-pay-debug__bar" style="box-sizing: border-box; padding: 5px; white-space: nowrap; min-width: 35px; width: 0px; border-radius: 3px; text-align: right;">
						<span class="pronamic-pay-debug__bar__status">0 %</span>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<button id="pronamic-pay-debug-scheduler-start" class="button button-large button-primary" disabled="disabled">
					<?php esc_html_e( 'Start', 'pronamic_ideal' ); ?>
				</button>

				<a href="#" id="pronamic-pay-debug-scheduler-pending" class="button button-large button-primary" style="display:none;">
					<?php esc_html_e( 'View pending scheduled actions', 'pronamic_ideal' ); ?>
				</a>
			</td>
		</tr>
	</table>
</div>

<?php wp_print_scripts( 'pronamic-pay-admin-debug-scheduler' ); ?>
