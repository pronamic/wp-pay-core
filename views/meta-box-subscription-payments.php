<?php
/**
 * Meta Box Subscription Payments
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

?>

<?php if ( empty( $periods ) ) : ?>

	<?php esc_html_e( 'No payments found.', 'pronamic_ideal' ); ?>

<?php else : ?>

	<table class="pronamic-pay-table widefat">
		<thead>
			<tr>
				<th scope="col">
					<span class="pronamic-pay-tip pronamic-pay-icon pronamic-pay-status" title="<?php esc_attr_e( 'Status', 'pronamic_ideal' ); ?>"><?php esc_html_e( 'Status', 'pronamic_ideal' ); ?></span>
				</th>
				<th scope="col"><?php esc_html_e( 'Period', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Payment', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Transaction', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php

			foreach ( $periods as $period ) :

				$is_first = true;

				$can_retry = $period['can_retry'];

				$payments = $period['payments'];

				$period = $period['period'];

				?>

				<?php foreach ( $payments as $payment ) : ?>

					<?php

					$payment_id         = $payment->get_id();
					$payments_post_type = get_post_type( $payment_id );

					?>

					<tr>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_status', $payment_id ); ?>
						</td>
						<td>
							<?php

							if ( ! $is_first ) :

								echo esc_html( '⌙ ' );

							endif;

							?>

							<?php echo esc_html( $period->human_readable_range() ); ?>
						</td>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_title', $payment_id ); ?>
						</td>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_transaction', $payment_id ); ?>
						</td>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_amount', $payment_id ); ?>
						</td>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_date', $payment_id ); ?>
						</td>
					</tr>

					<?php if ( $is_first && $can_retry && $this->plugin->subscriptions_module->can_retry_payment( $payment ) ) : ?>

						<tr>
							<td>&nbsp;</td>
							<td colspan="6">
								<?php

								$action_url = \wp_nonce_url(
									\add_query_arg(
										array(
											'pronamic_retry_payment' => $payment_id,
										),
										\get_edit_post_link( $post->ID )
									),
									'pronamic_retry_payment_' . $payment_id
								);

								\printf(
									'<p><a class="button" href="%s">%s</a></p>',
									\esc_url( $action_url ),
									\esc_html(
										sprintf(
											/* translators: %d: payment ID */
											__( 'Retry payment #%d', 'pronamic_ideal' ),
											$payment_id
										)
									)
								);

								?>
							</td>
						</tr>

					<?php endif; ?>

					<?php

					$is_first = false;

				endforeach;

				?>

			<?php endforeach; ?>

		</tbody>
	</table>

<?php endif; ?>
