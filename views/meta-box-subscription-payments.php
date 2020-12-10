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

<?php if ( empty( $payments ) ) : ?>

	<?php esc_html_e( 'No payments found.', 'pronamic_ideal' ); ?>

<?php else : ?>

	<table class="pronamic-pay-table widefat">
		<thead>
			<tr>
				<th scope="col">
					<span class="pronamic-pay-tip pronamic-pay-icon pronamic-pay-status" title="<?php esc_attr_e( 'Status', 'pronamic_ideal' ); ?>"><?php esc_html_e( 'Status', 'pronamic_ideal' ); ?></span>
				</th>
				<th scope="col"><?php esc_html_e( 'Payment', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Transaction', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Start Date', 'pronamic_ideal' ); ?></th>
				<th scope="col"><?php esc_html_e( 'End Date', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $payments as $payment_data ) : ?>

				<?php

				$payment = $payment_data['payment'];

				$payment_id         = $payment->get_id();
				$payments_post_type = get_post_type( $payment_id );

				// Get subscription period from payment.
				$period = null;

				$periods = $payment->get_periods();

				if ( null !== $periods ) :

					foreach ( $periods as $subscription_period ) :

						if ( $subscription->get_id() === $subscription_period->get_phase()->get_subscription()->get_id() ) :

							$period = $subscription_period;

							break;

						endif;

					endforeach;

				endif;

				?>

				<tr>
					<td>
						<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_status', $payment_id ); ?>
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
					<td>
						<?php

						$start_date = ( null !== $period ? $period->get_start_date() : $payment->start_date );

						echo esc_html( null === $start_date ? '—' : $start_date->format_i18n() );

						?>
					</td>
					<td>
						<?php

						$end_date = ( null !== $period ? $period->get_end_date() : $payment->end_date );

						echo esc_html( null === $end_date ? '—' : $end_date->format_i18n() );

						?>
					</td>
				</tr>

				<?php if ( $this->plugin->subscriptions_module->can_retry_payment( $payment ) ) : ?>

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

				<?php if ( null !== $payment_data['children'] ) : ?>

					<?php $payment_data['children'] = \array_reverse( $payment_data['children'] ); ?>

					<?php foreach ( $payment_data['children'] as $child_payment ) : ?>

						<?php

						$child_payment_id = $child_payment->get_id();

						// Get subscription period from payment.
						$period = null;

						$periods = $child_payment->get_periods();

						if ( null !== $periods ) :

							foreach ( $periods as $subscription_period ) :

								if ( $subscription->get_id() === $subscription_period->get_phase()->get_subscription()->get_id() ) :

									$period = $subscription_period;

									break;

								endif;

							endforeach;

						endif;

						?>

						<tr>
							<td>
								<?php \do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_status', $child_payment_id ); ?>
							</td>
							<td>
								&nbsp;&nbsp;&nbsp;
								<?php \do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_title', $child_payment_id ); ?>
							</td>
							<td>
								<?php \do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_transaction', $child_payment_id ); ?>
							</td>
							<td>
								<?php \do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_amount', $child_payment_id ); ?>
							</td>
							<td>
								<?php \do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_date', $child_payment_id ); ?>
							</td>
							<td>
								<?php

								$start_date = ( null !== $period ? $period->get_start_date() : $payment->start_date );

								echo \esc_html( null === $start_date ? '—' : $start_date->format_i18n() );

								?>
							</td>
							<td>
								<?php

								$end_date = ( null !== $period ? $period->get_end_date() : $payment->end_date );

								echo \esc_html( null === $end_date ? '—' : $end_date->format_i18n() );

								?>
							</td>
						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			<?php endforeach; ?>

		</tbody>
	</table>

<?php endif; ?>
