<?php
/**
 * Meta Box Payment Subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Payments\Payment $payment Payment.
 */

use Pronamic\WordPress\Pay\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subscriptions = $payment->get_subscriptions();

if ( empty( $subscriptions ) ) : ?>

	<p>
		<?php esc_html_e( 'This payment is not related to a subscription.', 'pronamic_ideal' ); ?>
	</p>

<?php else : ?>

	<?php foreach ( $subscriptions as $subscription ) : ?>

		<?php

		$subscription_id = $subscription->get_id();

		$phase = $subscription->get_display_phase();

		?>

		<table class="form-table">

			<?php if ( null !== $subscription_id ) : ?>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Subscription', 'pronamic_ideal' ); ?>
					</th>
					<td>
						<?php edit_post_link( get_the_title( $subscription_id ), '', '', $subscription_id ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Status', 'pronamic_ideal' ); ?>
					</th>
					<td>
						<?php

						$status_object = get_post_status_object( (string) get_post_status( $subscription_id ) );

						if ( isset( $status_object, $status_object->label ) ) {
							echo esc_html( $status_object->label );
						} else {
							echo '—';
						}

						?>
					</td>
				</tr>

			<?php endif; ?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Description', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo esc_html( (string) $subscription->get_description() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					if ( null !== $phase ) {
						echo esc_html( $phase->get_amount()->format_i18n() );
					}

					?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo esc_html_x( 'Recurrence', 'Recurring payment', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					$total_periods = ( null === $phase ) ? null : $phase->get_total_periods();

					if ( null === $phase || 1 === $total_periods ) {
						// No recurrence.
						echo '—';
					} elseif ( null === $total_periods ) {
						// Infinite.
						echo esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) );
					} else {
						// Fixed number of recurrences.
						printf(
							'%s (%s)',
							esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) ),
							esc_html( strval( Util::format_frequency( $total_periods ) ) )
						);
					}

					?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Source', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php

					echo wp_kses(
						$subscription->get_source_text(),
						[
							'a'  => [
								'href' => true,
							],
							'br' => [],
						]
					);

					?>
				</td>
			</tr>
		</table>

	<?php endforeach; ?>

<?php endif; ?>
