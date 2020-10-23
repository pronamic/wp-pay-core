<?php
/**
 * Meta Box Payment Subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Util;

$payment_id = get_the_ID();

if ( empty( $payment_id ) ) {
	return;
}

$payment = get_pronamic_payment( $payment_id );

if ( null === $payment ) {
	return;
}

$subscription = $payment->get_subscription();

if ( $subscription ) :

	$phase = $subscription->get_display_phase();

	?>

	<table class="form-table">
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Subscription', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php edit_post_link( get_the_title( $subscription->post->ID ), '', '', $subscription->post->ID ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Status', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$status_object = get_post_status_object( get_post_status( $subscription->post->ID ) );

				if ( isset( $status_object, $status_object->label ) ) {
					echo esc_html( $status_object->label );
				} else {
					echo '—';
				}

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Description', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( $subscription->get_description() ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Amount', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				if ( null !== $phase ) :

					echo esc_html( $phase->get_amount()->format_i18n() );

				endif;

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php echo esc_html_x( 'Recurrence', 'Recurring payment', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				if ( null === $phase || 1 === $phase->get_total_periods() ) :
					// No recurrence.
					echo '—';

				elseif ( $phase->is_infinite() ) :
					// Infinite.
					echo esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) );

				else :
					// Fixed number of recurrences.
					printf(
						'%s (%s)',
						esc_html( strval( Util::format_recurrences( $phase->get_interval() ) ) ),
						esc_html( strval( Util::format_frequency( $phase->get_total_periods() ) ) )
					);

				endif;

				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Source', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $subscription->get_source_text();

				?>
			</td>
		</tr>
	</table>

<?php else : ?>

	<p>
		<?php esc_html_e( 'This payment is not related to a subscription.', 'pronamic_ideal' ); ?>
	</p>

<?php endif; ?>
