<?php
/**
 * Meta Box Payment Refunds
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

if ( empty( $payment->refunds ) ) : ?>

	<p>
		<?php \esc_html_e( 'No payment refunds found.', 'pronamic_ideal' ); ?>
	</p>

<?php else : ?>

	<div class="pronamic-pay-table-responsive">
		<table class="pronamic-pay-table widefat">
			<thead>
				<tr>
					<th scope="col"><?php \esc_html_e( 'Date', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'By', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Amount', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Description', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'PSP ID', 'pronamic_ideal' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>

			<tbody>

				<?php foreach ( $payment->refunds as $refund ) : ?>

					<tr>
						<td><?php echo \esc_html( $refund->created_at->format_i18n() ); ?></td>
						<td><?php echo \esc_html( $refund->created_by->display_name ); ?></td>
						<td><?php echo \esc_html( $refund->get_amount()->format_i18n() ); ?></td>
						<td><?php echo \esc_html( $refund->get_description() ); ?></td>
						<td><?php echo \esc_html( $refund->psp_id ); ?></td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>
