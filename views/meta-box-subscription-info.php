<?php
/**
 * Meta Box Subscription Info
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Pay\Util;

$subscription_id = $subscription->get_id();

$customer = $subscription->get_customer();
$user_id  = is_null( $customer ) ? null : $customer->get_user_id();

$phase = $subscription->get_display_phase();

?>
<table class="form-table">
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Date', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php the_time( __( 'l jS \o\f F Y, h:ia', 'pronamic_ideal' ) ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'ID', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( $subscription_id ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Status', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$status_object = get_post_status_object( get_post_status( $subscription_id ) );

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
			<?php esc_html_e( 'Gateway', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php edit_post_link( get_the_title( $subscription->config_id ), '', '', $subscription->config_id ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( PaymentMethods::get_name( $subscription->payment_method ) ); ?>
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
			<?php echo esc_html__( 'Recurrence', 'pronamic_ideal' ); ?>
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
			<?php esc_html_e( 'Start Date', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$start_date = $subscription->get_start_date();

			echo empty( $start_date ) ? '—' : esc_html( $start_date->format_i18n() );

			?>
		</td>
	</tr>

	<?php

	$end_date = ( null === $phase ? null : $phase->get_end_date() );

	// Show end date if frequency is limited.
	if ( null !== $end_date ) :

		?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'End Date', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				echo esc_html( ( new DateTime( $end_date->format( \DATE_ATOM ) ) )->format_i18n() );

				?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Paid up to', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$expiry_date = $subscription->get_expiry_date();

			echo empty( $expiry_date ) ? '—' : esc_html( $expiry_date->format_i18n() );

			?>
		</td>
	</tr>

	<?php

	// Show next payment (delivery) date if subscription is not cancelled or completed.
	if ( ! in_array( $subscription->get_status(), array( SubscriptionStatus::CANCELLED, SubscriptionStatus::COMPLETED, SubscriptionStatus::EXPIRED ), true ) ) :

		?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Next Payment Date', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$next_payment_date = $subscription->get_next_payment_date();

				echo empty( $next_payment_date ) ? '—' : esc_html( $next_payment_date->format_i18n() );

				?>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Next Payment Delivery Date', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$next_payment_delivery_date = $subscription->get_next_payment_delivery_date();

				echo empty( $next_payment_delivery_date ) ? '—' : esc_html( $next_payment_delivery_date->format_i18n() );

				?>
			</td>
		</tr>

	<?php endif; ?>

	<?php

	$customer = $subscription->get_customer();

	if ( null !== $customer ) :

		$text = \strval( $customer->get_name() );

		if ( empty( $text ) ) :

			$text = $customer->get_email();

		endif;

		if ( ! empty( $text ) ) :

			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Customer', 'pronamic_ideal' ); ?>
				</th>
				<td>
					<?php echo \esc_html( $text ); ?>
				</td>
			</tr>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( null !== $user_id ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'User', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$user_text = sprintf( '#%s', $user_id );

				// User display name.
				$user = new WP_User( $user_id );

				$display_name = $user->display_name;

				if ( ! empty( $display_name ) ) {
					$user_text .= sprintf( ' - %s', $display_name );
				}

				printf(
					'<a href="%s">%s</a>',
					esc_url( get_edit_user_link( $user_id ) ),
					esc_html( $user_text )
				);

				?>
			</td>
		</tr>

	<?php endif; ?>

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

	<?php if ( 's2member' === $subscription->get_source() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Period', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $subscription->get_id(), '_pronamic_subscription_s2member_period', true ) ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Level', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php echo esc_html( get_post_meta( $subscription->get_id(), '_pronamic_subscription_s2member_level', true ) ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Cancel URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = $subscription->get_cancel_url();

			printf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Renewal URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = $subscription->get_renewal_url();

			printf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Mandate Selection URL', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$url = $subscription->get_mandate_selection_url();

			printf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $url )
			);

			?>
		</td>
	</tr>

	<?php if ( $this->plugin->is_debug_mode() ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'REST API URL', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				/**
				 * REST API URL.
				 *
				 * @link https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/#cookie-authentication
				 */
				$rest_api_url = rest_url( 'pronamic-pay/v1/subscriptions/' . $subscription_id );

				$rest_api_nonce_url = wp_nonce_url( $rest_api_url, 'wp_rest' );

				printf(
					'<a href="%s">%s</a>',
					esc_url( $rest_api_nonce_url ),
					esc_html( $rest_api_url )
				);

				?>
			</td>
		</tr>

	<?php endif; ?>

</table>
