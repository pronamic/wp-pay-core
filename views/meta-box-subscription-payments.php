<?php
/**
 * Meta Box Subscription Payments
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 * @var \Pronamic\WordPress\Pay\Subscriptions\Subscription $subscription Subscription.
 */

use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subscription_id = $subscription->get_id();

if ( null === $subscription_id ) {
	return;
}

$payments = $subscription->get_payments();

$data = [];

foreach ( $payments as $payment ) {
	$periods = (array) $payment->get_periods();

	/**
	 * A payment can be for multiple and different subscription periods.
	 * Here we only want to show the periods of this subscription and
	 * therefore we filter out other periods.
	 */
	$periods = \array_filter(
		$periods,
		function ( $period ) use ( $subscription ) {
			return ( $subscription->get_id() === $period->get_phase()->get_subscription()->get_id() );
		}
	);

	if ( 0 === count( $periods ) ) {
		$key = 'payment-' . $payment->get_id();

		$data[ $key ] = (object) [
			'date'     => $payment->get_date(),
			'payments' => [ $payment ],
			'period'   => null,
		];
	}

	foreach ( $periods as $period ) {
		$key = 'period-' . $period->get_start_date()->getTimestamp();

		if ( ! array_key_exists( $key, $data ) ) {
			$data[ $key ] = (object) [
				'date'     => $payment->get_date(),
				'payments' => [],
				'period'   => $period,
			];
		}

		$data[ $key ]->payments[] = $payment;
	}
}

foreach ( $data as $item ) {
	usort(
		$item->payments,
		function ( $a, $b ) {
			return $a->get_date() <=> $b->get_date();
		}
	);

	$item->first = reset( $item->payments );

	if ( false !== $item->first ) {
		$item->date = $item->first->get_date();
	}

	$statuses = array_map(
		function ( $payment ) {
			return $payment->get_status();
		},
		$item->payments
	);

	$item->can_retry = ! ( in_array( PaymentStatus::OPEN, $statuses, true ) || in_array( PaymentStatus::SUCCESS, $statuses, true ) );
}

usort(
	$data,
	function ( $a, $b ) {
		return $b->date <=> $a->date;
	}
);

if ( 0 === count( $payments ) ) : ?>

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

			foreach ( $data as $item ) :
				foreach ( $item->payments as $payment ) :
					$payment_id         = $payment->get_id();
					$payments_post_type = get_post_type( $payment_id );

					?>

					<tr>
						<td>
							<?php do_action( 'manage_' . $payments_post_type . '_posts_custom_column', 'pronamic_payment_status', $payment_id ); ?>
						</td>
						<td>
							<?php

							if ( null !== $item->period ) :
								$prefix = ( $payment === $item->first ? '' : '⌙ ' );

								echo esc_html( $prefix . $item->period->human_readable_range() );

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

					<?php if ( null !== $item->period && $payment === $item->first && $item->can_retry && $plugin->subscriptions_module->can_retry_payment( $payment ) ) : ?>

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
												'sequence_number' => $item->period->get_phase()->get_sequence_number(),
												'start_date' => $item->period->get_start_date()->format( DATE_ATOM ),
												'end_date' => $item->period->get_end_date()->format( DATE_ATOM ),
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

				<?php endforeach; ?>

			<?php endforeach; ?>

		</tbody>
	</table>

<?php endif; ?>
