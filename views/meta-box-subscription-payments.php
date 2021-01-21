<?php
/**
 * Meta Box Subscription Payments
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

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

			$next_period = $subscription->next_period();

			$gateway = Plugin::get_gateway( $subscription->get_config_id() );

			$allow_next_period_statuses = array( SubscriptionStatus::OPEN, SubscriptionStatus::ACTIVE, SubscriptionStatus::FAILURE );

			if ( null !== $next_period && \in_array( $subscription->get_status(), $allow_next_period_statuses, true ) && null !== $gateway && $gateway->supports( 'recurring' ) ) :

				?>

				<tr>
					<td>&nbsp;</td>
					<td><?php echo \esc_html( $next_period->human_readable_range() ); ?></td>
					<td colspan="2">
						<?php

						$create_next_payment_url = wp_nonce_url(
							add_query_arg( 'pronamic_next_period', true, \get_edit_post_link( $subscription->get_id() ) ),
							'pronamic_next_period_' . $subscription->get_id()
						);

						if ( in_array( $subscription->get_source(), array( 'woocommerce' ), true ) ) :

							echo wp_kses_post(
								sprintf(
									/* translators: %s: next payment date */
									__( 'Will be created on %s', 'pronamic_ideal' ),
									\esc_html( $subscription->get_next_payment_date()->format_i18n() )
								)
							);

						else :

							echo wp_kses_post(
								sprintf(
									/* translators: 1: next payment delivery date, 2: create next period payment anchor */
									__( 'Will be created on %1$s or %2$s', 'pronamic_ideal' ),
									\esc_html( $subscription->get_next_payment_delivery_date()->format_i18n() ),
									\sprintf(
										'<a href="%1$s">%2$s</a>',
										\esc_url( $create_next_payment_url ),
										\esc_html( \__( 'create now', 'pronamic_ideal' ) )
									)
								)
							);

						endif;

						?>
					</td>
					<td><?php echo \esc_html( $next_period->get_amount()->format_i18n() ); ?></td>
					<td>—</td>
				</tr>

				<?php

			endif;

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
