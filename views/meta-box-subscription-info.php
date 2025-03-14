<?php
/**
 * Meta Box Subscription Info
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 * @var \Pronamic\WordPress\Pay\Subscriptions\Subscription $subscription Subscription.
 */

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;
use Pronamic\WordPress\Pay\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\wp_enqueue_script( 'pronamic-pay-admin-clipboard' );

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
			<?php echo esc_html( (string) $subscription->get_id() ); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php esc_html_e( 'Description', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php echo esc_html( (string) $subscription->get_description() ); ?>
		</td>
	</tr>

	<?php if ( null !== $subscription->config_id ) : ?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Gateway', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php edit_post_link( get_the_title( $subscription->config_id ), '', '', $subscription->config_id ); ?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<?php esc_html_e( 'Payment Method', 'pronamic_ideal' ); ?>
		</th>
		<td>
			<?php

			$payment_method = $subscription->get_payment_method();

			// Icon.
			$icon_url = PaymentMethods::get_icon_url( $payment_method );

			if ( null !== $icon_url ) {
				\printf(
					'<span class="pronamic-pay-tip" title="%2$s"><img src="%1$s" alt="%2$s" title="%2$s" width="32" valign="bottom" /></span> ',
					\esc_url( $icon_url ),
					\esc_attr( (string) PaymentMethods::get_name( $payment_method ) )
				);
			}

			// Name.
			echo esc_html( (string) PaymentMethods::get_name( $payment_method ) );

			?>
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

	<?php

	// Show next payment (delivery) date if subscription is active.
	if ( SubscriptionStatus::ACTIVE === $subscription->get_status() ) :

		?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Next Payment Date', 'pronamic_ideal' ); ?>
			</th>
			<td>
				<?php

				$next_payment_date = $subscription->get_next_payment_date();

				echo empty( $next_payment_date ) ? '—' : esc_html( $next_payment_date->format_i18n( __( 'D j M Y', 'pronamic_ideal' ) ) );

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

				echo empty( $next_payment_delivery_date ) ? '—' : esc_html( $next_payment_delivery_date->format_i18n( __( 'D j M Y', 'pronamic_ideal' ) ) );

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

	<tr>
		<th scope="row" rowspan="3">
			<?php esc_html_e( 'Customer action links', 'pronamic_ideal' ); ?>

			<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="<?php echo esc_attr__( 'These actions links can be shared with the customer.', 'pronamic_ideal' ); ?>" tabindex="0"></span>
		</th>
		<td>
			<div class="pronamic-pay-action-link">
				<div>
					<?php

					$url = $subscription->get_cancel_url();

					\printf(
						'<a class="pronamic-pay-action-link-anchor" href="%s">%s</a>',
						\esc_attr( $url ),
						\esc_html__( 'Customer subscription cancel page →', 'pronamic_ideal' )
					);

					?>
				</div>

				<div>
					<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="<?php echo \esc_attr__( 'This page can be shared with the customer and gives the customer the option to cancel this subscription.', 'pronamic_ideal' ); ?>" tabindex="0"></span>
				</div>

				<div class="pronamic-pay-action-link-clipboard">
					<button type="button" class="button button-small pronamic-pay-clipboard" data-clipboard-text="<?php echo \esc_url( $url ); ?>"><?php \esc_html_e( 'Copy URL to clipboard', 'pronamic_ideal' ); ?></button>
					<span class="success hidden" aria-hidden="true"><?php \esc_html_e( 'Copied!', 'pronamic_ideal' ); ?></span>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="pronamic-pay-action-link">
				<div>
					<?php

					$url = $subscription->get_renewal_url();

					\printf(
						'<a class="pronamic-pay-action-link-anchor" href="%s">%s</a>',
						\esc_attr( $url ),
						\esc_html__( 'Customer subscription renew page →', 'pronamic_ideal' )
					);

					?>
				</div>

				<div>
					<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="<?php echo \esc_attr__( 'This page can be shared with the customer and gives the customer the option to (early) renew the subscription.', 'pronamic_ideal' ); ?>" tabindex="0"></span>
				</div>

				<div class="pronamic-pay-action-link-clipboard">
					<button type="button" class="button button-small pronamic-pay-copy-url" data-clipboard-text="<?php echo \esc_url( $url ); ?>"><?php \esc_html_e( 'Copy URL to clipboard', 'pronamic_ideal' ); ?></button>
					<span class="success hidden" aria-hidden="true"><?php \esc_html_e( 'Copied!', 'pronamic_ideal' ); ?></span>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<div class="pronamic-pay-action-link">
				<div>
					<?php

					$url = $subscription->get_mandate_selection_url();

					\printf(
						'<a class="pronamic-pay-action-link-anchor" href="%s">%s</a>',
						\esc_attr( $url ),
						\esc_html__( 'Customer change payment method page →', 'pronamic_ideal' )
					);

					?>
				</div>

				<div>
					<span class="dashicons dashicons-editor-help pronamic-pay-tip" title="<?php echo \esc_attr__( 'This link can be shared with the customer and gives the customer the opportunity to change the payment method. This is useful if a credit card expires or if a customer wants to have the charge debited from another account.', 'pronamic_ideal' ); ?>" tabindex="0"></span>
				</div>

				<div class="pronamic-pay-action-link-clipboard">
					<button type="button" class="button button-small pronamic-pay-copy-url" data-clipboard-text="<?php echo \esc_url( $url ); ?>"><?php \esc_html_e( 'Copy URL to clipboard', 'pronamic_ideal' ); ?></button>
					<span class="success hidden" aria-hidden="true"><?php \esc_html_e( 'Copied!', 'pronamic_ideal' ); ?></span>
				</div>
			</div>
		</td>
	</tr>

	<?php if ( $plugin->is_debug_mode() ) : ?>

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
