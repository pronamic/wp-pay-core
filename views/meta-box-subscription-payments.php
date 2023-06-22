<?php
/**
 * Meta Box Subscription Payments
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 * @var \Pronamic\WordPress\Pay\Subscriptions\Subscription $subscription Subscription.
 */

use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

$subscription_id = $subscription->get_id();

if ( null === $subscription_id ) {
	return;
}

?>

<?php if ( 0 === count( $subscription->get_payments() ) ) : ?>

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

			$next_payment_date = $subscription->get_next_payment_date();

			$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

			$next_period = $subscription->get_next_period();

			$gateway = Plugin::get_gateway( (int) $subscription->get_config_id() );

			$allow_next_period_statuses = [ SubscriptionStatus::OPEN, SubscriptionStatus::ACTIVE, SubscriptionStatus::FAILURE ];

			if ( null !== $next_period && \in_array( $subscription->get_status(), $allow_next_period_statuses, true ) && null !== $gateway && $gateway->supports( 'recurring' ) ) :

				?>

				<tr>
					<td>&nbsp;</td>
					<td><?php echo \esc_html( $next_period->human_readable_range() ); ?></td>
					<td colspan="2">
						<?php

						if ( in_array( $subscription->get_source(), [ 'woocommerce' ], true ) && null !== $next_payment_date ) :

							echo wp_kses_post(
								sprintf(
									/* translators: %s: next payment date */
									__( 'Will be created on %s', 'pronamic_ideal' ),
									\esc_html( $next_payment_date->format_i18n( __( 'D j M Y', 'pronamic_ideal' ) ) )
								)
							);

						elseif ( null !== $next_payment_delivery_date ) :

							$create_next_payment_url = \wp_nonce_url(
								\add_query_arg(
									\urlencode_deep(
										[
											'period_payment' => true,
											'subscription_id' => $subscription->get_id(),
											'sequence_number' => $next_period->get_phase()->get_sequence_number(),
											'start_date' => $next_period->get_start_date()->format( DATE_ATOM ),
											'end_date'   => $next_period->get_end_date()->format( DATE_ATOM ),
										]
									),
									\get_edit_post_link( $subscription_id )
								),
								'pronamic_period_payment_' . $subscription->get_id()
							);

							echo wp_kses_post(
								sprintf(
									/* translators: 1: next payment delivery date, 2: create next period payment anchor */
									__( 'Will be created on %1$s or %2$s', 'pronamic_ideal' ),
									\esc_html( $next_payment_delivery_date->format_i18n( __( 'D j M Y', 'pronamic_ideal' ) ) ),
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

			$data = [];

			foreach ( $subscription->get_payments() as $payment ) {
				$key = sprintf(
					'%s-%s',
					$payment->get_date()->getTimestamp(),
					$payment->get_id()
				);

				$item = [
					'period'   => null,
					'payments' => [
						$key => $payment,
					],
				];

				// Maybe bundle payments for period.
				$periods = $payment->get_periods();

				if ( null !== $periods ) {
					foreach ( $periods as $period ) {
						if ( $subscription->get_id() !== $period->get_phase()->get_subscription()->get_id() ) {
							continue;
						}

						$item['period'] = $period;

						$key = $period->get_start_date()->getTimestamp();

						// Add payment if period already exists in data.
						if ( \array_key_exists( $key, $data ) ) {
							$data[ $key ]['payments'] = $data[ $key ]['payments'] + $item['payments'];

							$item = null;
						}

						break;
					}
				}

				// Add item.
				if ( null !== $item ) {
					$data[ $key ] = $item;
				}
			}

			// Add items to result.
			$result = [];

			foreach ( $data as $item ) {
				\ksort( $item['payments'] );

				// Determine wether payment for period can be retried.
				$can_retry = false;

				if ( null !== $item['period'] ) {
					$has_open_or_success = array_intersect(
						\wp_list_pluck( $item['payments'], 'status' ),
						[
							PaymentStatus::OPEN,
							PaymentStatus::SUCCESS,
						]
					);

					$can_retry = empty( $has_open_or_success );
				}

				$item['can_retry'] = $can_retry;

				// Add item to result using (first) payment date as key for sorting.
				$payment = reset( $item['payments'] );

				$key = sprintf(
					'%s-%s',
					$payment->get_date()->getTimestamp(),
					$payment->get_id()
				);

				$result[ $key ] = $item;
			}

			// Sort items by date.
			\krsort( $result );

			foreach ( $result as $sort_key => $item ) :
				$period = $item['period'];

				$is_first = ( null !== $period );

				foreach ( $item['payments'] as $payment ) :
					$payment_id         = $payment->get_id();
					$payments_post_type = get_post_type( $payment_id );

					?>

					<tr>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_status', $payment_id ); ?>
						</td>
						<td>
							<?php

							if ( null !== $period ) :
								$prefix = ( $is_first ? '' : '⌙ ' );

								echo esc_html( $prefix . $period->human_readable_range() );

							endif;

							?>
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

					<?php if ( $is_first && $item['can_retry'] && $plugin->subscriptions_module->can_retry_payment( $payment ) ) : ?>

						<tr>
							<td>&nbsp;</td>
							<td colspan="6">
								<?php

								$action_url = \wp_nonce_url(
									\add_query_arg(
										\urlencode_deep(
											[
												'period_payment' => true,
												'subscription_id' => $subscription->get_id(),
												'sequence_number' => $period->get_phase()->get_sequence_number(),
												'start_date' => $period->get_start_date()->format( DATE_ATOM ),
												'end_date' => $period->get_end_date()->format( DATE_ATOM ),
											]
										),
										\get_edit_post_link( $subscription_id )
									),
									'pronamic_period_payment_' . $subscription->get_id()
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
